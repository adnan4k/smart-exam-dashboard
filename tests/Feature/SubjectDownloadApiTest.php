<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Choice;
use App\Models\Note;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use App\Models\YearGroup;
use App\Services\SubjectContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectDownloadApiTest extends TestCase
{
    use RefreshDatabase;

    private Type $type;

    private User $user;

    private YearGroup $yearGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = Type::create(['name' => 'Grade 12']);
        $this->user = User::factory()->create(['type_id' => $this->type->id]);
        $this->yearGroup = YearGroup::create(['year' => 2024]);
    }

    /**
     * The whole point of the catalogue endpoint: `subjects` holds one row per
     * (name, year, region), and the app shows one card per name.
     *
     * @test
     */
    public function catalogue_collapses_year_and_region_variants_into_one_entry()
    {
        $biology2015 = $this->subject('Biology', '2015', 'Addis Ababa');
        $biology2016 = $this->subject('Biology', '2016', 'Oromia');
        $chemistry = $this->subject('Chemistry', '2015', 'Addis Ababa');

        $this->question($biology2015);
        $this->question($biology2015);
        $this->question($biology2016);
        $this->question($chemistry);

        $response = $this->getJson('/api/subjects/catalogue?user_id='.$this->user->id)
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $data = collect($response->json('data'));

        $this->assertCount(2, $data, 'Expected one entry per subject name.');

        $biology = $data->firstWhere('key', 'Biology');

        $this->assertSame('Biology', $biology['name']);
        $this->assertSame(3, $biology['question_count']);
        $this->assertEqualsCanonicalizing(['2015', '2016'], $biology['years']);
        $this->assertEqualsCanonicalizing(['Addis Ababa', 'Oromia'], $biology['regions']);
        $this->assertEqualsCanonicalizing(
            [$biology2015->id, $biology2016->id],
            $biology['subject_ids']
        );
    }

    /** @test */
    public function catalogue_carries_no_question_bodies()
    {
        $subject = $this->subject('Biology', '2015');
        $this->question($subject, ['question_text' => 'What is mitosis?']);

        $response = $this->getJson('/api/subjects/catalogue?user_id='.$this->user->id)
            ->assertStatus(200);

        // It is fetched on every launch; shipping bodies here would defeat the
        // point of splitting catalogue from content.
        $this->assertStringNotContainsString('What is mitosis?', $response->getContent());
    }

    /** @test */
    public function content_returns_questions_from_every_variant_of_the_subject()
    {
        $this->subscribe();

        $biology2015 = $this->subject('Biology', '2015');
        $biology2016 = $this->subject('Biology', '2016');
        $chemistry = $this->subject('Chemistry', '2015');

        $this->question($biology2015);
        $this->question($biology2016);
        $this->question($chemistry);

        $response = $this->getJson(
            '/api/subjects/content?user_id='.$this->user->id.'&subject=Biology'
        )->assertStatus(200)->assertJsonPath('status', 'success');

        $this->assertCount(2, $response->json('questions'));
    }

    /**
     * The old getQuestionsBySubject returned questions without their `subject`
     * and `chapter` relations, which the app dereferences unconditionally while
     * parsing. Missing relations were a client-side crash, not a soft failure.
     *
     * @test
     */
    public function content_questions_carry_the_relations_and_absolute_image_urls_the_app_parses()
    {
        $this->subscribe();

        $subject = $this->subject('Biology', '2015', 'Addis Ababa');
        $chapter = Chapter::create(['name' => 'Cell Division']);

        $question = $this->question($subject, [
            'chapter_id' => $chapter->id,
            'question_image_path' => 'questions/mitosis.png',
        ]);

        Choice::create([
            'question_id' => $question->id,
            'choice_text' => 'Cell division',
            'choice_image_path' => 'choices/answer.png',
        ]);

        $payload = $this->getJson(
            '/api/subjects/content?user_id='.$this->user->id.'&subject=Biology'
        )->assertStatus(200)->json('questions.0');

        $this->assertNotNull($payload['subject'], 'App reads year/region off subject.');
        $this->assertNotNull($payload['chapter']);
        $this->assertSame('2015', $payload['subject']['year']);
        $this->assertSame('Addis Ababa', $payload['subject']['region']);

        $this->assertSame(asset('storage/questions/mitosis.png'), $payload['question_image_path']);
        $this->assertSame(asset('storage/choices/answer.png'), $payload['choices'][0]['choice_image_path']);
    }

    /** @test */
    public function content_caps_questions_for_users_without_a_paid_subscription()
    {
        $subject = $this->subject('Biology', '2015');

        foreach (range(1, SubjectContentService::FREE_QUESTION_LIMIT + 5) as $i) {
            $this->question($subject);
        }

        $response = $this->getJson(
            '/api/subjects/content?user_id='.$this->user->id.'&subject=Biology'
        )->assertStatus(200)->assertJsonPath('is_subscribed', false);

        $this->assertCount(SubjectContentService::FREE_QUESTION_LIMIT, $response->json('questions'));
    }

    /** @test */
    public function content_returns_notes_grouped_by_chapter_in_the_shape_the_app_parses()
    {
        $this->subscribe();

        $subject = $this->subject('Biology', '2015');
        $this->question($subject);

        $chapter = Chapter::create(['name' => 'Genetics']);

        Note::create([
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'type_id' => $this->type->id,
            'title' => 'Mendelian inheritance',
            'content' => 'Dominant and recessive alleles.',
        ]);

        $notes = $this->getJson(
            '/api/subjects/content?user_id='.$this->user->id.'&subject=Biology'
        )->assertStatus(200)->json('notes');

        $this->assertSame('Biology', $notes['name']);
        $this->assertCount(1, $notes['chapters']);
        $this->assertSame('Genetics', $notes['chapters'][0]['name']);

        // camelCase, matching NoteController::forUserGrouped and the app's
        // NoteModel.fromJson.
        $note = $notes['chapters'][0]['notes'][0];
        $this->assertArrayHasKey('subjectName', $note);
        $this->assertArrayHasKey('chapterName', $note);
        $this->assertArrayHasKey('createdAt', $note);
    }

    /** @test */
    public function content_skips_the_payload_when_the_client_already_has_the_current_version()
    {
        $this->subscribe();

        $subject = $this->subject('Biology', '2015');
        $this->question($subject);

        $version = $this->getJson('/api/subjects/catalogue?user_id='.$this->user->id)
            ->json('data.0.content_version');

        $this->assertNotNull($version);

        $this->getJson(
            '/api/subjects/content?user_id='.$this->user->id
            .'&subject=Biology&known_version='.urlencode($version)
        )
            ->assertStatus(200)
            ->assertJsonPath('status', 'not_modified')
            ->assertJsonMissingPath('questions');
    }

    /** @test */
    public function content_404s_for_a_subject_the_user_cannot_reach()
    {
        $this->subject('Biology', '2015');

        $this->getJson('/api/subjects/content?user_id='.$this->user->id.'&subject=Astrophysics')
            ->assertStatus(404);
    }

    /**
     * Regression guard: this endpoint used to accept a bare subject id with no
     * user, no exam-type filter and no subscription check.
     *
     * @test
     */
    public function questions_by_subject_requires_a_user()
    {
        $subject = $this->subject('Biology', '2015');
        $this->question($subject);

        $this->getJson('/api/questions/subject?subject='.$subject->id)
            ->assertStatus(422);
    }

    /** @test */
    public function questions_by_subject_caps_unsubscribed_users()
    {
        $subject = $this->subject('Biology', '2015');

        foreach (range(1, SubjectContentService::FREE_QUESTION_LIMIT + 3) as $i) {
            $this->question($subject);
        }

        $response = $this->getJson(
            '/api/questions/subject?subject='.$subject->id.'&user_id='.$this->user->id
        )->assertStatus(200);

        $returned = collect($response->json('response'))->flatten(1)->count();

        $this->assertSame(SubjectContentService::FREE_QUESTION_LIMIT, $returned);
    }

    /** @test */
    public function notes_for_user_grouped_can_be_narrowed_to_one_subject()
    {
        $biology = $this->subject('Biology', '2015');
        $chemistry = $this->subject('Chemistry', '2015');
        $chapter = Chapter::create(['name' => 'Genetics']);

        foreach ([$biology, $chemistry] as $subject) {
            Note::create([
                'subject_id' => $subject->id,
                'chapter_id' => $chapter->id,
                'type_id' => $this->type->id,
                'title' => 'Note for '.$subject->name,
                'content' => 'Body',
            ]);
        }

        $all = $this->getJson('/api/notes/for-user-grouped?user_id='.$this->user->id)
            ->assertStatus(200)->json('data');
        $this->assertCount(2, $all);

        $narrowed = $this->getJson(
            '/api/notes/for-user-grouped?user_id='.$this->user->id.'&subject=Biology'
        )->assertStatus(200)->json('data');

        $this->assertCount(1, $narrowed);
        $this->assertSame('Biology', $narrowed[0]['name']);
    }

    private function subject(string $name, string $year, ?string $region = null): Subject
    {
        return Subject::create([
            'name' => $name,
            'year' => $year,
            'region' => $region,
            'type_id' => $this->type->id,
            'default_duration' => 2,
            'is_sample' => false,
        ]);
    }

    private function question(Subject $subject, array $attributes = []): Question
    {
        return Question::create(array_merge([
            'subject_id' => $subject->id,
            'year_group_id' => $this->yearGroup->id,
            'type_id' => $this->type->id,
            'question_text' => 'Placeholder question',
            'explanation' => 'Placeholder explanation',
        ], $attributes));
    }

    private function subscribe(): void
    {
        Subscription::create([
            'user_id' => $this->user->id,
            'year_group_id' => $this->yearGroup->id,
            'type_id' => $this->type->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);
    }
}
