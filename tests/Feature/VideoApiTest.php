<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use App\Models\Video;
use App\Models\YearGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * GET /api/videos/by-subject?subject_id=…&user_id=…
 *
 * The endpoint is a listing, but it is also the only place the app learns
 * whether a download is possible, so these tests pin the three flags it emits
 * together — `locked`, `file_available` and `download_url` — rather than just
 * the tree shape.
 */
class VideoApiTest extends TestCase
{
    use RefreshDatabase;

    private Type $type;

    private User $user;

    private Subject $subject;

    private Chapter $chapter;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Video::DISK);
        Storage::fake(Video::THUMB_DISK);

        $this->type = Type::create(['name' => 'Freshman Exams']);
        $this->user = User::factory()->create(['type_id' => $this->type->id]);
        $this->subject = Subject::create([
            'name' => 'Psychology',
            'year' => '2015',
            'type_id' => $this->type->id,
        ]);
        $this->chapter = Chapter::create(['name' => 'Essence of Psychology']);
    }

    /** @test */
    public function by_subject_groups_videos_under_their_chapter()
    {
        $this->subscribe();

        $chaptered = $this->video(['chapter_id' => $this->chapter->id, 'title' => 'Chapter one']);
        $loose = $this->video(['chapter_id' => null, 'title' => 'Subject intro']);

        $body = $this->getJson(
            '/api/videos/by-subject?subject_id='.$this->subject->id.'&user_id='.$this->user->id
        )->assertStatus(200)->assertJsonPath('status', 'success')->json();

        $this->assertTrue($body['entitled']);
        $this->assertSame($this->subject->id, $body['data']['subject_id']);
        $this->assertSame('Psychology', $body['data']['subject_name']);

        // A video with no chapter belongs to the subject itself.
        $this->assertCount(1, $body['data']['subject_videos']);
        $this->assertSame($loose->id, $body['data']['subject_videos'][0]['id']);

        $this->assertCount(1, $body['data']['chapters']);
        $this->assertSame($this->chapter->id, $body['data']['chapters'][0]['chapter_id']);
        $this->assertSame('Essence of Psychology', $body['data']['chapters'][0]['chapter_name']);
        $this->assertSame($chaptered->id, $body['data']['chapters'][0]['videos'][0]['id']);
    }

    /** @test */
    public function by_subject_hands_a_subscribed_user_a_working_download_url()
    {
        $this->subscribe();
        $video = $this->video(['chapter_id' => $this->chapter->id]);

        $payload = $this->firstVideo();

        $this->assertFalse($payload['locked']);
        $this->assertTrue($payload['file_available']);
        $this->assertSame($video->downloadUrl($this->user->id), $payload['download_url']);

        // The advertised link has to actually serve the file.
        $this->get($payload['download_url'])->assertStatus(200);
    }

    /** @test */
    public function by_subject_withholds_the_download_url_from_an_unsubscribed_user()
    {
        $this->video(['chapter_id' => $this->chapter->id]);

        $payload = $this->firstVideo();

        $this->assertTrue($payload['locked']);
        $this->assertNull($payload['download_url']);
        // Metadata still ships: the app renders a locked card from it.
        $this->assertSame('Lecture', $payload['title']);
    }

    /** @test */
    public function by_subject_locks_a_video_belonging_to_another_exam_type()
    {
        $this->subscribe();
        $otherType = Type::create(['name' => 'Grade 12']);
        $this->video(['chapter_id' => $this->chapter->id, 'type_id' => $otherType->id]);

        $payload = $this->firstVideo();

        $this->assertTrue($payload['locked'], 'Another exam type is not this user to download.');
        $this->assertNull($payload['download_url']);
    }

    /**
     * Production state of video id 1: the row carries a file_size and checksum
     * but the file is gone from the private disk, so `download()` answers
     * `file_missing`. The listing must not advertise a link that can only fail.
     *
     * @test
     */
    public function by_subject_advertises_no_download_when_the_file_is_missing_from_disk()
    {
        $this->subscribe();
        $video = $this->video(['chapter_id' => $this->chapter->id]);

        Storage::disk(Video::DISK)->delete($video->file_path);

        $payload = $this->firstVideo();

        $this->assertFalse($payload['file_available']);
        $this->assertNull($payload['download_url']);

        $this->getJson('/api/videos/'.$video->id.'/download?user_id='.$this->user->id)
            ->assertStatus(404)
            ->assertJsonPath('reason', 'file_missing');
    }

    /** @test */
    public function by_subject_omits_inactive_videos()
    {
        $this->subscribe();
        $this->video(['chapter_id' => $this->chapter->id, 'is_active' => false]);

        $body = $this->getJson(
            '/api/videos/by-subject?subject_id='.$this->subject->id.'&user_id='.$this->user->id
        )->assertStatus(200)->json('data');

        $this->assertSame([], $body['subject_videos']);
        $this->assertSame([], $body['chapters']);
    }

    /** @test */
    public function by_subject_can_be_narrowed_to_one_language()
    {
        $this->subscribe();
        $this->video(['chapter_id' => $this->chapter->id, 'language' => 'english']);
        $amharic = $this->video(['chapter_id' => $this->chapter->id, 'language' => 'amharic']);

        $videos = $this->getJson(
            '/api/videos/by-subject?subject_id='.$this->subject->id
            .'&user_id='.$this->user->id.'&language=amharic'
        )->assertStatus(200)->json('data.chapters.0.videos');

        $this->assertCount(1, $videos);
        $this->assertSame($amharic->id, $videos[0]['id']);
    }

    /**
     * file_path points into private storage. Leaking it would let a client
     * bypass the download gate if the disk were ever served over HTTP.
     *
     * @test
     */
    public function by_subject_never_leaks_the_private_file_path()
    {
        $this->subscribe();
        $video = $this->video(['chapter_id' => $this->chapter->id]);

        $response = $this->getJson(
            '/api/videos/by-subject?subject_id='.$this->subject->id.'&user_id='.$this->user->id
        )->assertStatus(200);

        $this->assertArrayNotHasKey('file_path', $this->firstVideo());
        $this->assertStringNotContainsString($video->file_path, $response->getContent());
    }

    /** @test */
    public function by_subject_works_without_a_user_and_locks_everything()
    {
        $this->video(['chapter_id' => $this->chapter->id]);

        $body = $this->getJson('/api/videos/by-subject?subject_id='.$this->subject->id)
            ->assertStatus(200)->json();

        $this->assertFalse($body['entitled']);
        $this->assertTrue($body['data']['chapters'][0]['videos'][0]['locked']);
        $this->assertNull($body['data']['chapters'][0]['videos'][0]['download_url']);
    }

    /** @test */
    public function by_subject_rejects_an_unknown_subject_or_user()
    {
        $this->getJson('/api/videos/by-subject?subject_id=999999')->assertStatus(422);
        $this->getJson('/api/videos/by-subject')->assertStatus(422);
        $this->getJson('/api/videos/by-subject?subject_id='.$this->subject->id.'&user_id=999999')
            ->assertStatus(422);
        $this->getJson(
            '/api/videos/by-subject?subject_id='.$this->subject->id.'&language=klingon'
        )->assertStatus(422);
    }

    /** @test */
    public function by_subject_returns_an_empty_tree_for_a_subject_with_no_videos()
    {
        $empty = Subject::create(['name' => 'Logic', 'year' => '2015', 'type_id' => $this->type->id]);

        $body = $this->getJson(
            '/api/videos/by-subject?subject_id='.$empty->id.'&user_id='.$this->user->id
        )->assertStatus(200)->json('data');

        $this->assertSame([], $body['subject_videos']);
        $this->assertSame([], $body['chapters']);
        $this->assertSame('Logic', $body['subject_name']);
    }

    /* ---------------------------------------------------------------- */

    private function firstVideo(): array
    {
        $data = $this->getJson(
            '/api/videos/by-subject?subject_id='.$this->subject->id.'&user_id='.$this->user->id
        )->assertStatus(200)->json('data');

        return $data['subject_videos'][0] ?? $data['chapters'][0]['videos'][0];
    }

    private function video(array $attributes = []): Video
    {
        $path = UploadedFile::fake()
            ->create('lecture.mp4', 64, 'video/mp4')
            ->store('videos', Video::DISK);

        return Video::create(array_merge([
            'user_id' => $this->user->id,
            'type_id' => $this->type->id,
            'subject_id' => $this->subject->id,
            'chapter_id' => $this->chapter->id,
            'title' => 'Lecture',
            'description' => 'Lecture description',
            'file_path' => $path,
            'mime_type' => 'video/mp4',
            'file_size' => 65536,
            'checksum' => md5('lecture'),
            'duration' => 34,
            'language' => 'english',
            'sort_order' => 0,
            'is_active' => true,
        ], $attributes));
    }

    private function subscribe(): void
    {
        Subscription::create([
            'user_id' => $this->user->id,
            'year_group_id' => YearGroup::create(['year' => 2024])->id,
            'type_id' => $this->type->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addYear(),
            'payment_status' => 'paid',
        ]);
    }
}
