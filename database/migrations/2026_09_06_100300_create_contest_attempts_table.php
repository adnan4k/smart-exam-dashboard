<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('device_id')->nullable();
            $table->string('ip_address', 45)->nullable();

            // DATETIME, not TIMESTAMP, and deliberately so: MySQL only gives the
            // first TIMESTAMP column in a table an implicit CURRENT_TIMESTAMP
            // default. Every later TIMESTAMP NOT NULL column gets an implicit
            // '0000-00-00 00:00:00', which NO_ZERO_DATE - part of the strict mode
            // Laravel sets - rejects outright, so `expires_at` failed to create on
            // any server running with explicit_defaults_for_timestamp = 0.
            // The contests table already dates its columns this way.
            $table->dateTime('started_at');
            // Server-computed deadline. The client counts down to this, never to
            // a duration it calculated itself.
            $table->dateTime('expires_at');
            $table->dateTime('submitted_at')->nullable();

            $table->enum('status', ['in_progress', 'submitted', 'expired', 'voided'])
                ->default('in_progress');

            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('correct_count')->default(0);
            $table->unsignedSmallInteger('wrong_count')->default(0);
            $table->unsignedSmallInteger('unanswered_count')->default(0);
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->unsignedInteger('time_taken_seconds')->nullable();

            $table->unsignedInteger('rank')->nullable();
            $table->unsignedInteger('stars_awarded')->default(0);
            $table->unsignedInteger('coins_awarded')->default(0);

            $table->timestamps();

            // One attempt per student per contest, enforced by the database.
            $table->unique(['contest_id', 'user_id']);
            // Device is checked in code so an admin can override a shared phone.
            $table->index(['contest_id', 'device_id']);
            // Ranking reads this: highest score first, then fastest.
            $table->index(['contest_id', 'status', 'score', 'time_taken_seconds'], 'contest_ranking_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_attempts');
    }
};
