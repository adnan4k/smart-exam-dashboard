<?php

namespace App\Services;

use App\Exceptions\ContestException;
use App\Models\Contest;
use App\Models\ContestAttempt;
use App\Models\ContestAttemptAnswer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ContestService
{
    /**
     * Seconds of slack allowed after the deadline, to cover a slow upload on a
     * bad connection. Answers stamped later than this are not counted, so
     * holding the connection open buys a student nothing.
     */
    public const SUBMIT_GRACE_SECONDS = 60;

    /**
     * Open an attempt, or resume the one already in progress.
     *
     * The unique index on (contest_id, user_id) is the real guarantee that a
     * student sits a contest once; the lock here just turns a race into a clean
     * resume instead of a duplicate-key error.
     */
    public function start(Contest $contest, User $user, ?string $deviceId = null, ?string $ip = null): ContestAttempt
    {
        if ($contest->type_id && $user->type_id && $contest->type_id !== $user->type_id) {
            throw ContestException::wrongCohort();
        }

        return DB::transaction(function () use ($contest, $user, $deviceId, $ip) {
            $existing = ContestAttempt::where('contest_id', $contest->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->status !== 'in_progress') {
                    throw ContestException::alreadyAttempted();
                }

                if (! $existing->isOpen()) {
                    // Their time ran out while they were away. Score what we have.
                    $this->closeAttempt($existing, $existing->expires_at, 'submitted');

                    throw ContestException::attemptClosed();
                }

                return $existing;
            }

            if (! $contest->isLive()) {
                throw ContestException::notOpen();
            }

            if (! $contest->acceptsNewEntries()) {
                throw ContestException::joinWindowClosed();
            }

            // One entry per device, checked here rather than by a unique index so
            // an admin can clear a genuinely shared phone.
            if ($deviceId && ContestAttempt::where('contest_id', $contest->id)
                ->where('device_id', $deviceId)
                ->exists()) {
                throw ContestException::deviceAlreadyUsed();
            }

            $startedAt = now();

            return ContestAttempt::create([
                'contest_id'      => $contest->id,
                'user_id'         => $user->id,
                'device_id'       => $deviceId,
                'ip_address'      => $ip,
                'started_at'      => $startedAt,
                'expires_at'      => $contest->deadlineFor($startedAt),
                'status'          => 'in_progress',
                'total_questions' => $contest->contestQuestions()->count(),
            ]);
        });
    }

    /**
     * The whole paper, with the answers stripped out.
     *
     * Nothing here reveals which choice is correct - that only comes back after
     * the attempt is submitted.
     */
    public function paperFor(Contest $contest): array
    {
        $questions = Question::withUnreleased()
            ->with('choices')
            ->whereIn('id', $contest->contestQuestions()->pluck('question_id'))
            ->get()
            ->keyBy('id');

        return $contest->contestQuestions->map(function ($cq) use ($questions) {
            $question = $questions->get($cq->question_id);

            if (! $question) {
                return null;
            }

            return [
                'position'       => $cq->position,
                'points'         => $cq->points,
                'question_id'    => $question->id,
                'question_text'  => $question->question_text,
                'question_image' => $question->question_image_path
                    ? asset('storage/' . $question->question_image_path)
                    : null,
                'formula'        => $question->formula,
                'subject_id'     => $question->subject_id,
                'chapter_id'     => $question->chapter_id,
                // Shuffled per attempt so a screenshot of "B, D, A, C" is useless
                // to anyone else.
                'choices'        => $question->choices->shuffle()->map(fn ($choice) => [
                    'id'      => $choice->id,
                    'text'    => $choice->choice_text,
                    'image'   => $choice->choice_image_path
                        ? asset('storage/' . $choice->choice_image_path)
                        : null,
                    'formula' => $choice->formula,
                ])->values(),
            ];
        })->filter()->values()->all();
    }

    /**
     * Save answers mid-contest so a dropped connection or a dead battery does
     * not cost the student their paper.
     */
    public function syncAnswers(ContestAttempt $attempt, array $answers): int
    {
        if ($attempt->status !== 'in_progress') {
            throw ContestException::attemptClosed();
        }

        if (now()->gt($attempt->expires_at->copy()->addSeconds(self::SUBMIT_GRACE_SECONDS))) {
            $this->closeAttempt($attempt, $attempt->expires_at, 'submitted');

            throw ContestException::attemptClosed();
        }

        return $this->storeAnswers($attempt, $answers);
    }

    /**
     * Final submission. Answers that arrive within the grace window are saved
     * first; anything later is ignored and the attempt is scored on what the
     * server already holds.
     */
    public function submit(ContestAttempt $attempt, array $answers = []): ContestAttempt
    {
        if ($attempt->status !== 'in_progress') {
            throw ContestException::alreadyAttempted();
        }

        $deadline = $attempt->expires_at->copy()->addSeconds(self::SUBMIT_GRACE_SECONDS);
        $onTime   = now()->lte($deadline);

        if ($onTime && $answers) {
            $this->storeAnswers($attempt, $answers);
        }

        // A late submission is scored, but gets no credit for the extra time.
        $submittedAt = $onTime ? now() : $attempt->expires_at;

        return $this->closeAttempt($attempt, $submittedAt, 'submitted');
    }

    /**
     * Grade an attempt and close it. Scoring never trusts the client - the
     * correct choice is read from the question here and nowhere else.
     */
    public function closeAttempt(ContestAttempt $attempt, Carbon $submittedAt, string $status): ContestAttempt
    {
        return DB::transaction(function () use ($attempt, $submittedAt, $status) {
            $attempt->refresh();

            if ($attempt->status !== 'in_progress') {
                return $attempt;
            }

            $paper = $attempt->contest->contestQuestions()->get();

            $questions = Question::withUnreleased()
                ->whereIn('id', $paper->pluck('question_id'))
                ->get()
                ->keyBy('id');

            $answers = $attempt->answers()->get()->keyBy('question_id');

            $score = $correct = $wrong = $unanswered = 0;

            foreach ($paper as $contestQuestion) {
                $question = $questions->get($contestQuestion->question_id);
                $answer   = $answers->get($contestQuestion->question_id);

                if (! $answer || ! $answer->choice_id) {
                    $unanswered++;
                    continue;
                }

                $isCorrect = $question && $question->correctChoiceId()
                    && (int) $answer->choice_id === (int) $question->correctChoiceId();

                if ($isCorrect) {
                    $correct++;
                    $score += $contestQuestion->points;
                } else {
                    $wrong++;
                }

                if ($answer->is_correct !== $isCorrect) {
                    $answer->update(['is_correct' => $isCorrect]);
                }
            }

            $attempt->update([
                'status'             => $status,
                'submitted_at'       => $submittedAt,
                'score'              => $score,
                'correct_count'      => $correct,
                'wrong_count'        => $wrong,
                'unanswered_count'   => $unanswered,
                'total_questions'    => $paper->count(),
                'time_taken_seconds' => max(0, (int) floor($attempt->started_at->diffInSeconds($submittedAt))),
            ]);

            return $attempt->fresh();
        });
    }

    /**
     * Auto-submit attempts whose time has run out. Students who were cut off by
     * a power cut still get scored on what reached the server.
     */
    public function closeExpiredAttempts(?Contest $contest = null): int
    {
        $query = ContestAttempt::where('status', 'in_progress')
            ->where('expires_at', '<=', now()->subSeconds(self::SUBMIT_GRACE_SECONDS))
            ->when($contest, fn ($q) => $q->where('contest_id', $contest->id));

        $closed = 0;

        $query->with('contest')->chunkById(100, function ($attempts) use (&$closed) {
            foreach ($attempts as $attempt) {
                $hasAnswers = $attempt->answers()->whereNotNull('choice_id')->exists();
                $this->closeAttempt(
                    $attempt,
                    $attempt->expires_at,
                    $hasAnswers ? 'submitted' : 'expired'
                );
                $closed++;
            }
        });

        return $closed;
    }

    /**
     * Where a student stands right now, without waiting for the contest to close.
     */
    public function provisionalRank(ContestAttempt $attempt): int
    {
        return ContestAttempt::where('contest_id', $attempt->contest_id)
            ->where('status', 'submitted')
            ->where(function ($q) use ($attempt) {
                $q->where('score', '>', $attempt->score)
                    ->orWhere(function ($q) use ($attempt) {
                        $q->where('score', $attempt->score)
                            ->where('time_taken_seconds', '<', $attempt->time_taken_seconds);
                    });
            })
            ->count() + 1;
    }

    /**
     * Only answers to questions actually on the paper are stored.
     */
    private function storeAnswers(ContestAttempt $attempt, array $answers): int
    {
        $paperQuestionIds = $attempt->contest->contestQuestions()->pluck('question_id')->all();
        $saved = 0;

        foreach ($answers as $answer) {
            $questionId = (int) ($answer['question_id'] ?? 0);
            $choiceId   = $answer['choice_id'] ?? null;

            if (! in_array($questionId, $paperQuestionIds, true)) {
                continue;
            }

            ContestAttemptAnswer::updateOrCreate(
                [
                    'contest_attempt_id' => $attempt->id,
                    'question_id'        => $questionId,
                ],
                [
                    'choice_id'   => $choiceId ? (int) $choiceId : null,
                    'answered_at' => now(),
                ]
            );

            $saved++;
        }

        return $saved;
    }
}
