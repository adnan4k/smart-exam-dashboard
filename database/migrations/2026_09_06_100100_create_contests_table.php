<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            // A contest is scoped to one exam type, so students only compete
            // against their own cohort.
            $table->foreignId('type_id')->nullable()->constrained('types')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();

            $table->unsignedSmallInteger('duration_minutes')->default(40);
            // How long after starts_at a student may still enter.
            $table->unsignedSmallInteger('join_window_minutes')->default(10);

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->enum('status', ['draft', 'scheduled', 'closed', 'finalized'])->default('draft');
            $table->unsignedSmallInteger('question_count')->default(0);
            $table->timestamp('finalized_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'starts_at']);
            $table->index(['type_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contests');
    }
};
