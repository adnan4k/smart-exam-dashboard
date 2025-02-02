<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizSchema extends Migration
{
    public function up(): void
    {
        // Create users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'student'])->default('student');
            $table->rememberToken();
            $table->timestamps();
        });

        // Create subjects table
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Create year_groups table
        Schema::create('year_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->timestamps();
        });

        // Create questions table
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('year_group_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->string('question_image_path')->nullable(); // Path to question image
            $table->text('formula')->nullable(); // LaTeX formula for the question
            $table->string('answer_text');
            $table->text('explanation');
            $table->string('explanation_image_path')->nullable(); // Path to explanation image
            $table->timestamps();
        });

        // Create choices table
        Schema::create('choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('choice_text');
            $table->string('choice_image_path')->nullable(); // Path to choice image
            $table->text('formula')->nullable(); // LaTeX formula for the choice (if needed)
            $table->timestamps();
        });

        // Create subscriptions table
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('year_group_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('choices');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('year_groups');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('users');
    }
} 