<?php

namespace App\Services;

use App\Models\Choice;
use App\Models\Note;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Builds the payloads behind the per-subject download feature.
 *
 * ## Why this class exists: one subject is many `subjects` rows
 *
 * The `subjects` table stores one row per (name, year, region) combination —
 * `year` and `region` are columns on `subjects`, and the unique index on `name`
 * was dropped in 2025_05_15_202215. So "Biology" is not a row, it is a set of
 * rows.
 *
 * The mobile app has always presented one card per distinct *name*, and grouped
 * questions by `subject.name`. This service makes that grouping explicit and
 * server-side, so that one card tap maps to exactly one HTTP request. Keying the
 * download on a raw `subjects.id` instead would download a single year of a
 * single region, which is not a unit any user recognises.
 *
 * The grouping key is therefore the subject name, scoped to the user's exam type.
 */
class SubjectContentService
{
    /**
     * Per-record JSON scaffolding (keys, punctuation, ids, timestamps, image
     * URLs) that the text-length aggregates below do not account for. Used only
     * to turn raw text length into a size estimate the app can show on a card
     * before the download starts. Deliberately rough.
     */
    private const QUESTION_JSON_OVERHEAD_BYTES = 512;

    private const NOTE_JSON_OVERHEAD_BYTES = 256;

    /**
     * Questions handed to a user without a paid subscription, per subject.
     * Mirrors the existing cap in QuestionController::getQuestionsByType so that
     * moving the app onto these endpoints does not change who can see what.
     */
    public const FREE_QUESTION_LIMIT = 40;

    /**
     * Matches QuestionController::getQuestionsByType exactly (no type_id
     * predicate). NoteController::forUserGrouped additionally filters on
     * type_id; that inconsistency predates this change and is left alone.
     */
    public function isSubscribed(User $user): bool
    {
        return $user->subscriptions()
            ->where('payment_status', 'paid')
            ->exists();
    }

    /**
     * One entry per distinct subject name available to this user.
     *
     * Small enough to fetch on every app launch — it carries counts, sizes and a
     * content version, but no question or note bodies.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function catalogue(User $user): Collection
    {
        $typeId = $user->type_id;

        $subjectIds = Question::where('type_id', $typeId)
            ->distinct()
            ->pluck('subject_id')
            ->filter()
            ->values();

        if ($subjectIds->isEmpty()) {
            return collect();
        }

        $variants = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();

        $questionStats = $this->questionStats($typeId, $subjectIds);
        $choiceStats = $this->choiceStats($typeId, $subjectIds);
        $noteStats = $this->noteStats($typeId, $subjectIds);

        return $variants
            ->groupBy('name')
            ->map(fn (EloquentCollection $rows, string $name) => $this->catalogueEntry(
                $name,
                $rows,
                $questionStats,
                $choiceStats,
                $noteStats
            ))
            ->values();
    }

    /**
     * Every `subjects` row belonging to one subject name, for this user's type.
     *
     * Returns an empty collection for an unknown name, which callers translate
     * into a 404.
     */
    public function variantsFor(User $user, string $subjectName): EloquentCollection
    {
        $typeId = $user->type_id;

        $subjectIds = Question::where('type_id', $typeId)
            ->distinct()
            ->pluck('subject_id')
            ->filter();

        return Subject::whereIn('id', $subjectIds)
            ->where('name', $subjectName)
            ->get();
    }

    /**
     * Every question for one subject name, already transformed for the app.
     *
     * Unsubscribed users get the first FREE_QUESTION_LIMIT questions.
     *
     * @param  EloquentCollection<int, Subject>  $variants
     * @return array<int, array<string, mixed>>
     */
    public function questionsFor(EloquentCollection $variants, bool $isSubscribed, int $typeId): array
    {
        $query = Question::where('type_id', $typeId)
            ->whereIn('subject_id', $variants->pluck('id'))
            ->with(['choices', 'subject', 'yearGroup', 'chapter'])
            ->orderBy('id', 'asc');

        if (! $isSubscribed) {
            $query->limit(self::FREE_QUESTION_LIMIT);
        }

        return $query->get()
            ->map(fn (Question $question) => $this->transformQuestion($question))
            ->values()
            ->all();
    }

    /**
     * Notes for one subject name, shaped as the app's NoteSubjectModel:
     * { id, name, chapters: [ { id, name, notes: [...] } ] }.
     *
     * Chapters are merged across year/region variants and de-duplicated, because
     * chapter rows are global (the `chapters.subject_id` column was dropped in
     * 2025_06_25_235941) and the same chapter recurs across years.
     *
     * Keys are camelCase to match NoteController::forUserGrouped, which is what
     * the app's NoteModel.fromJson already parses.
     *
     * @param  EloquentCollection<int, Subject>  $variants
     * @return array<string, mixed>
     */
    public function notesFor(EloquentCollection $variants, string $subjectName, int $typeId): array
    {
        $subjectIds = $variants->pluck('id');

        $notes = Note::with(['subject', 'chapter'])
            ->whereIn('subject_id', $subjectIds)
            ->where(fn ($q) => $q->whereNull('type_id')->orWhere('type_id', $typeId))
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(fn (Note $note) => $note->chapter !== null);

        $chapters = $notes
            ->groupBy('chapter_id')
            ->map(fn (Collection $chapterNotes) => [
                'id' => (string) $chapterNotes->first()->chapter_id,
                'name' => $chapterNotes->first()->chapter->name,
                'notes' => $chapterNotes->map(fn (Note $note) => $this->transformNote($note))->values()->all(),
            ])
            ->sortBy('name')
            ->values()
            ->all();

        return [
            'id' => (string) ($variants->first()->id ?? ''),
            'name' => $subjectName,
            'chapters' => $chapters,
        ];
    }

    /**
     * The exact question shape the app parses.
     *
     * Image columns are wrapped with asset() here and nowhere else: the app
     * downloads these URLs directly, so a bare storage path is a broken image.
     * `subject`, `chapter` and `year_group` are emitted as whole models on
     * purpose — the app reads year and region off `subject`, so replacing them
     * with ids would break question parsing.
     *
     * @return array<string, mixed>
     */
    public function transformQuestion(Question $question): array
    {
        return [
            'id' => $question->id,
            'correct_choice_id' => $question->answer_id,
            'subject_id' => $question->subject_id,
            'year_group_id' => $question->year_group_id,
            'chapter_id' => $question->chapter_id,
            'question_text' => $question->question_text,
            'question_image_path' => $this->assetUrl($question->question_image_path),
            'formula' => $question->formula,
            'explanation' => $question->explanation,
            'explanation_image_path' => $this->assetUrl($question->explanation_image_path),
            'created_at' => $question->created_at,
            'updated_at' => $question->updated_at,
            'type_id' => $question->type_id,
            'duration' => $question->duration,
            'choices' => $question->choices->map(fn (Choice $choice) => [
                'id' => $choice->id,
                'question_id' => $choice->question_id,
                'choice_text' => $choice->choice_text,
                'choice_image_path' => $this->assetUrl($choice->choice_image_path),
                'formula' => $choice->formula,
                'created_at' => $choice->created_at,
                'updated_at' => $choice->updated_at,
            ]),
            'subject' => $question->subject,
            'chapter' => $question->chapter,
            'year_group' => $question->yearGroup,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformNote(Note $note): array
    {
        return [
            'id' => (string) $note->id,
            'title' => $note->title,
            'content' => $note->content,
            'subjectId' => (string) $note->subject_id,
            'subjectName' => $note->subject?->name,
            'grade' => $note->grade,
            'language' => $note->language,
            'chapterId' => (string) $note->chapter_id,
            'chapterName' => $note->chapter?->name,
            'createdAt' => $note->created_at?->toISOString(),
            'updatedAt' => $note->updated_at?->toISOString(),
        ];
    }

    private function assetUrl(?string $path): ?string
    {
        return $path ? asset('storage/'.$path) : null;
    }

    /**
     * @param  EloquentCollection<int, Subject>  $rows
     * @return array<string, mixed>
     */
    private function catalogueEntry(
        string $name,
        EloquentCollection $rows,
        Collection $questionStats,
        Collection $choiceStats,
        Collection $noteStats
    ): array {
        $ids = $rows->pluck('id');

        $questions = $ids->map(fn ($id) => $questionStats->get($id))->filter();
        $choices = $ids->map(fn ($id) => $choiceStats->get($id))->filter();
        $notes = $ids->map(fn ($id) => $noteStats->get($id))->filter();

        $questionCount = (int) $questions->sum('question_count');
        $noteCount = (int) $notes->sum('note_count');

        $textBytes = (int) $questions->sum('text_bytes')
            + (int) $choices->sum('text_bytes')
            + (int) $notes->sum('text_bytes');

        $imageCount = (int) $questions->sum('image_count') + (int) $choices->sum('image_count');

        return [
            // The download key. Not a `subjects.id` — see the class docblock.
            'key' => $name,
            'name' => $name,
            'subject_ids' => $ids->values()->all(),
            'years' => $rows->pluck('year')->filter()->unique()->sortDesc()->values()->all(),
            'regions' => $rows->pluck('region')->filter()->unique()->sort()->values()->all(),
            'duration' => $rows->first()->default_duration === null
                ? null
                : (int) $rows->first()->default_duration,
            'is_sample' => (bool) $rows->contains(fn (Subject $s) => (bool) $s->is_sample),
            'question_count' => $questionCount,
            'note_count' => $noteCount,
            'image_count' => $imageCount,
            // Uncompressed JSON estimate. The wire transfer is gzipped, so the
            // real download is roughly a quarter of this. Labelled "estimated"
            // for that reason; do not treat it as exact.
            'estimated_size_bytes' => $textBytes
                + ($questionCount * self::QUESTION_JSON_OVERHEAD_BYTES)
                + ($noteCount * self::NOTE_JSON_OVERHEAD_BYTES),
            // Changes whenever any question or note in this subject changes, so
            // the app can offer "update available" without re-downloading.
            'content_version' => $this->contentVersion($questions, $notes),
        ];
    }

    private function contentVersion(Collection $questions, Collection $notes): ?string
    {
        $timestamps = $questions->pluck('last_updated')
            ->merge($notes->pluck('last_updated'))
            ->filter()
            ->map(fn ($value) => (string) $value)
            ->sort()
            ->values();

        return $timestamps->last();
    }

    /**
     * LENGTH() rather than CHAR_LENGTH() on purpose: we want bytes, which is
     * what the app is estimating a download against.
     */
    private function questionStats(int $typeId, Collection $subjectIds): Collection
    {
        return Question::where('type_id', $typeId)
            ->whereIn('subject_id', $subjectIds)
            ->groupBy('subject_id')
            ->selectRaw(
                'subject_id,
                 COUNT(*) as question_count,
                 MAX(updated_at) as last_updated,
                 SUM(LENGTH(COALESCE(question_text, \'\')) + LENGTH(COALESCE(explanation, \'\'))) as text_bytes,
                 SUM(CASE WHEN question_image_path IS NOT NULL AND question_image_path <> \'\' THEN 1 ELSE 0 END)
                 + SUM(CASE WHEN explanation_image_path IS NOT NULL AND explanation_image_path <> \'\' THEN 1 ELSE 0 END)
                   as image_count'
            )
            ->get()
            ->keyBy('subject_id');
    }

    private function choiceStats(int $typeId, Collection $subjectIds): Collection
    {
        return Choice::join('questions', 'choices.question_id', '=', 'questions.id')
            ->where('questions.type_id', $typeId)
            ->whereIn('questions.subject_id', $subjectIds)
            ->groupBy('questions.subject_id')
            ->selectRaw(
                'questions.subject_id as subject_id,
                 SUM(LENGTH(COALESCE(choices.choice_text, \'\'))) as text_bytes,
                 SUM(CASE WHEN choices.choice_image_path IS NOT NULL AND choices.choice_image_path <> \'\'
                     THEN 1 ELSE 0 END) as image_count'
            )
            ->get()
            ->keyBy('subject_id');
    }

    private function noteStats(int $typeId, Collection $subjectIds): Collection
    {
        return Note::whereIn('subject_id', $subjectIds)
            ->where(fn ($q) => $q->whereNull('type_id')->orWhere('type_id', $typeId))
            ->groupBy('subject_id')
            ->selectRaw(
                'subject_id,
                 COUNT(*) as note_count,
                 MAX(updated_at) as last_updated,
                 SUM(LENGTH(COALESCE(content, \'\'))) as text_bytes'
            )
            ->get()
            ->keyBy('subject_id');
    }
}
