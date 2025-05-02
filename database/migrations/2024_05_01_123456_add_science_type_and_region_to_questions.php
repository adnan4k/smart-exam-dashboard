<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The class name MUST match the file name (without the date prefix)
class AddScienceTypeAndRegionToQuestions extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('science_type', ['social', 'natural'])->after('type_id');
            $table->string('region')->nullable()->after('science_type');
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['science_type', 'region']);
        });
    }
}