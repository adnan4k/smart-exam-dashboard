<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stars a student earns for every question they answer correctly.
     *
     * Contests that already exist default to 0, so their payouts and standings
     * stay exactly as they were. New contests get a value from the admin form.
     */
    public function up(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->unsignedSmallInteger('stars_per_question')->default(0)->after('duration_minutes');
        });

        Schema::table('contest_attempts', function (Blueprint $table) {
            // Earned on the paper itself (correct answers x stars_per_question),
            // known the moment an attempt is graded. stars_awarded stays the
            // total actually paid at finalization: these plus any rank bonus.
            $table->unsignedInteger('question_stars')->default(0)->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('contest_attempts', function (Blueprint $table) {
            $table->dropColumn('question_stars');
        });

        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('stars_per_question');
        });
    }
};
