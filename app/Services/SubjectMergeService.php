<?php

namespace App\Services;

use App\Models\Contest;
use App\Models\Note;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Video;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubjectMergeService
{
    /**
     * Preview the impact of merging source subjects into a target subject.
     */
    public function preview(int $targetSubjectId, array $sourceSubjectIds): array
    {
        $targetSubject = Subject::findOrFail($targetSubjectId);
        $sourceIds = array_values(array_filter(array_unique($sourceSubjectIds), fn ($id) => (int) $id !== $targetSubjectId));

        if (empty($sourceIds)) {
            return [
                'target' => $targetSubject,
                'sources' => collect(),
                'questions_count' => 0,
                'notes_count' => 0,
                'videos_count' => 0,
                'contests_count' => 0,
            ];
        }

        $sources = Subject::withCount(['questions', 'notes', 'videos'])
            ->whereIn('id', $sourceIds)
            ->get();

        $questionsCount = Question::withoutGlobalScopes()->whereIn('subject_id', $sourceIds)->count();
        $notesCount = Note::whereIn('subject_id', $sourceIds)->count();
        $videosCount = Video::whereIn('subject_id', $sourceIds)->count();
        $contestsCount = Contest::whereIn('subject_id', $sourceIds)->count();

        return [
            'target' => $targetSubject,
            'sources' => $sources,
            'questions_count' => $questionsCount,
            'notes_count' => $notesCount,
            'videos_count' => $videosCount,
            'contests_count' => $contestsCount,
        ];
    }

    /**
     * Merge one or more source subjects into a single target subject.
     * All questions, notes, videos, contest references, and package/subscription
     * associations are safely moved to the target subject, and the source subjects are deleted.
     */
    public function merge(int $targetSubjectId, array $sourceSubjectIds): array
    {
        $target = Subject::findOrFail($targetSubjectId);

        // Sanitize source IDs (exclude target itself)
        $sourceIds = array_values(array_filter(array_unique($sourceSubjectIds), fn ($id) => (int) $id !== $targetSubjectId && (int) $id > 0));

        if (empty($sourceIds)) {
            throw new InvalidArgumentException('Please select at least one different source subject to merge.');
        }

        $sources = Subject::whereIn('id', $sourceIds)->get();
        if ($sources->isEmpty()) {
            throw new InvalidArgumentException('No valid source subjects found.');
        }

        return DB::transaction(function () use ($target, $sources, $sourceIds) {
            $questionsMoved = Question::withoutGlobalScopes()->whereIn('subject_id', $sourceIds)->count();
            $notesMoved = Note::whereIn('subject_id', $sourceIds)->count();
            $videosMoved = Video::whereIn('subject_id', $sourceIds)->count();
            $contestsMoved = Contest::whereIn('subject_id', $sourceIds)->count();

            // 1. Move questions
            Question::withoutGlobalScopes()->whereIn('subject_id', $sourceIds)->update(['subject_id' => $target->id]);

            // 2. Move notes
            Note::whereIn('subject_id', $sourceIds)->update(['subject_id' => $target->id]);

            // 3. Move videos
            Video::whereIn('subject_id', $sourceIds)->update(['subject_id' => $target->id]);

            // 4. Move contest references
            Contest::whereIn('subject_id', $sourceIds)->update(['subject_id' => $target->id]);

            // 5. Transfer package_subject associations
            $packageRows = DB::table('package_subject')
                ->whereIn('subject_id', $sourceIds)
                ->get();

            foreach ($packageRows as $row) {
                DB::table('package_subject')->updateOrInsert(
                    ['package_id' => $row->package_id, 'subject_id' => $target->id],
                    ['is_default' => (bool) $row->is_default, 'updated_at' => now()]
                );
            }
            DB::table('package_subject')->whereIn('subject_id', $sourceIds)->delete();

            // 6. Transfer subscription_subject associations
            $subscriptionRows = DB::table('subscription_subject')
                ->whereIn('subject_id', $sourceIds)
                ->get();

            foreach ($subscriptionRows as $row) {
                DB::table('subscription_subject')->updateOrInsert(
                    ['subscription_id' => $row->subscription_id, 'subject_id' => $target->id],
                    ['updated_at' => now()]
                );
            }
            DB::table('subscription_subject')->whereIn('subject_id', $sourceIds)->delete();

            // 7. Delete source subjects
            $sourceNames = $sources->pluck('name')->all();
            Subject::whereIn('id', $sourceIds)->delete();

            return [
                'target_id' => $target->id,
                'target_name' => $target->name,
                'merged_count' => count($sourceIds),
                'merged_names' => $sourceNames,
                'questions_moved' => $questionsMoved,
                'notes_moved' => $notesMoved,
                'videos_moved' => $videosMoved,
                'contests_moved' => $contestsMoved,
            ];
        });
    }
}
