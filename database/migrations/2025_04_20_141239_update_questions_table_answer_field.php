<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            // Remove the old answer_text column if it exists
            if (Schema::hasColumn('questions', 'answer_text')) {
                $table->dropColumn('answer_text');
            }
            
            // Add the new answer_id column
            if (!Schema::hasColumn('questions', 'answer_id')) {
                $table->foreignId('answer_id')->nullable()->constrained('choices')->after('formula');
            }
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['answer_id']);
            $table->dropColumn('answer_id');
            $table->text('answer_text')->nullable()->after('formula');
        });
    }
};