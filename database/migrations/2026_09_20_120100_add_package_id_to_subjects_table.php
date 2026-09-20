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
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->after('type_id')->constrained('packages')->nullOnDelete();
            $table->string('package_type')->nullable()->after('package_id')->index(); // 'semester_1', 'semester_2', 'coc', 'all'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn(['package_id', 'package_type']);
        });
    }
};
