<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

        public function up()
        {
            Schema::create('tours', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('duration')->nullable();
                $table->string('tour_code')->nullable();
                $table->text('inclusions')->nullable(); // Store inclusions as text
                $table->text('exclusions')->nullable(); // Store exclusions as text
                $table->string('image')->nullable();
                $table->timestamps();
            });
        }
        
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
