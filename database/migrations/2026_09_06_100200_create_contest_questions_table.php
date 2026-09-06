<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The frozen paper. Everyone sitting a contest gets exactly these questions
     * in this order, so scores are comparable.
     */
    public function up(): void
    {
        Schema::create('contest_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->unsignedSmallInteger('points')->default(1);
            $table->timestamps();

            $table->unique(['contest_id', 'question_id']);
            $table->index(['contest_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_questions');
    }
};
