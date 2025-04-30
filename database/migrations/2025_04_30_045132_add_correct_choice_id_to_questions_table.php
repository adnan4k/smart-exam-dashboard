<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->unsignedBigInteger('correct_choice_id')->nullable()->after('id');
            $table->foreign('correct_choice_id')->references('id')->on('choices')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['correct_choice_id']);
            $table->dropColumn('correct_choice_id');
        });
    }
};