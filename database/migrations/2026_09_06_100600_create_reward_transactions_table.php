<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The ledger. Stars and coins are only ever created or spent here, and the
     * cached totals on users are derived from it.
     *
     * idempotency_key makes re-running a finalize job safe.
     */
    public function up(): void
    {
        Schema::create('reward_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('stars')->default(0);
            $table->integer('coins')->default(0);
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('reason');
            $table->json('meta')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_transactions');
    }
};
