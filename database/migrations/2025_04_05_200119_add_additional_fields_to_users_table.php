<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->enum('institution_type', [
                'elementary',
                'high_school',
                'preparatory',
                'university',
                'college',
                'other'
            ])->nullable();
                       
            $table->string('institution_name')->nullable();
            $table->foreignId('type_id')->nullable()->constrained('types')->onDelete('set null');        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
