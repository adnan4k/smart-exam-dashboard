<?php

use App\Models\Package;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Grandfather all legacy subscriptions where package_id is NULL into the All Access package.
     */
    public function up(): void
    {
        // 1. Ensure the All Access package exists
        $allAccessPackage = DB::table('packages')->where('slug', Package::SLUG_ALL_ACCESS)->first();

        if (! $allAccessPackage) {
            $packageId = DB::table('packages')->insertGetId([
                'name' => 'All Access',
                'slug' => Package::SLUG_ALL_ACCESS,
                'description' => 'Complete bundle unlocking everything: 1st Semester, 2nd Semester, and COC preparation materials.',
                'price' => 700.00,
                'duration_days' => 365,
                'is_active' => true,
                'order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $packageId = $allAccessPackage->id;
        }

        // 2. Grandfather all subscriptions with NULL package_id into the All Access package
        DB::table('subscriptions')
            ->whereNull('package_id')
            ->update([
                'package_id' => $packageId,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal intentionally left conservative to avoid unlinking valid subscriptions
    }
};
