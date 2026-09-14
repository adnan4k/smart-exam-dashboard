<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cached balances, kept in step with reward_transactions so leaderboards
     * don't have to sum the ledger on every request.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('total_stars')->default(0)->after('fcm_token');
            $table->unsignedInteger('total_coins')->default(0)->after('total_stars');
            $table->index('total_stars');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['total_stars']);
            $table->dropColumn(['total_stars', 'total_coins']);
        });
    }
};
