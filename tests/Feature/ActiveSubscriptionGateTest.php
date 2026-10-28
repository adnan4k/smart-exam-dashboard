<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Note;
use App\Models\Package;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use App\Models\YearGroup;
use App\Services\SubjectContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Paid content is for students whose subscription is paid, unexpired, and
 * covers the subject in question. Paying for one package, or having paid once
 * in the past, is not enough.
 */
class ActiveSubscriptionGateTest extends TestCase
{
    use RefreshDatabase;

    private Type $type;
    private YearGroup $yearGroup;
    private Package $sem1Package;
    private Package $sem2Package;
    private User $student;
    private Subject $sem1Subject;
    private Subject $sem2Subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = Type::create(['name' => 'University Prep']);
        $this->yearGroup = YearGroup::create(['year' => 2024]);

        $this->sem1Package = Package::updateOrCreate(
            ['slug' => 'semester_1'],
            ['name' => '1st Semester', 'price' => 300.00, 'is_active' => true, 'type_id' => $this->type->id]
        );
        $this->sem2Package = Package::updateOrCreate(
            ['slug' => 'semester_2'],
            ['name' => '2nd Semester', 'price' => 300.00, 'is_active' => true, 'type_id' => $this->type->id]
        );

        $this->student = User::factory()->create(['type_id' => $this->type->id]);

        $this->sem1Subject = $this->subject('Physics', $this->sem1Package);
        $this->sem2Subject = $this->subject('Logic', $this->sem2Package);

        foreach ([$this->sem1Subject, $this->sem2Subject] as $subject) {
            foreach (range(1, SubjectContentService::FREE_QUESTION_LIMIT + 5) as $i) {
                $this->question($subject);
            }

            Note::create([
                'subject_id' => $subject->id,
                'chapter_id' => Chapter::create(['name' => $subject->name.' chapter'])->id,
                'type_id' => $this->type->id,
                'title' => $subject->name.' note',
                'content' => 'Paid body',
            ]);
        }
    }

    /** @test */
    public function content_does_not_unlock_a_subject_from_a_package_the_student_did_not_buy()
    {
        $this->subscribe($this->sem1Package);

        $this->getJson($this->contentUrl('Physics'))
            ->assertStatus(200)
            ->assertJsonPath('is_subscribed', true)
            ->assertJsonCount(SubjectContentService::FREE_QUESTION_LIMIT + 5, 'questions')
            ->assertJsonCount(1, 'notes.chapters');

        $this->getJson($this->contentUrl('Logic'))
            ->assertStatus(200)
            ->assertJsonPath('is_subscribed', false)
            ->assertJsonCount(0, 'questions')
            ->assertJsonCount(0, 'notes.chapters');
    }

    /** @test */
    public function an_expired_subscription_unlocks_nothing()
    {
        $this->subscribe($this->sem1Package, now()->subDay());

        $this->assertFalse($this->student->canAccessSubject($this->sem1Subject));
        $this->assertFalse($this->student->hasPaidPackage());

        $this->getJson($this->contentUrl('Physics'))
            ->assertJsonPath('is_subscribed', false)
            ->assertJsonCount(0, 'questions')
            ->assertJsonCount(0, 'notes.chapters');

        $this->getJson('/api/notes/for-user?user_id='.$this->student->id
            .'&subject_id='.$this->sem1Subject->id
            .'&chapter_id='.Note::where('subject_id', $this->sem1Subject->id)->value('chapter_id'))
            ->assertStatus(403);

        // Same status values as before; is_subscribed carries the expiry.
        $this->postJson('/api/check-subscription', ['user_id' => $this->student->id])
            ->assertJsonPath('is_subscribed', false)
            ->assertJsonPath('status', 'paid');
    }

    /** @test */
    public function a_subscription_ending_today_is_still_active()
    {
        $this->subscribe($this->sem1Package, now()->startOfDay());

        $this->assertTrue($this->student->canAccessSubject($this->sem1Subject));
    }

    /** @test */
    public function an_expired_subscription_can_be_renewed()
    {
        $this->subscribe($this->sem1Package, now()->subDay());

        $this->postJson('/api/subscribe', [
            'user_id' => $this->student->id,
            'package_id' => $this->sem1Package->id,
            'image' => 'not-a-receipt',
        ])->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->student->id,
            'package_id' => $this->sem1Package->id,
            'payment_status' => 'pending',
        ]);
    }

    /** @test */
    public function notes_grouped_lock_subjects_outside_the_students_package()
    {
        $this->subscribe($this->sem1Package);

        $data = collect($this->getJson('/api/notes/for-user-grouped?user_id='.$this->student->id)
            ->assertStatus(200)
            ->json('data'))
            ->keyBy('name');

        $this->assertCount(1, $data['Physics']['chapters']);
        $this->assertSame([], $data['Logic']['chapters']);
        $this->assertSame(['id', 'name', 'chapters'], array_keys($data['Logic']));
        $this->assertSame(['id', 'name', 'chapters'], array_keys($data['Physics']));
    }

    /** @test */
    public function public_note_listing_leaves_out_paid_notes_for_anonymous_callers()
    {
        $this->getJson('/api/notes')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonStructure(['status', 'data', 'pagination']);

        // A locked note answers exactly like a note that does not exist.
        config(['app.debug' => false]);
        $note = Note::where('subject_id', $this->sem1Subject->id)->first();
        $locked = $this->getJson('/api/notes/'.$note->id)->assertStatus(404)->json();
        $missing = $this->getJson('/api/notes/999999')->assertStatus(404)->json();
        $this->assertSame(
            str_replace('999999', (string) $note->id, $missing['message']),
            $locked['message']
        );
        $this->assertSame(array_keys($missing), array_keys($locked));
    }

    /** @test */
    public function public_note_listing_returns_notes_unchanged_for_an_entitled_token()
    {
        $this->subscribe($this->sem1Package);
        $this->actingAs($this->student, 'sanctum');

        $notes = $this->getJson('/api/notes')->assertStatus(200)->json('data');

        $this->assertCount(1, $notes);
        $this->assertSame('Paid body', $notes[0]['content']);
        $this->assertArrayNotHasKey('locked', $notes[0]);

        $note = Note::where('subject_id', $this->sem1Subject->id)->first();
        $this->getJson('/api/notes/'.$note->id)
            ->assertStatus(200)
            ->assertJsonPath('data.content', 'Paid body');
    }

    /** @test */
    public function question_dump_endpoints_cap_subjects_the_student_has_not_paid_for()
    {
        $this->subscribe($this->sem1Package);

        foreach (['/api/questions/grouped-by-subject', '/api/questions/year?year=2024'] as $url) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $response = $this->getJson($url.$separator.'user_id='.$this->student->id)
                ->assertStatus(200)
                ->json('response');

            $this->assertCount(SubjectContentService::FREE_QUESTION_LIMIT + 5, $response['Physics'], $url);
            $this->assertCount(SubjectContentService::FREE_QUESTION_LIMIT, $response['Logic'], $url);
        }

        // Still callable without user_id, as before: previews only.
        $anonymous = $this->getJson('/api/questions/grouped-by-subject')
            ->assertStatus(200)
            ->json('response');
        $this->assertCount(SubjectContentService::FREE_QUESTION_LIMIT, $anonymous['Physics']);

        $this->getJson('/api/questions/grouped-by-type')->assertStatus(200);
        $this->getJson('/api/questions/year?year=2024')->assertStatus(200);
    }

    /** @test */
    public function a_paid_student_can_add_subjects_but_not_swap_them()
    {
        $subscription = $this->subscribe($this->sem1Package);
        $subscription->subjects()->sync([$this->sem1Subject->id]);

        // Swapping Physics out for Logic would unlock a subject never paid for.
        $this->postJson('/api/subscriptions/select-subjects', [
            'user_id' => $this->student->id,
            'package_id' => $this->sem1Package->id,
            'subject_ids' => [$this->sem2Subject->id],
        ])->assertStatus(422)->assertJsonPath('status', 'error');

        $this->assertSame([$this->sem1Subject->id], $subscription->subjects()->pluck('subjects.id')->all());

        // Filling a free slot is still allowed, with the usual success response.
        $this->postJson('/api/subscriptions/select-subjects', [
            'user_id' => $this->student->id,
            'package_id' => $this->sem1Package->id,
            'subject_ids' => [$this->sem1Subject->id, $this->sem2Subject->id],
        ])->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('selected_count', 2);
    }

    /** @test */
    public function a_pending_student_can_still_change_their_mind()
    {
        $subscription = $this->subscribe($this->sem1Package);
        $subscription->update(['payment_status' => 'pending']);
        $subscription->subjects()->sync([$this->sem1Subject->id]);

        $this->postJson('/api/subscriptions/select-subjects', [
            'user_id' => $this->student->id,
            'package_id' => $this->sem1Package->id,
            'subject_ids' => [$this->sem2Subject->id],
        ])->assertStatus(200);
    }

    private function subject(string $name, Package $package): Subject
    {
        return Subject::create([
            'name' => $name,
            'year' => '2015',
            'type_id' => $this->type->id,
            'package_id' => $package->id,
            'package_type' => $package->slug,
            'is_sample' => false,
        ]);
    }

    private function question(Subject $subject): Question
    {
        return Question::create([
            'subject_id' => $subject->id,
            'year_group_id' => $this->yearGroup->id,
            'type_id' => $this->type->id,
            'question_text' => 'Placeholder question',
            'explanation' => 'Placeholder explanation',
            'released_at' => now(),
        ]);
    }

    private function subscribe(Package $package, $endDate = null): Subscription
    {
        return Subscription::create([
            'user_id' => $this->student->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => $package->id,
            'start_date' => now()->subMonth(),
            'end_date' => $endDate ?? now()->addYear(),
            'payment_status' => 'paid',
        ]);
    }

    private function contentUrl(string $subject): string
    {
        return '/api/subjects/content?user_id='.$this->student->id.'&subject='.urlencode($subject);
    }
}
