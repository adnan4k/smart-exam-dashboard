<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The question form has always written science_type and region, but the 2024
 * migration that was supposed to add the columns had its body commented out,
 * so every save blew up with "Unknown column 'science_type'". That migration is
 * already marked as run and cannot be replayed, so the columns are added here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (! Schema::hasColumn('questions', 'science_type')) {
                $table->enum('science_type', ['social', 'natural'])->default('natural')->after('type_id');
            }

            if (! Schema::hasColumn('questions', 'region')) {
                $table->string('region')->nullable()->after('science_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['science_type', 'region']);
        });
    }
};
