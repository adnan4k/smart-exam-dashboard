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
        Schema::create('notification_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_notification_id')->constrained('app_notifications')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('reaction_type', ['like', 'dislike']);
            $table->timestamps();

            // Ensure a user can only have one reaction per notification
            $table->unique(['app_notification_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_reactions');
    }
};
