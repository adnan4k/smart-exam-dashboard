<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Choice;
use App\Models\Contest;
use App\Models\ContestAttempt;
use App\Models\ContestQuestion;
use App\Models\ContestRewardRule;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use App\Models\YearGroup;
use App\Services\ContestFinalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * A contest pays a configurable number of stars per correct answer, and its
 * leaderboard ranks on those stars.
 */
class ContestStarsTest extends TestCase
{
    use RefreshDatabase;

    private Type $type;

    private Contest $contest;

    /** @var Question[] */
    private array $questions = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = Type::create(['name' => 'Freshman Exams', 'price' => '250']);

        $this->contest = Contest::create([
            'title'              => 'Star Round',
            'type_id'            => $this->type->id,
            'duration_minutes'   => 40,
            'stars_per_question' => 3,
            'starts_at'          => now()->subMinutes(2),
            'ends_at'            => now()->addHour(),
            'status'             => 'scheduled',
            'question_count'     => 3,
        ]);

        $subject = Subject::create(['name' => 'Logic', 'year' => '2015', 'type_id' => $this->type->id]);
        $chapter = Chapter::create(['name' => 'Arguments']);

        foreach ([1, 2, 3] as $position) {
            $question = Question::create([
                'subject_id'    => $subject->id,
                'chapter_id'    => $chapter->id,
                'type_id'       => $this->type->id,
                'question_text' => "Question {$position}",
                'explanation'   => 'Because.',
                'bank'          => 'contest',
                'released_at'   => null,
            ]);

            foreach (['right', 'wrong'] as $text) {
                Choice::create(['question_id' => $question->id, 'choice_text' => $text]);
            }

            $question->update(['correct_choice_id' => $question->choices()->where('choice_text', 'right')->value('id')]);

            ContestQuestion::create([
                'contest_id'  => $this->contest->id,
                'question_id' => $question->id,
                'position'    => $position,
                'points'      => 1,
            ]);

            $this->questions[] = $question->load('choices');
        }
    }

    /** @test */
    public function submitting_earns_stars_for_each_correct_answer()
    {
        $user = $this->student();

        $result = $this->sit($user, correct: 2);

        $result->assertOk()
            ->assertJsonPath('result.correct', 2)
            ->assertJsonPath('result.question_stars', 6);

        $this->assertSame(6, ContestAttempt::where('user_id', $user->id)->value('question_stars'));
    }

    /** @test */
    public function the_contest_leaderboard_ranks_by_stars_earned()
    {
        $low  = $this->student();
        $high = $this->student();

        $this->sit($low, correct: 1);
        $this->sit($high, correct: 3);

        Sanctum::actingAs($low);

        $this->getJson("/api/contests/{$this->contest->id}/leaderboard")
            ->assertOk()
            ->assertJsonPath('leaderboard.0.user_id', $high->id)
            ->assertJsonPath('leaderboard.0.stars', 9)
            ->assertJsonPath('leaderboard.1.user_id', $low->id)
            ->assertJsonPath('leaderboard.1.stars', 3)
            ->assertJsonPath('me.rank', 2);
    }

    /** @test */
    public function finalizing_pays_question_stars_plus_any_rank_bonus_into_the_global_leaderboard()
    {
        ContestRewardRule::create([
            'contest_id' => $this->contest->id,
            'rank_from'  => 1,
            'rank_to'    => 1,
            'stars'      => 10,
            'coins'      => 0,
            'is_active'  => true,
        ]);

        $winner = $this->student();
        $runner = $this->student();

        $this->sit($winner, correct: 3);
        $this->sit($runner, correct: 2);

        $this->travel(2)->hours();
        app(ContestFinalizer::class)->finalize($this->contest->fresh());

        // 3 correct x 3 stars + 10 for first place; 2 x 3 with no bonus.
        $this->assertSame(19, $winner->fresh()->total_stars);
        $this->assertSame(6, $runner->fresh()->total_stars);

        Sanctum::actingAs($runner);

        $this->getJson('/api/contests/leaderboard')
            ->assertOk()
            ->assertJsonPath('leaderboard.0.user_id', $winner->id)
            ->assertJsonPath('leaderboard.0.stars', 19)
            ->assertJsonPath('leaderboard.1.user_id', $runner->id)
            ->assertJsonPath('leaderboard.1.stars', 6);
    }

    /** @test */
    public function top_students_returns_the_three_highest_star_earners()
    {
        $students = [];
        foreach ([3, 1, 2, 3, 0, 1] as $correct) {
            $student = $this->student();
            $this->sit($student, correct: $correct);
            $students[] = $student;
        }

        $this->travel(2)->hours();
        app(ContestFinalizer::class)->finalize($this->contest->fresh());

        Sanctum::actingAs($students[0]);

        $this->getJson('/api/contests/top-students')
            ->assertOk()
            ->assertJsonCount(3, 'top_students')
            ->assertJsonPath('top_students.0.stars', 9)
            ->assertJsonPath('top_students.1.stars', 9)
            ->assertJsonPath('top_students.2.stars', 6)
            ->assertJsonPath('top_students.2.user_id', $students[2]->id)
            // Two students tied on 9 share first place; the next is third.
            ->assertJsonPath('top_students.0.rank', 1)
            ->assertJsonPath('top_students.1.rank', 1)
            ->assertJsonPath('top_students.2.rank', 3)
            ->assertJsonPath('me.rank', 1)
            ->assertJsonPath('me.stars', 9);

        // Outside the top three, the caller still sees where they stand.
        Sanctum::actingAs($students[1]);

        $this->getJson('/api/contests/top-students')
            ->assertJsonPath('me.user_id', $students[1]->id)
            ->assertJsonPath('me.rank', 4)
            ->assertJsonPath('me.stars', 3);

        // No stars earned yet means no rank.
        Sanctum::actingAs($students[4]);

        $this->getJson('/api/contests/top-students')
            ->assertJsonPath('me.rank', null)
            ->assertJsonPath('me.stars', 0);
    }

    /** @test */
    public function each_contest_shows_the_students_own_rank_in_it()
    {
        $first  = $this->student();
        $second = $this->student();
        $absent = $this->student();

        $this->sit($first, correct: 3);
        $this->sit($second, correct: 1);

        // While the contest is running the rank is live.
        Sanctum::actingAs($second);

        $this->getJson('/api/contests')
            ->assertOk()
            ->assertJsonPath('contests.0.my_rank', 2)
            ->assertJsonPath('contests.0.my_rank_is_final', false)
            ->assertJsonPath('contests.0.participants', 2);

        $this->getJson("/api/contests/{$this->contest->id}")
            ->assertJsonPath('my_attempt.rank', 2)
            ->assertJsonPath('my_attempt.participants', 2);

        // Once finalized it is the settled rank.
        $this->travel(2)->hours();
        app(ContestFinalizer::class)->finalize($this->contest->fresh());

        Sanctum::actingAs($first);

        $this->getJson('/api/contests')
            ->assertJsonPath('contests.0.my_rank', 1)
            ->assertJsonPath('contests.0.my_rank_is_final', true);

        // A student who never entered has no rank in it.
        Sanctum::actingAs($absent);

        $this->getJson('/api/contests')
            ->assertJsonPath('contests.0.my_rank', null)
            ->assertJsonPath('contests.0.participants', 2);
    }

    /** @test */
    public function a_contest_with_zero_stars_per_question_pays_none()
    {
        $this->contest->update(['stars_per_question' => 0]);
        $user = $this->student();

        $this->sit($user, correct: 3)->assertJsonPath('result.question_stars', 0);
    }

    private function student(): User
    {
        $user = User::factory()->create(['type_id' => $this->type->id]);

        Subscription::create([
            'user_id'        => $user->id,
            'year_group_id'  => YearGroup::create(['year' => 2024])->id,
            'type_id'        => $this->type->id,
            'start_date'     => now()->subDay(),
            'end_date'       => now()->addYear(),
            'payment_status' => 'paid',
        ]);

        return $user;
    }

    /**
     * Enter the contest and submit, answering the first $correct questions
     * right and the rest wrong.
     */
    private function sit(User $user, int $correct)
    {
        Sanctum::actingAs($user);

        $this->postJson("/api/contests/{$this->contest->id}/start", [
            'device_id' => 'device-' . $user->id,
        ])->assertOk();

        $answers = [];
        foreach ($this->questions as $i => $question) {
            $answers[] = [
                'question_id' => $question->id,
                'choice_id'   => $question->choices
                    ->firstWhere('choice_text', $i < $correct ? 'right' : 'wrong')->id,
            ];
        }

        return $this->postJson("/api/contests/{$this->contest->id}/submit", ['answers' => $answers]);
    }
}
