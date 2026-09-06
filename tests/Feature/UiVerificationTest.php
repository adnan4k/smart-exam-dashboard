<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Contest;
use App\Models\Note;
use App\Models\Question;
use App\Models\Referral;
use App\Models\ReferralSetting;
use App\Models\Subject;
use App\Models\Type;
use App\Models\User;
use App\Models\YearGroup;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UiVerificationTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Tester',
                'password' => Hash::make('secret123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }

    /** @test */
    public function it_renders_login_page_and_validates()
    {
        Livewire::test(\App\Http\Livewire\Auth\Login::class)
            ->assertStatus(200)
            ->assertSee('Smart Exam Console')
            ->set('email', 'wrong@example.com')
            ->set('password', 'invalid')
            ->call('login')
            ->assertHasErrors(['email']);
    }

    /** @test */
    public function it_renders_dashboard_and_components()
    {
        $this->actingAs($this->admin);

        Livewire::test(\App\Http\Livewire\Dashboard::class)
            ->assertStatus(200);
    }

    /** @test */
    public function it_renders_and_updates_profile()
    {
        $this->actingAs($this->admin);

        Livewire::test(\App\Http\Livewire\Profile::class)
            ->assertStatus(200)
            ->assertSet('name', $this->admin->name)
            ->assertSet('email', $this->admin->email)
            ->set('name', 'Admin Updated Name')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertEquals('Admin Updated Name', $this->admin->fresh()->name);
    }

    /** @test */
    public function it_creates_and_manages_exam_types()
    {
        $this->actingAs($this->admin);

        $typeName = 'Test Type ' . uniqid();

        Livewire::test(\App\Http\Livewire\Type\Form::class)
            ->set('name', $typeName)
            ->set('description', 'Test Description')
            ->set('price', 150)
            ->call('saveType')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('types', ['name' => $typeName]);

        Livewire::test(\App\Http\Livewire\Type\TypeComponent::class)
            ->assertStatus(200)
            ->assertSee($typeName);
    }

    /** @test */
    public function it_creates_and_manages_chapters()
    {
        $this->actingAs($this->admin);

        $chapterName = 'Test Chapter ' . uniqid();

        Livewire::test(\App\Http\Livewire\Chapter\Form::class)
            ->set('name', $chapterName)
            ->set('description', 'Chapter Details')
            ->call('saveChapter')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('chapters', ['name' => $chapterName]);

        Livewire::test(\App\Http\Livewire\Chapter\ChapterComponent::class)
            ->assertStatus(200)
            ->assertSee($chapterName);
    }

    /** @test */
    public function it_creates_and_manages_subjects()
    {
        $this->actingAs($this->admin);

        $type = Type::first() ?? Type::create(['name' => 'General', 'price' => 0]);
        $subjectName = 'Test Subject ' . uniqid();

        Livewire::test(\App\Http\Livewire\Subjects\Form::class)
            ->set('name', $subjectName)
            ->set('typeId', $type->id)
            ->set('defaultDuration', 60)
            ->set('year', '2026')
            ->call('saveSubject')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('subjects', ['name' => $subjectName]);

        Livewire::test(\App\Http\Livewire\Subjects\SubjectComponent::class)
            ->assertStatus(200)
            ->assertSee($subjectName);
    }

    /** @test */
    public function it_creates_and_manages_year_groups()
    {
        $this->actingAs($this->admin);

        $year = rand(2025, 2099);

        Livewire::test(\App\Http\Livewire\YearGroups\Form::class)
            ->set('year', $year)
            ->call('saveYearGroup')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('year_groups', ['year' => $year]);

        Livewire::test(\App\Http\Livewire\YearGroups\YearGroupComponent::class)
            ->assertStatus(200)
            ->assertSee((string) $year);
    }

    /** @test */
    public function it_creates_and_manages_categories()
    {
        $this->actingAs($this->admin);

        $catTitle = 'Category ' . uniqid();

        Livewire::test(\App\Http\Livewire\Categories\Form::class)
            ->set('title', $catTitle)
            ->set('description', 'Category description')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['title' => $catTitle]);

        Livewire::test(\App\Http\Livewire\Categories\CategoryComponent::class)
            ->assertStatus(200)
            ->assertSee($catTitle);
    }

    /** @test */
    public function it_creates_and_manages_notes()
    {
        $this->actingAs($this->admin);

        $type = Type::first() ?? Type::create(['name' => 'Gen', 'price' => 0]);
        $subject = Subject::first() ?? Subject::create(['name' => 'Subj', 'type_id' => $type->id, 'default_duration' => 60]);
        $chapter = Chapter::first() ?? Chapter::create(['name' => 'Chap']);

        $noteTitle = 'Study Note ' . uniqid();

        Livewire::test(\App\Http\Livewire\Notes\Form::class)
            ->set('typeId', $type->id)
            ->set('subjectId', $subject->id)
            ->set('chapterId', $chapter->id)
            ->set('title', $noteTitle)
            ->set('content', '<p>Sample Note Content</p>')
            ->set('language', 'english')
            ->call('saveNote')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('notes', ['title' => $noteTitle]);

        Livewire::test(\App\Http\Livewire\Notes\NoteComponent::class)
            ->assertStatus(200)
            ->assertSee($noteTitle);
    }

    /** @test */
    public function it_renders_and_manages_videos_component()
    {
        $this->actingAs($this->admin);

        Livewire::test(\App\Http\Livewire\Videos\VideoComponent::class)
            ->assertStatus(200)
            ->set('search', 'NonExistentVideoQuery')
            ->assertStatus(200)
            ->call('clearFilters')
            ->assertSet('search', '');

        Livewire::test(\App\Http\Livewire\Videos\Form::class)
            ->assertStatus(200)
            ->assertSee('Duration (Seconds)')
            ->assertSee('Grade')
            ->assertSee('Display Order');
    }

    /** @test */
    public function it_manages_referrals_and_settings()
    {
        $this->actingAs($this->admin);

        Livewire::test(\App\Http\Livewire\Referral\ReferralComponent::class)
            ->assertStatus(200);

        Livewire::test(\App\Http\Livewire\Referral\ReferralSetting\Form::class)
            ->set('required_referrals', 10)
            ->set('reward_amount', 250)
            ->set('is_active', true)
            ->call('saveReferralSetting')
            ->assertHasNoErrors();

        Livewire::test(\App\Http\Livewire\Referral\ReferralSetting\ReferralSettingComponent::class)
            ->assertStatus(200);
    }

    /** @test */
    public function it_creates_and_manages_notifications()
    {
        $this->actingAs($this->admin);

        $type = Type::first() ?? Type::create(['name' => 'Alert Type', 'price' => 0]);
        $notifTitle = 'Broadcast ' . uniqid();

        Livewire::test(\App\Http\Livewire\Notifications\Form::class)
            ->set('title', $notifTitle)
            ->set('body', 'This is an important broadcast.')
            ->set('type_id', $type->id)
            ->call('saveNotification')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('app_notifications', ['title' => $notifTitle]);

        Livewire::test(\App\Http\Livewire\Notifications\NotificationComponent::class)
            ->assertStatus(200)
            ->assertSee($notifTitle);
    }

    /** @test */
    public function it_renders_users_and_subscription_lists()
    {
        $this->actingAs($this->admin);

        Livewire::test(\App\Http\Livewire\User\UserComponent::class)
            ->assertStatus(200)
            ->assertSee($this->admin->email);

        Livewire::test(\App\Http\Livewire\Subscription\SubscriptionComponent::class)
            ->assertStatus(200);
    }

    /** @test */
    public function it_executes_contest_lifecycle_and_builder()
    {
        $this->actingAs($this->admin);

        $contest = Contest::create([
            'title' => 'Lifecycle Contest ' . uniqid(),
            'description' => 'Test contest for paper builder',
            'status' => 'draft',
            'duration_minutes' => 45,
            'join_window_minutes' => 15,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $subject = Subject::first();

        $question = Question::create([
            'question_text' => 'What is 10 + 15?',
            'explanation' => '10 + 15 = 25',
            'subject_id' => $subject ? $subject->id : 1,
            'type_id' => $subject && $subject->type_id ? $subject->type_id : 1,
            'difficulty' => 'easy',
            'bank' => 'contest',
        ]);

        // Add question to contest paper
        $addResponse = $this->post(route('contests.questions.add', $contest), [
            'question_id' => $question->id,
            'points' => 2,
        ]);
        $addResponse->assertSessionHasNoErrors();

        // Verify question pool route
        $poolResponse = $this->get(route('contests.pool', $contest));
        $poolResponse->assertStatus(200);

        // Verify builder view
        $builderResponse = $this->get(route('contests.builder', $contest));
        $builderResponse->assertStatus(200)
            ->assertSee($contest->title);

        // Verify leaderboard
        $leaderboardResponse = $this->get(route('contests.leaderboard', $contest));
        $leaderboardResponse->assertStatus(200)
            ->assertSee($contest->title);

        // Remove question
        $removeResponse = $this->delete(route('contests.questions.remove', $contest), [
            'question_id' => $question->id,
        ]);
        $removeResponse->assertSessionHasNoErrors();
    }
}
