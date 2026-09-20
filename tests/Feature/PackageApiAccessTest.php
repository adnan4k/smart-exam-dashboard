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
use App\Models\Video;
use App\Models\YearGroup;
use App\Services\SubjectContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PackageApiAccessTest extends TestCase
{
    use RefreshDatabase;

    private Type $type;
    private YearGroup $yearGroup;
    private Package $sem1Package;
    private Package $sem2Package;
    private Package $cocPackage;
    private Package $allAccessPackage;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Video::DISK);
        Storage::fake(Video::THUMB_DISK);

        $this->type = Type::create(['name' => 'University Prep']);
        $this->yearGroup = YearGroup::create(['year' => 2024]);

        $this->sem1Package = Package::updateOrCreate(
            ['slug' => 'semester_1'],
            [
                'name' => '1st Semester',
                'price' => 300.00,
                'is_active' => true,
                'display_order' => 1,
                'type_id' => $this->type->id,
            ]
        );

        $this->sem2Package = Package::updateOrCreate(
            ['slug' => 'semester_2'],
            [
                'name' => '2nd Semester',
                'price' => 300.00,
                'is_active' => true,
                'display_order' => 2,
                'type_id' => $this->type->id,
            ]
        );

        $this->cocPackage = Package::updateOrCreate(
            ['slug' => 'coc'],
            [
                'name' => 'COC Exam',
                'price' => 400.00,
                'is_active' => true,
                'display_order' => 3,
                'type_id' => $this->type->id,
            ]
        );

        $this->allAccessPackage = Package::updateOrCreate(
            ['slug' => 'all_access'],
            [
                'name' => 'All Access',
                'price' => 700.00,
                'is_active' => true,
                'display_order' => 4,
                'type_id' => $this->type->id,
            ]
        );
    }

    /** @test */
    public function get_packages_returns_all_active_packages_with_user_subscription_status()
    {
        $user = User::factory()->create(['type_id' => $this->type->id]);

        // Subscribe user to 1st Semester
        Subscription::create([
            'user_id' => $user->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => $this->sem1Package->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);

        $response = $this->getJson('/api/packages?user_id=' . $user->id)
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $packages = collect($response->json('data'));
        $this->assertCount(4, $packages);

        $sem1 = $packages->firstWhere('slug', 'semester_1');
        $sem2 = $packages->firstWhere('slug', 'semester_2');

        $this->assertTrue($sem1['is_subscribed']);
        $this->assertFalse($sem2['is_subscribed']);
    }

    /** @test */
    public function all_access_marks_all_packages_as_subscribed()
    {
        $user = User::factory()->create(['type_id' => $this->type->id]);

        Subscription::create([
            'user_id' => $user->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => $this->allAccessPackage->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);

        $response = $this->getJson('/api/packages?user_id=' . $user->id)
            ->assertStatus(200);

        $packages = collect($response->json('data'));
        foreach ($packages as $pkg) {
            $this->assertTrue($pkg['is_subscribed'], "Expected {$pkg['slug']} to be marked subscribed with All Access");
        }
    }

    /** @test */
    public function subscribe_endpoint_creates_package_subscription_and_prevents_duplicate_active()
    {
        $user = User::factory()->create(['type_id' => $this->type->id]);

        $image = UploadedFile::fake()->image('receipt.jpg');

        $payload = [
            'user_id' => $user->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => $this->sem1Package->id,
            'payment_method' => 'telebirr',
            'transaction_id' => 'TX123456',
            'image' => $image,
        ];

        $response = $this->post('/api/subscribe', $payload)
            ->assertStatus(201);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'package_id' => $this->sem1Package->id,
            'payment_status' => 'pending',
        ]);

        // Mark as paid to simulate admin verification
        $user->subscriptions()->where('package_id', $this->sem1Package->id)->update(['payment_status' => 'paid']);

        // Attempting to subscribe to the same package again while already active paid should be rejected
        $this->post('/api/subscribe', [
            'user_id' => $user->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => $this->sem1Package->id,
            'payment_method' => 'telebirr',
            'transaction_id' => 'TX123456_DUP',
            'image' => UploadedFile::fake()->image('receipt2.jpg'),
        ])
            ->assertStatus(400);
    }

    /** @test */
    public function check_subscription_returns_package_slugs_and_all_access_flag()
    {
        $user = User::factory()->create(['type_id' => $this->type->id]);

        Subscription::create([
            'user_id' => $user->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => $this->sem1Package->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => $this->cocPackage->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);

        $response = $this->postJson('/api/check-subscription', ['user_id' => $user->id])
            ->assertStatus(200)
            ->assertJsonPath('status', 'paid');

        $this->assertTrue($response->json('is_subscribed'));
        $this->assertFalse($response->json('has_all_access'));
        $this->assertEqualsCanonicalizing(['semester_1', 'coc'], $response->json('subscribed_package_slugs'));
    }

    /** @test */
    public function student_with_sem1_accesses_sem1_content_fully_and_is_capped_on_sem2()
    {
        $student = User::factory()->create(['type_id' => $this->type->id]);

        // Student pays for 1st Semester
        Subscription::create([
            'user_id' => $student->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => $this->sem1Package->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);

        $sem1Subject = Subject::create([
            'name' => 'General Psychology',
            'year' => '2015',
            'type_id' => $this->type->id,
            'package_id' => $this->sem1Package->id,
            'package_type' => 'semester_1',
        ]);

        $sem2Subject = Subject::create([
            'name' => 'Inclusion in Education',
            'year' => '2015',
            'type_id' => $this->type->id,
            'package_id' => $this->sem2Package->id,
            'package_type' => 'semester_2',
        ]);

        // Seed 45 questions for each subject
        foreach (range(1, 45) as $i) {
            $this->makeQuestion($sem1Subject, "Psychology Q{$i}");
            $this->makeQuestion($sem2Subject, "Inclusion Q{$i}");
        }

        // 1. Check Sem 1 content download -> entitled, gets all 45 questions
        $sem1Resp = $this->getJson('/api/subjects/content?user_id=' . $student->id . '&subject=' . urlencode($sem1Subject->name))
            ->assertStatus(200);

        $this->assertTrue($sem1Resp->json('is_subscribed'));
        $this->assertCount(45, $sem1Resp->json('questions'));

        // 2. Check Sem 2 content download -> NOT entitled, capped to FREE_QUESTION_LIMIT (40)
        $sem2Resp = $this->getJson('/api/subjects/content?user_id=' . $student->id . '&subject=' . urlencode($sem2Subject->name))
            ->assertStatus(200);

        $this->assertFalse($sem2Resp->json('is_subscribed'));
        $this->assertCount(SubjectContentService::FREE_QUESTION_LIMIT, $sem2Resp->json('questions'));

        // 3. Check QuestionController /api/questions/subject endpoint
        $qSem1Resp = $this->getJson('/api/questions/subject?subject=' . $sem1Subject->id . '&user_id=' . $student->id)
            ->assertStatus(200);
        $this->assertCount(45, collect($qSem1Resp->json('response'))->flatten(1));

        $qSem2Resp = $this->getJson('/api/questions/subject?subject=' . $sem2Subject->id . '&user_id=' . $student->id)
            ->assertStatus(200);
        $this->assertCount(SubjectContentService::FREE_QUESTION_LIMIT, collect($qSem2Resp->json('response'))->flatten(1));
    }

    /** @test */
    public function student_with_all_access_unlocks_both_semesters()
    {
        $student = User::factory()->create(['type_id' => $this->type->id]);

        // Student pays for All Access
        Subscription::create([
            'user_id' => $student->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => $this->allAccessPackage->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);

        $sem1Subject = Subject::create([
            'name' => 'Math for Natural Sciences',
            'year' => '2015',
            'type_id' => $this->type->id,
            'package_id' => $this->sem1Package->id,
            'package_type' => 'semester_1',
        ]);

        $sem2Subject = Subject::create([
            'name' => 'Anthropology',
            'year' => '2015',
            'type_id' => $this->type->id,
            'package_id' => $this->sem2Package->id,
            'package_type' => 'semester_2',
        ]);

        foreach (range(1, 45) as $i) {
            $this->makeQuestion($sem1Subject, "Math Q{$i}");
            $this->makeQuestion($sem2Subject, "Anthro Q{$i}");
        }

        $sem1Resp = $this->getJson('/api/subjects/content?user_id=' . $student->id . '&subject=' . urlencode($sem1Subject->name))
            ->assertStatus(200);
        $this->assertTrue($sem1Resp->json('is_subscribed'));
        $this->assertCount(45, $sem1Resp->json('questions'));

        $sem2Resp = $this->getJson('/api/subjects/content?user_id=' . $student->id . '&subject=' . urlencode($sem2Subject->name))
            ->assertStatus(200);
        $this->assertTrue($sem2Resp->json('is_subscribed'));
        $this->assertCount(45, $sem2Resp->json('questions'));
    }

    /** @test */
    public function legacy_subscriptions_retain_access_to_all_subjects()
    {
        $student = User::factory()->create(['type_id' => $this->type->id]);

        // Legacy subscription: package_id is NULL
        Subscription::create([
            'user_id' => $student->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => null,
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);

        $sem1Subject = Subject::create([
            'name' => 'Economics',
            'year' => '2015',
            'type_id' => $this->type->id,
            'package_id' => $this->sem1Package->id,
            'package_type' => 'semester_1',
        ]);

        $cocSubject = Subject::create([
            'name' => 'Nursing Exit Exam',
            'year' => '2015',
            'type_id' => $this->type->id,
            'package_id' => $this->cocPackage->id,
            'package_type' => 'coc',
        ]);

        foreach (range(1, 45) as $i) {
            $this->makeQuestion($sem1Subject, "Econ Q{$i}");
            $this->makeQuestion($cocSubject, "COC Q{$i}");
        }

        $sem1Resp = $this->getJson('/api/subjects/content?user_id=' . $student->id . '&subject=' . urlencode($sem1Subject->name))
            ->assertStatus(200);
        $this->assertTrue($sem1Resp->json('is_subscribed'));
        $this->assertCount(45, $sem1Resp->json('questions'));

        $cocResp = $this->getJson('/api/subjects/content?user_id=' . $student->id . '&subject=' . urlencode($cocSubject->name))
            ->assertStatus(200);
        $this->assertTrue($cocResp->json('is_subscribed'));
        $this->assertCount(45, $cocResp->json('questions'));
    }

    /** @test */
    public function notes_and_videos_are_gated_by_subject_package()
    {
        $student = User::factory()->create(['type_id' => $this->type->id]);

        // Student only pays for 1st Semester
        Subscription::create([
            'user_id' => $student->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => $this->sem1Package->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);

        $sem1Subject = Subject::create([
            'name' => 'Physics',
            'year' => '2015',
            'type_id' => $this->type->id,
            'package_id' => $this->sem1Package->id,
            'package_type' => 'semester_1',
        ]);

        $sem2Subject = Subject::create([
            'name' => 'Logic and Critical Thinking',
            'year' => '2015',
            'type_id' => $this->type->id,
            'package_id' => $this->sem2Package->id,
            'package_type' => 'semester_2',
        ]);

        $chapter1 = Chapter::create(['name' => 'Mechanics']);
        $chapter2 = Chapter::create(['name' => 'Formal Logic']);

        Note::create([
            'subject_id' => $sem1Subject->id,
            'chapter_id' => $chapter1->id,
            'type_id' => $this->type->id,
            'title' => 'Newton Laws',
            'content' => 'F = ma',
        ]);

        Note::create([
            'subject_id' => $sem2Subject->id,
            'chapter_id' => $chapter2->id,
            'type_id' => $this->type->id,
            'title' => 'Fallacies',
            'content' => 'Ad hominem',
        ]);

        // Notes test: sem1 succeeds, sem2 gives 403
        $this->getJson("/api/notes/for-user?user_id={$student->id}&subject_id={$sem1Subject->id}&chapter_id={$chapter1->id}")
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->getJson("/api/notes/for-user?user_id={$student->id}&subject_id={$sem2Subject->id}&chapter_id={$chapter2->id}")
            ->assertStatus(403)
            ->assertJsonPath('status', 'error');

        // Video test: create video for sem1 and sem2
        $file1 = UploadedFile::fake()->create('physics.mp4', 100, 'video/mp4');
        $path1 = $file1->store('videos', Video::DISK);

        $video1 = Video::create([
            'title' => 'Mechanics Video',
            'file_path' => $path1,
            'file_size' => 102400,
            'mime_type' => 'video/mp4',
            'subject_id' => $sem1Subject->id,
            'chapter_id' => $chapter1->id,
            'type_id' => $this->type->id,
            'is_active' => true,
        ]);

        $file2 = UploadedFile::fake()->create('logic.mp4', 100, 'video/mp4');
        $path2 = $file2->store('videos', Video::DISK);

        $video2 = Video::create([
            'title' => 'Logic Video',
            'file_path' => $path2,
            'file_size' => 102400,
            'mime_type' => 'video/mp4',
            'subject_id' => $sem2Subject->id,
            'chapter_id' => $chapter2->id,
            'type_id' => $this->type->id,
            'is_active' => true,
        ]);

        // Video 1 download succeeds
        $this->get("/api/videos/{$video1->id}/download?user_id={$student->id}")
            ->assertStatus(200);

        // Video 2 download is rejected with 403 not_entitled
        $this->getJson("/api/videos/{$video2->id}/download?user_id={$student->id}")
            ->assertStatus(403)
            ->assertJsonPath('reason', 'not_entitled');
    }

    /** @test */
    public function artisan_command_grandfathers_null_package_subscriptions_to_all_access()
    {
        $user1 = User::factory()->create(['type_id' => $this->type->id]);
        $user2 = User::factory()->create(['type_id' => $this->type->id]);

        $sub1 = Subscription::create([
            'user_id' => $user1->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => null,
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);

        $sub2 = Subscription::create([
            'user_id' => $user2->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => null,
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);

        $this->artisan('packages:grandfather-legacy', ['--dry-run' => true])
            ->assertExitCode(0);

        $this->assertNull($sub1->fresh()->package_id);

        $this->artisan('packages:grandfather-legacy')
            ->assertExitCode(0);

        $this->assertSame($this->allAccessPackage->id, $sub1->fresh()->package_id);
        $this->assertSame($this->allAccessPackage->id, $sub2->fresh()->package_id);
    }

    private function makeQuestion(Subject $subject, string $text): Question
    {
        return Question::create([
            'subject_id' => $subject->id,
            'year_group_id' => $this->yearGroup->id,
            'type_id' => $this->type->id,
            'question_text' => $text,
            'explanation' => 'Explanation for ' . $text,
            'released_at' => now(),
        ]);
    }
}
