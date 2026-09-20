<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add max_subjects to packages table (default 7)
        Schema::table('packages', function (Blueprint $table) {
            $table->integer('max_subjects')->default(7)->after('duration_days');
        });

        // 2. Create package_subject pivot table for default subjects
        Schema::create('package_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->boolean('is_default')->default(true);
            $table->timestamps();

            $table->unique(['package_id', 'subject_id']);
        });

        // 3. Create subscription_subject pivot table for user selected subjects
        Schema::create('subscription_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['subscription_id', 'subject_id']);
        });

        // 4. Migrate existing subjects.package_id into package_subject as default subjects
        if (Schema::hasColumn('subjects', 'package_id')) {
            $existingSubjects = DB::table('subjects')
                ->whereNotNull('package_id')
                ->select('id as subject_id', 'package_id')
                ->get();

            $now = now();
            foreach ($existingSubjects as $sub) {
                DB::table('package_subject')->updateOrInsert(
                    ['package_id' => $sub->package_id, 'subject_id' => $sub->subject_id],
                    ['is_default' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_subject');
        Schema::dropIfExists('package_subject');

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('max_subjects');
        });
    }
};
