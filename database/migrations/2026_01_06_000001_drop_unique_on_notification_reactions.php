<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_reactions', function (Blueprint $table) {
            // Drop the existing composite unique constraint
            $table->dropUnique(['app_notification_id', 'user_id']);
            // Add a non-unique index for efficient lookups
            $table->index(['app_notification_id', 'user_id'], 'notif_reactions_notification_user_index');
        });
    }

    public function down(): void
    {
        Schema::table('notification_reactions', function (Blueprint $table) {
            $table->dropIndex('notif_reactions_notification_user_index');
            $table->unique(['app_notification_id', 'user_id']);
        });
    }
};
