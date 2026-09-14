<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Questions authored for a contest live in the same table as study questions,
     * but stay invisible to the study API until their contest closes.
     *
     * `bank` records what a question was authored for and never changes.
     * `released_at` records when it became visible to students; null means hidden.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('bank', ['study', 'contest'])->default('study')->after('type_id')->index();
            $table->timestamp('released_at')->nullable()->after('bank')->index();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->nullable()->after('released_at');
        });

        // Every question that exists today is already study content.
        DB::table('questions')->update(['released_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['bank', 'released_at', 'difficulty']);
        });
    }
};
