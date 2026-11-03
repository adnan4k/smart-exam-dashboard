<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prizes an admin hands to top-ranked students: a free subscription, or
     * money sent outside the app (Telebirr, bank transfer, ...).
     *
     * Kept separate from reward_transactions, which is the automatic star
     * ledger. These are manual, and cash ones need tracking until they are
     * actually paid.
     */
    public function up(): void
    {
        Schema::create('contest_prizes', function (Blueprint $table) {
            $table->id();

            // Nullable so deleting a user never erases a record of money paid;
            // the recipient_* snapshot keeps who it was.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();

            // Null contest = awarded for the overall ranking.
            $table->foreignId('contest_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('rank')->nullable();

            $table->enum('kind', ['subscription', 'cash']);

            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('subscription_days')->nullable();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('amount', 10, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();

            $table->enum('status', ['pending', 'delivered', 'cancelled'])->default('pending')->index();
            // dateTime, not timestamp: see the production explicit_defaults_for_timestamp note.
            $table->dateTime('delivered_at')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'contest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_prizes');
    }
};
