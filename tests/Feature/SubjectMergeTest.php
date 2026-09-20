<?php

namespace Tests\Feature;

use App\Http\Livewire\Subjects\SubjectComponent;
use App\Models\Note;
use App\Models\Package;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use App\Models\Video;
use App\Models\YearGroup;
use App\Services\SubjectMergeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubjectMergeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Type $type;
    private YearGroup $yearGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin_merge_test@example.com',
            'role' => 'admin',
        ]);

        $this->type = Type::create(['name' => 'University Entrance']);
        $this->yearGroup = YearGroup::create(['year' => 2026]);
    }

    /** @test */
    public function subject_component_displays_questions_count_for_each_subject()
    {
        $this->actingAs($this->admin);

        $subject = Subject::create([
            'name' => 'Biology General',
            'type_id' => $this->type->id,
            'year' => '2026',
        ]);

        // Create 3 questions for this subject
        foreach (range(1, 3) as $i) {
            Question::create([
                'subject_id' => $subject->id,
                'type_id' => $this->type->id,
                'year_group_id' => $this->yearGroup->id,
                'question_text' => "Question {$i}",
                'explanation' => "Explanation {$i}",
            ]);
        }

        Livewire::test(SubjectComponent::class)
            ->assertStatus(200)
            ->assertSee('Biology General')
            ->assertSee('3');
    }

    /** @test */
    public function subject_merge_service_moves_questions_notes_and_subscriptions_and_deletes_source()
    {
        $targetSubject = Subject::create(['name' => 'Mathematics Master', 'type_id' => $this->type->id, 'year' => '2026']);
        $sourceSubject1 = Subject::create(['name' => 'Math 2024', 'type_id' => $this->type->id, 'year' => '2024']);
        $sourceSubject2 = Subject::create(['name' => 'General Maths', 'type_id' => $this->type->id, 'year' => '2025']);

        // Attach questions to sources
        $q1 = Question::create([
            'subject_id' => $sourceSubject1->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'question_text' => 'Calc Q1',
            'explanation' => 'Calc Q1 explanation',
        ]);
        $q2 = Question::create([
            'subject_id' => $sourceSubject2->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'question_text' => 'Algebra Q1',
            'explanation' => 'Algebra Q1 explanation',
        ]);

        // Attach notes to source 1
        $chapter = \App\Models\Chapter::create(['name' => 'General Chapter']);
        Note::create([
            'subject_id' => $sourceSubject1->id,
            'chapter_id' => $chapter->id,
            'type_id' => $this->type->id,
            'title' => 'Calculus Notes',
            'content' => 'Derivatives and integrals',
        ]);

        // Attach package default to source 1
        $package = Package::create([
            'name' => 'Semester 1 Prep',
            'slug' => 'sem1_prep',
            'price' => 300.00,
        ]);
        $package->subjects()->attach($sourceSubject1->id, ['is_default' => true]);

        // Attach subscription to source 2
        $user = User::factory()->create();
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);
        $subscription->subjects()->attach($sourceSubject2->id);

        // Execute merge
        $service = new SubjectMergeService();
        $preview = $service->preview($targetSubject->id, [$sourceSubject1->id, $sourceSubject2->id]);
        $this->assertEquals(2, $preview['questions_count']);
        $this->assertEquals(1, $preview['notes_count']);

        $result = $service->merge($targetSubject->id, [$sourceSubject1->id, $sourceSubject2->id]);

        $this->assertEquals(2, $result['merged_count']);
        $this->assertEquals(2, $result['questions_moved']);
        $this->assertEquals(1, $result['notes_moved']);

        // Assert questions moved to target
        $this->assertEquals($targetSubject->id, $q1->fresh()->subject_id);
        $this->assertEquals($targetSubject->id, $q2->fresh()->subject_id);
        $this->assertEquals(2, $targetSubject->fresh()->questions()->withoutGlobalScopes()->count());

        // Assert package now points to target
        $this->assertTrue($package->fresh()->subjects->contains($targetSubject));

        // Assert subscription now points to target
        $this->assertTrue($subscription->fresh()->subjects->contains($targetSubject));

        // Assert sources are deleted
        $this->assertDatabaseMissing('subjects', ['id' => $sourceSubject1->id]);
        $this->assertDatabaseMissing('subjects', ['id' => $sourceSubject2->id]);
        $this->assertDatabaseHas('subjects', ['id' => $targetSubject->id]);
    }

    /** @test */
    public function subject_component_can_trigger_merge_modal_and_execute_merge()
    {
        $this->actingAs($this->admin);

        $target = Subject::create(['name' => 'Chemistry', 'type_id' => $this->type->id, 'year' => '2026']);
        $source = Subject::create(['name' => 'Chemistry 2024', 'type_id' => $this->type->id, 'year' => '2024']);

        Question::create([
            'subject_id' => $source->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'question_text' => 'Periodic Table Q1',
            'explanation' => 'Periodic Table Q1 explanation',
            'released_at' => now(),
        ]);

        Livewire::test(SubjectComponent::class)
            ->call('openMergeModal', $target->id)
            ->assertSet('targetSubjectId', $target->id)
            ->assertSet('showMergeModal', true)
            ->set('sourceSubjectIds', [$source->id])
            ->call('executeMerge')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('subjects', ['id' => $source->id]);
        $this->assertEquals(1, $target->fresh()->questions()->count());
    }
}
