<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entry is now open for the whole contest, so the join window is gone.
     */
    public function up(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('join_window_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->unsignedSmallInteger('join_window_minutes')->default(10)->after('duration_minutes');
        });
    }
};
