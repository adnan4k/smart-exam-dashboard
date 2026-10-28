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
 * The response contract the mobile app parses, across every entitlement state
 * a student can be in: free, paid for one package, paid for everything,
 * expired, pending, and legacy (no package).
 */
class MobileContractScenariosTest extends TestCase
{
    use RefreshDatabase;

    private Type $type;
    private YearGroup $yearGroup;
    private Package $sem1;
    private Package $sem2;
    private User $student;
    private Subject $physics;
    private Subject $logic;
    private Subject $sample;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = Type::create(['name' => 'University Prep']);
        $this->yearGroup = YearGroup::create(['year' => 2024]);
        $this->sem1 = Package::updateOrCreate(['slug' => 'semester_1'], ['name' => '1st Semester', 'price' => 300, 'is_active' => true, 'type_id' => $this->type->id]);
        $this->sem2 = Package::updateOrCreate(['slug' => 'semester_2'], ['name' => '2nd Semester', 'price' => 300, 'is_active' => true, 'type_id' => $this->type->id]);
        $this->student = User::factory()->create(['type_id' => $this->type->id]);

        $this->physics = $this->subject('Physics', $this->sem1);
        $this->logic = $this->subject('Logic', $this->sem2);
        $this->sample = $this->subject('Civics', null, true);

        foreach ([$this->physics, $this->logic, $this->sample] as $subject) {
            foreach (range(1, SubjectContentService::FREE_QUESTION_LIMIT + 3) as $i) {
                Question::create([
                    'subject_id' => $subject->id,
                    'year_group_id' => $this->yearGroup->id,
                    'type_id' => $this->type->id,
                    'question_text' => 'Q',
                    'explanation' => 'E',
                    'released_at' => now(),
                ]);
            }
            Note::create([
                'subject_id' => $subject->id,
                'chapter_id' => Chapter::create(['name' => $subject->name.' ch'])->id,
                'type_id' => $this->type->id,
                'title' => $subject->name.' note',
                'content' => 'Body',
            ]);
        }
    }

    public static function entitlementStates(): array
    {
        return [
            'free' => ['free'],
            'sem1 paid' => ['sem1'],
            'expired' => ['expired'],
            'pending' => ['pending'],
            'legacy all-access' => ['legacy'],
        ];
    }

    /**
     * The app compares the catalogue's content_version with the one it stored
     * from /subjects/content. If they differ for an unchanged subject, the app
     * re-downloads (or shows "update available") forever.
     *
     * @test
     * @dataProvider entitlementStates
     */
    public function catalogue_and_content_agree_on_content_version(string $state)
    {
        $this->enter($state);

        $catalogue = collect($this->getJson('/api/subjects/catalogue?user_id='.$this->student->id)
            ->assertStatus(200)->json('data'))->keyBy('name');

        foreach (['Physics', 'Logic', 'Civics'] as $name) {
            $content = $this->getJson($this->contentUrl($name))->assertStatus(200)->json();

            $this->assertSame(
                $catalogue[$name]['content_version'],
                $content['subject']['content_version'],
                "$state / $name: catalogue and content versions differ"
            );
            // Samples differ here on purpose (open, but not a subscription);
            // that predates the per-subject gate and the app already lives with it.
            if ($name !== 'Civics') {
                $this->assertSame(
                    $catalogue[$name]['is_subscribed'],
                    $content['is_subscribed'],
                    "$state / $name: catalogue and content disagree on is_subscribed"
                );
            }

            // Round trip: the stored version must short-circuit the next sync.
            $this->getJson($this->contentUrl($name).'&known_version='.$content['subject']['content_version'])
                ->assertJsonPath('status', 'not_modified');
        }
    }

    /**
     * @test
     * @dataProvider entitlementStates
     */
    public function content_shape_is_identical_whether_locked_or_not(string $state)
    {
        $this->enter($state);

        foreach (['Physics', 'Logic', 'Civics'] as $name) {
            $json = $this->getJson($this->contentUrl($name))->assertStatus(200)->json();

            $this->assertSame(['status', 'is_subscribed', 'subject', 'questions', 'notes'], array_keys($json), "$state / $name");
            $this->assertSame(['id', 'name', 'chapters'], array_keys($json['notes']), "$state / $name");
            $this->assertIsString($json['notes']['id']);
            $this->assertIsArray($json['questions']);
        }
    }

    /** @test */
    public function sample_subject_stays_fully_open_to_a_free_student()
    {
        $json = $this->getJson($this->contentUrl('Civics'))->json();

        $this->assertCount(SubjectContentService::FREE_QUESTION_LIMIT + 3, $json['questions']);
        $this->assertCount(1, $json['notes']['chapters']);

        $grouped = collect($this->getJson('/api/notes/for-user-grouped?user_id='.$this->student->id)->json('data'))->keyBy('name');
        $this->assertCount(1, $grouped['Civics']['chapters']);
        $this->assertSame([], $grouped['Physics']['chapters']);
    }

    /** @test */
    public function all_access_and_legacy_subscribers_unlock_every_subject()
    {
        foreach (['all_access', 'legacy'] as $kind) {
            Subscription::query()->delete();
            $kind === 'legacy'
                ? $this->subscribe(null)
                : $this->subscribe(Package::updateOrCreate(['slug' => Package::SLUG_ALL_ACCESS], ['name' => 'All', 'price' => 900, 'is_active' => true, 'type_id' => $this->type->id]));

            foreach (['Physics', 'Logic'] as $name) {
                $this->getJson($this->contentUrl($name))
                    ->assertJsonPath('is_subscribed', true)
                    ->assertJsonCount(SubjectContentService::FREE_QUESTION_LIMIT + 3, 'questions');
            }
        }
    }

    /** @test */
    public function expiry_boundaries()
    {
        $sub = $this->subscribe($this->sem1, now()->toDateString());
        $this->assertTrue($this->student->fresh()->canAccessSubject($this->physics), 'ends today');

        $sub->update(['end_date' => now()->subDay()->toDateString()]);
        $this->assertFalse($this->student->fresh()->canAccessSubject($this->physics), 'ended yesterday');

        $sub->update(['end_date' => now()->addDay()->toDateString()]);
        $this->assertTrue($this->student->fresh()->canAccessSubject($this->physics), 'ends tomorrow');

        // end_date stored with a time component earlier today
        $sub->update(['end_date' => now()->startOfDay()->addMinute()]);
        $this->assertTrue($sub->fresh()->isActive());
        $this->assertTrue($this->student->fresh()->hasPaidPackage());
    }

    /** @test */
    public function check_subscription_shape_in_every_state()
    {
        $keys = ['status', 'is_subscribed', 'has_all_access', 'subscribed_package_slugs', 'subscribed_packages', 'user_id', 'type_id', 'type_price', 'message'];
        $expect = [
            'free' => ['none', false],
            'sem1' => ['paid', true],
            'expired' => ['paid', false],
            'pending' => ['pending', false],
        ];

        foreach ($expect as $state => [$status, $subscribed]) {
            Subscription::query()->delete();
            $this->enter($state);

            $json = $this->postJson('/api/check-subscription', ['user_id' => $this->student->id])->assertStatus(200)->json();
            $this->assertSame($keys, array_keys($json), $state);
            $this->assertSame($status, $json['status'], $state);
            $this->assertSame($subscribed, $json['is_subscribed'], $state);
            $this->assertSame($subscribed ? ['semester_1'] : [], $json['subscribed_package_slugs'], $state);
        }
    }

    /** @test */
    public function packages_endpoint_reports_expired_as_not_subscribed_with_same_shape()
    {
        $this->enter('expired');

        $pkg = collect($this->getJson('/api/subscription/packages?user_id='.$this->student->id)
            ->assertStatus(200)->json('data'))->keyBy('slug')['semester_1'];

        $this->assertFalse($pkg['is_subscribed']);
        $this->assertSame('paid', $pkg['payment_status']);
    }

    /** @test */
    public function an_active_subscriber_cannot_resubscribe_but_an_expired_one_can()
    {
        $this->enter('sem1');
        $this->postJson('/api/subscribe', ['user_id' => $this->student->id, 'package_id' => $this->sem1->id, 'image' => 'x'])
            ->assertStatus(400);

        Subscription::query()->update(['end_date' => now()->subDay()->toDateString()]);
        $this->postJson('/api/subscribe', ['user_id' => $this->student->id, 'package_id' => $this->sem1->id, 'image' => 'x'])
            ->assertStatus(200);

        $this->assertSame(1, Subscription::count(), 'renewal reuses the row');
        $this->assertTrue(Subscription::first()->end_date >= now()->toDateString());
    }

    /** @test */
    public function resending_the_same_subject_selection_still_succeeds()
    {
        $sub = $this->subscribe($this->sem1);
        $sub->subjects()->sync([$this->physics->id, $this->sample->id]);

        // Same set, different order, with a duplicate: what an app "save" button sends.
        $this->postJson('/api/subscriptions/select-subjects', [
            'user_id' => $this->student->id,
            'package_id' => $this->sem1->id,
            'subject_ids' => [$this->sample->id, $this->physics->id, $this->physics->id],
        ])->assertStatus(200)->assertJsonPath('status', 'success');

        // Legacy alias route behaves the same.
        $this->postJson('/api/package/select-subjects', [
            'user_id' => $this->student->id,
            'package_id' => $this->sem1->id,
            'subject_ids' => [$this->logic->id],
        ])->assertStatus(422);
    }

    /** @test */
    public function question_endpoints_with_user_id_in_post_body_or_query()
    {
        $this->enter('sem1');

        $post = $this->postJson('/api/questions/by-year', ['year' => 2024, 'user_id' => $this->student->id])->assertStatus(200)->json('response');
        $get = $this->getJson('/api/questions/year?year=2024&user_id='.$this->student->id)->assertStatus(200)->json('response');

        foreach ([$post, $get] as $response) {
            $this->assertCount(SubjectContentService::FREE_QUESTION_LIMIT + 3, $response['Physics']);
            $this->assertCount(SubjectContentService::FREE_QUESTION_LIMIT, $response['Logic']);
            $this->assertCount(SubjectContentService::FREE_QUESTION_LIMIT + 3, $response['Civics']);
            $this->assertArrayHasKey('choices', $response['Physics'][0]);
        }

        // Unknown user id is anonymous, not an error.
        $this->getJson('/api/questions/year?year=2024&user_id=999999')->assertStatus(200);
        // Unknown year: empty, not an error.
        $this->getJson('/api/questions/year?year=1990&user_id='.$this->student->id)->assertStatus(200)->assertJsonPath('response', []);

        $byType = $this->postJson('/api/get-questions', ['user_id' => $this->student->id])->assertStatus(200);
        $this->assertNotEmpty($byType->json());
    }

    /** @test */
    public function note_endpoints_by_token_and_anonymously()
    {
        $this->enter('sem1');

        // Anonymous: samples only, same envelope.
        $anon = $this->getJson('/api/notes')->assertStatus(200)->json();
        $this->assertSame(['Civics note'], collect($anon['data'])->pluck('title')->all());
        $this->getJson('/api/notes/filter')->assertStatus(200);

        // With the student's token: their package plus samples.
        $this->actingAs($this->student, 'sanctum');
        $titles = collect($this->getJson('/api/notes')->json('data'))->pluck('title')->sort()->values()->all();
        $this->assertSame(['Civics note', 'Physics note'], $titles);

        $filtered = collect($this->getJson('/api/notes/filter?subject_id='.$this->logic->id)->assertStatus(200)->json('data'));
        $this->assertCount(0, $filtered);

        $logicNote = Note::where('subject_id', $this->logic->id)->first();
        $this->getJson('/api/notes/'.$logicNote->id)->assertStatus(404);
    }

    /** @test */
    public function for_user_notes_respect_subscription()
    {
        $this->enter('sem1');
        $url = fn (Subject $s) => '/api/notes/for-user?user_id='.$this->student->id
            .'&subject_id='.$s->id.'&chapter_id='.Note::where('subject_id', $s->id)->value('chapter_id');

        $this->getJson($url($this->physics))->assertStatus(200);
        $this->getJson($url($this->logic))->assertStatus(403);
        $this->getJson($url($this->sample))->assertStatus(200);
    }

    /** @test */
    public function fcm_topic_drops_for_an_expired_student()
    {
        // getUserTopic() itself is unreachable (the POST route 500s because the
        // nullable Request is never injected, and its `instanceof User` branch
        // is ruled out by the type hint), on master as well as here. This is
        // the lookup it runs.
        $this->enter('sem1');
        $this->assertSame($this->type->id, $this->student->subscriptions()->active()->first()?->type_id);

        Subscription::query()->update(['end_date' => now()->subDay()->toDateString()]);
        $this->assertNull($this->student->subscriptions()->active()->first());
    }

    private function enter(string $state): void
    {
        match ($state) {
            'free' => null,
            'sem1' => $this->subscribe($this->sem1),
            'expired' => $this->subscribe($this->sem1, now()->subDay()),
            'pending' => $this->subscribe($this->sem1)->update(['payment_status' => 'pending']),
            'legacy' => $this->subscribe(null),
        };
    }

    private function subject(string $name, ?Package $package, bool $sample = false): Subject
    {
        return Subject::create([
            'name' => $name,
            'year' => '2015',
            'type_id' => $this->type->id,
            'package_id' => $package?->id,
            'package_type' => $package?->slug,
            'is_sample' => $sample,
        ]);
    }

    private function subscribe(?Package $package, $endDate = null): Subscription
    {
        return Subscription::create([
            'user_id' => $this->student->id,
            'type_id' => $this->type->id,
            'year_group_id' => $this->yearGroup->id,
            'package_id' => $package?->id,
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
