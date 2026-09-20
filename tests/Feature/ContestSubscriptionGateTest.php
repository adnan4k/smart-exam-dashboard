<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Choice;
use App\Models\Contest;
use App\Models\ContestAttempt;
use App\Models\ContestQuestion;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use App\Models\YearGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Contests are part of the paid exam-type package, like notes and videos.
 *
 * The gate lives in ContestService::start(). Everything that hands back paper
 * or answers - saveAnswers, submit, review - first requires an attempt, and
 * only start() creates one, so these tests pin both halves: that entry is
 * refused, and that refusing it really does put the paper out of reach.
 */
class ContestSubscriptionGateTest extends TestCase
{
    use RefreshDatabase;

    private Type $type;

    private User $user;

    private Contest $contest;

    private Question $question;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = Type::create(['name' => 'Freshman Exams', 'price' => '250']);
        $this->user = User::factory()->create(['type_id' => $this->type->id]);
        $this->contest = $this->contest();
        $this->question = $this->contestQuestion($this->contest);
    }

    /* ---------------------------------------------------------------- *
     | Entry                                                            |
     * ---------------------------------------------------------------- */

    /** @test */
    public function an_unsubscribed_student_cannot_enter_a_contest()
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/contests/'.$this->contest->id.'/start')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'subscription_required');

        $this->assertDatabaseCount('contest_attempts', 0);
    }

    /** @test */
    public function a_subscribed_student_can_enter()
    {
        $this->subscribe();
        Sanctum::actingAs($this->user);

        $this->postJson('/api/contests/'.$this->contest->id.'/start')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseCount('contest_attempts', 1);
    }

    /**
     * The package is bought per exam type. Paying for a different type is not
     * a pass into this student's cohort.
     *
     * @test
     */
    public function a_subscription_for_another_exam_type_does_not_unlock_contests()
    {
        $other = Type::create(['name' => 'Grade 12', 'price' => '250']);
        $this->subscribe($other);
        Sanctum::actingAs($this->user);

        $this->postJson('/api/contests/'.$this->contest->id.'/start')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'subscription_required');
    }

    /** @test */
    public function an_unpaid_subscription_row_does_not_unlock_contests()
    {
        $this->subscribe(status: 'pending');
        Sanctum::actingAs($this->user);

        $this->postJson('/api/contests/'.$this->contest->id.'/start')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'subscription_required');
    }

    /**
     * No feature in this app enforces start_date/end_date, so contests must not
     * either - a lapsed student keeps their notes and videos, and losing only
     * contests would be an invisible, inconsistent takeaway.
     *
     * @test
     */
    public function a_past_dated_subscription_still_unlocks_contests()
    {
        $this->subscribe(start: now()->subYears(2), end: now()->subYear());
        Sanctum::actingAs($this->user);

        $this->postJson('/api/contests/'.$this->contest->id.'/start')
            ->assertStatus(200);
    }

    /**
     * start() doubles as resume. A student who was entitled when they began
     * must be able to finish the paper in front of them even if their payment
     * is revoked mid-attempt.
     *
     * @test
     */
    public function a_revoked_student_can_still_finish_an_attempt_already_in_progress()
    {
        $subscription = $this->subscribe();
        Sanctum::actingAs($this->user);

        $this->postJson('/api/contests/'.$this->contest->id.'/start')->assertStatus(200);

        $subscription->update(['payment_status' => 'failed']);

        $this->postJson('/api/contests/'.$this->contest->id.'/start')->assertStatus(200);

        $this->postJson('/api/contests/'.$this->contest->id.'/answers', [
            'answers' => [['question_id' => $this->question->id, 'choice_id' => $this->question->choices->first()->id]],
        ])->assertStatus(200);

        $this->postJson('/api/contests/'.$this->contest->id.'/submit')->assertStatus(200);
    }

    /* ---------------------------------------------------------------- *
     | Reach: no attempt means no paper                                  |
     * ---------------------------------------------------------------- */

    /** @test */
    public function an_unsubscribed_student_cannot_reach_the_paper_or_the_answer_key()
    {
        Sanctum::actingAs($this->user);
        $id = $this->contest->id;

        // No attempt can exist, so every downstream endpoint is closed too.
        $this->postJson("/api/contests/{$id}/answers", [
            'answers' => [['question_id' => $this->question->id, 'choice_id' => null]],
        ])->assertStatus(404)->assertJsonPath('error_code', 'no_attempt');

        $this->postJson("/api/contests/{$id}/submit")
            ->assertStatus(404)->assertJsonPath('error_code', 'no_attempt');

        $this->getJson("/api/contests/{$id}/review")
            ->assertStatus(403)->assertJsonPath('error_code', 'subscription_required');
    }

    /**
     * review() hands back the answer key for questions that have not reached
     * the study bank yet, so it states the rule itself rather than relying on
     * the attempt check alone.
     *
     * @test
     */
    public function review_refuses_an_unsubscribed_student_even_with_a_submitted_attempt()
    {
        $subscription = $this->subscribe();
        Sanctum::actingAs($this->user);

        $this->postJson('/api/contests/'.$this->contest->id.'/start')->assertStatus(200);
        $this->postJson('/api/contests/'.$this->contest->id.'/submit')->assertStatus(200);

        $this->getJson('/api/contests/'.$this->contest->id.'/review')->assertStatus(200);

        $subscription->update(['payment_status' => 'failed']);

        $this->getJson('/api/contests/'.$this->contest->id.'/review')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'subscription_required');
    }

    /* ---------------------------------------------------------------- *
     | Browsing stays open                                               |
     * ---------------------------------------------------------------- */

    /** @test */
    public function an_unsubscribed_student_still_sees_contests_marked_locked()
    {
        Sanctum::actingAs($this->user);

        $row = $this->getJson('/api/contests')->assertStatus(200)->json('contests.0');

        $this->assertSame($this->contest->id, $row['id']);
        $this->assertTrue($row['locked']);
        $this->assertSame('subscription_required', $row['lock_reason']);
        // The prize bands are the sales pitch, so they are not withheld.
        $this->assertArrayHasKey('question_count', $row);
    }

    /** @test */
    public function show_tells_an_unsubscribed_student_what_to_buy()
    {
        Sanctum::actingAs($this->user);

        $body = $this->getJson('/api/contests/'.$this->contest->id)
            ->assertStatus(200)->json('contest');

        $this->assertTrue($body['locked']);
        $this->assertSame('subscription_required', $body['lock_reason']);
        $this->assertSame($this->type->id, $body['package']['type_id']);
        $this->assertSame('Freshman Exams', $body['package']['name']);
        $this->assertSame('250', $body['package']['price']);
    }

    /** @test */
    public function a_subscribed_student_sees_nothing_locked()
    {
        $this->subscribe();
        Sanctum::actingAs($this->user);

        $this->getJson('/api/contests')->assertStatus(200)
            ->assertJsonPath('contests.0.locked', false)
            ->assertJsonPath('contests.0.lock_reason', null);

        $this->getJson('/api/contests/'.$this->contest->id)->assertStatus(200)
            ->assertJsonPath('contest.locked', false)
            ->assertJsonPath('contest.package', null);
    }

    /** @test */
    public function leaderboards_stay_open_to_everyone()
    {
        Sanctum::actingAs($this->user);

        $this->getJson('/api/contests/'.$this->contest->id.'/leaderboard')->assertStatus(200);
        $this->getJson('/api/contests/leaderboard')->assertStatus(200);
        $this->getJson('/api/me/contest-attempts')->assertStatus(200);
        $this->getJson('/api/me/rewards')->assertStatus(200);
    }

    /** @test */
    public function contest_routes_still_require_a_token()
    {
        $this->postJson('/api/contests/'.$this->contest->id.'/start')->assertStatus(401);
        $this->getJson('/api/contests')->assertStatus(401);
    }

    /* ---------------------------------------------------------------- */

    private function subscribe(?Type $type = null, string $status = 'paid', $start = null, $end = null): Subscription
    {
        return Subscription::create([
            'user_id' => $this->user->id,
            'year_group_id' => YearGroup::create(['year' => 2024])->id,
            'type_id' => ($type ?? $this->type)->id,
            'start_date' => $start ?? now()->subDay(),
            'end_date' => $end ?? now()->addYear(),
            'payment_status' => $status,
        ]);
    }

    private function contest(): Contest
    {
        return Contest::create([
            'title' => 'Weekly Challenge',
            'description' => 'A contest',
            'type_id' => $this->type->id,
            'duration_minutes' => 40,
            'join_window_minutes' => 10,
            'starts_at' => now()->subMinutes(2),
            'ends_at' => now()->addHour(),
            'status' => 'scheduled',
            'question_count' => 1,
        ]);
    }

    private function contestQuestion(Contest $contest): Question
    {
        $subject = Subject::create(['name' => 'Logic', 'year' => '2015', 'type_id' => $this->type->id]);

        $question = Question::create([
            'subject_id' => $subject->id,
            'chapter_id' => Chapter::create(['name' => 'Arguments'])->id,
            'type_id' => $this->type->id,
            'question_text' => 'What is 10 + 15?',
            'explanation' => 'Addition.',
            // Contest questions are hidden from the study bank until released.
            'bank' => 'contest',
            'released_at' => null,
        ]);

        foreach (['25', '30'] as $text) {
            Choice::create(['question_id' => $question->id, 'choice_text' => $text]);
        }

        $question->update(['correct_choice_id' => $question->choices()->first()->id]);

        ContestQuestion::create([
            'contest_id' => $contest->id,
            'question_id' => $question->id,
            'position' => 1,
            'points' => 1,
        ]);

        return $question->load('choices');
    }
}
