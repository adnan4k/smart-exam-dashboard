<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Hides questions that have not been released to students yet.
 *
 * Contest questions are authored in the same table as study questions, so
 * without this they would appear in the study API before their contest runs and
 * students could memorise the paper in advance.
 *
 * This scope is applied globally so no existing query, controller or admin
 * screen has to change. Every question that existed before contests was
 * backfilled with released_at = created_at, so this filter is a no-op for all
 * current data - it only ever hides a contest question that has not run yet.
 *
 * Contest code that legitimately needs the hidden pool (the paper builder, and
 * serving a live contest) opts out:
 *
 *     Question::withoutGlobalScope(ReleasedQuestionScope::class)
 */
class ReleasedQuestionScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $table = $model->getTable();

        $builder->where(function (Builder $query) use ($table) {
            $query->whereNotNull($table . '.released_at')
                ->where($table . '.released_at', '<=', now());
        });
    }
}
