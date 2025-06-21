<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForeignKeysForQuestionsAndChoicesTables extends Migration
{
    public function up()
    {
        // Modify choices table foreign key to cascade on delete
        Schema::table('choices', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['question_id']);
            
            // Re-add foreign key with cascade delete
            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
        });

        // Modify questions table foreign keys to set null on delete
        Schema::table('questions', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['correct_choice_id']);
            $table->dropForeign(['answer_id']);

            // Re-add foreign keys with SET NULL on delete
            $table->foreign('correct_choice_id')
                ->references('id')->on('choices')
                ->nullOnDelete();

            $table->foreign('answer_id')
                ->references('id')->on('choices')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        // Revert changes

        Schema::table('choices', function (Blueprint $table) {
            $table->dropForeign(['question_id']);

            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('restrict'); // or whatever the original behavior was
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['correct_choice_id']);
            $table->dropForeign(['answer_id']);

            $table->foreign('correct_choice_id')
                ->references('id')->on('choices')
                ->onDelete('restrict'); // or original behavior

            $table->foreign('answer_id')
                ->references('id')->on('choices')
                ->onDelete('restrict'); // or original behavior
        });
    }
}
