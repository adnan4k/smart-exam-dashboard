<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rank bands: 1st = 200 stars, 2nd = 150, 3rd = 100, and a long tail so a
     * student finishing 200th still walks away with something.
     *
     * A row with a null contest_id is a global default, used by any contest that
     * has no rules of its own.
     */
    public function up(): void
    {
        Schema::create('contest_reward_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('rank_from');
            // Null means open ended: everyone from rank_from downward.
            $table->unsignedInteger('rank_to')->nullable();
            $table->unsignedInteger('stars')->default(0);
            $table->unsignedInteger('coins')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['contest_id', 'rank_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_reward_rules');
    }
};
