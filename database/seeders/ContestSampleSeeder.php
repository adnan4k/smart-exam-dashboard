<?php

namespace Database\Seeders;

use App\Models\Choice;
use App\Models\Contest;
use App\Models\ContestQuestion;
use App\Models\Question;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ContestService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * A small, live contest for exercising every contest feature by hand.
 *
 *   php artisan db:seed --class=ContestSampleSeeder
 *
 * Safe to re-run: it removes the previous sample contest, its questions and
 * the sample students before recreating them.
 *
 * Creates:
 *  - 5 contest-bank questions (hidden from the study API)
 *  - 1 live contest (started 10 minutes ago, open for 3 hours, 20-minute paper,
 *    10 stars per correct answer), scheduled for type 1
 *  - 3 paid students: two have already submitted (leaderboard has data), the
 *    third is fresh so you can sit the paper yourself through the API
 *  - the global reward bands, if they are missing
 *
 * LOCAL USE ONLY. Never run this on production.
 */
class ContestSampleSeeder extends Seeder
{
    public const TITLE = '[Sample] Weekly Math Sprint';

    private const PASSWORD = 'password';

    /** [question, choices, index of the correct choice, explanation] */
    private const QUESTIONS = [
        ['What is 7 x 8?',                          ['54', '56', '58', '64'],           1, '7 x 8 = 56.'],
        ['Solve for x: 2x + 6 = 14',                ['3', '4', '5', '8'],               1, '2x = 8, so x = 4.'],
        ['What is the square root of 144?',         ['10', '11', '12', '14'],           2, '12 x 12 = 144.'],
        ['How many degrees are in a triangle?',     ['90', '180', '270', '360'],        1, 'Interior angles of a triangle sum to 180 degrees.'],
        ['What is 15% of 200?',                     ['15', '20', '30', '45'],           2, '0.15 x 200 = 30.'],
    ];

    public function run(ContestService $contests): void
    {
        if (app()->environment('production')) {
            $this->command?->error('ContestSampleSeeder refuses to run in production.');

            return;
        }

        Model::unguarded(function () use ($contests) {
            $this->call(ContestRewardRuleSeeder::class);

            $this->cleanUp();

            $typeId    = 1;
            $subjectId = 1;

            $contest = Contest::create([
                'title'              => self::TITLE,
                'description'        => 'Five quick arithmetic questions. Sample data for testing contests.',
                'type_id'            => $typeId,
                'subject_id'         => $subjectId,
                'duration_minutes'   => 20,
                'stars_per_question' => 10,
                'starts_at'          => now()->subMinutes(10),
                'ends_at'            => now()->addHours(3),
                'status'             => 'scheduled',
                'created_by'         => User::where('email', 'admin@gmail.com')->value('id'),
            ]);

            foreach (self::QUESTIONS as $position => [$text, $choices, $correct, $explanation]) {
                $question = Question::create([
                    'question_text' => $text,
                    'explanation'   => $explanation,
                    'subject_id'    => $subjectId,
                    'type_id'       => $typeId,
                    'difficulty'    => 'easy',
                    'bank'          => 'contest',
                    'released_at'   => null,
                ]);

                $choiceIds = [];
                foreach ($choices as $choiceText) {
                    $choiceIds[] = Choice::create([
                        'question_id' => $question->id,
                        'choice_text' => $choiceText,
                    ])->id;
                }

                $question->update([
                    'correct_choice_id' => $choiceIds[$correct],
                    'answer_id'         => $choiceIds[$correct],
                ]);

                ContestQuestion::create([
                    'contest_id'  => $contest->id,
                    'question_id' => $question->id,
                    'position'    => $position + 1,
                    'points'      => 1,
                ]);
            }

            $contest->update(['question_count' => count(self::QUESTIONS)]);

            $students = [
                // email => [name, correct answers to submit (null = leave fresh), minutes taken]
                'student1@sample.test' => ['Sample Student One',   5,    6],
                'student2@sample.test' => ['Sample Student Two',   3,    9],
                'student3@sample.test' => ['Sample Student Three', null, null],
            ];

            foreach ($students as $email => [$name, $correctCount, $minutes]) {
                $user = User::create([
                    'name'     => $name,
                    'email'    => $email,
                    'password' => Hash::make(self::PASSWORD),
                    'type_id'  => $typeId,
                ]);

                Subscription::create([
                    'user_id'        => $user->id,
                    'package_id'     => 1,
                    'type_id'        => $typeId,
                    'payment_status' => 'paid',
                    'start_date'     => now()->toDateString(),
                    'end_date'       => now()->addDays(30)->toDateString(),
                    'amount'         => 0,
                ]);

                if ($correctCount !== null) {
                    $this->sitPaper($contests, $contest, $user, $correctCount, $minutes);
                }
            }

            $this->report($contest);
        });
    }

    /**
     * Goes through the real start -> save -> submit path so grading, stars and
     * ranks are computed exactly as they would be for a real student.
     */
    private function sitPaper(ContestService $contests, Contest $contest, User $user, int $correctCount, int $minutes): void
    {
        $attempt = $contests->start($contest, $user, 'sample-device-' . $user->id, '127.0.0.1');

        // Backdate the start so time-taken (the final tiebreak) is realistic.
        $startedAt = now()->subMinutes($minutes);
        $attempt->update([
            'started_at' => $startedAt,
            'expires_at' => $contest->deadlineFor($startedAt),
        ]);

        $answers = [];
        foreach ($contest->contestQuestions()->with('question.choices')->get() as $i => $cq) {
            $question = $cq->question;
            $right    = $question->correctChoiceId();
            $wrong    = $question->choices->firstWhere('id', '!=', $right)->id;

            $answers[] = [
                'question_id' => $question->id,
                'choice_id'   => $i < $correctCount ? $right : $wrong,
            ];
        }

        $contests->submit($attempt->fresh(), $answers);
    }

    private function cleanUp(): void
    {
        foreach (Contest::where('title', self::TITLE)->get() as $old) {
            $questionIds = $old->contestQuestions()->pluck('question_id');

            $old->delete(); // cascades contest_questions and attempts

            Question::withUnreleased()->whereIn('id', $questionIds)->get()->each(function ($q) {
                $q->choices()->delete();
                $q->delete();
            });
        }

        $users = User::where('email', 'like', '%@sample.test')->get();
        foreach ($users as $user) {
            $user->tokens()->delete();
            Subscription::where('user_id', $user->id)->delete();
            $user->delete();
        }
    }

    private function report(Contest $contest): void
    {
        $fresh = User::where('email', 'student3@sample.test')->first();
        $token = $fresh->createToken('contest-sample')->plainTextToken;

        $this->command?->info("Contest #{$contest->id} \"{$contest->title}\" is live until {$contest->ends_at} (UTC).");
        $this->command?->line('Students (password: ' . self::PASSWORD . '): student1@sample.test (5/5, submitted), student2@sample.test (3/5, submitted), student3@sample.test (fresh).');
        $this->command?->line("Bearer token for student3: {$token}");
    }
}
