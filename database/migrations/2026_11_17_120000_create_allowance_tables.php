<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allowance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('frequency', 10);                        // weekly / monthly
            $table->unsignedTinyInteger('day_of_week')->nullable();  // 0 = Sunday ... 6 = Saturday
            $table->unsignedTinyInteger('day_of_month')->nullable(); // 1 - 28
            $table->string('note', 60)->nullable();
            $table->date('next_run_at');
            $table->timestamp('last_run_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'next_run_at']); // the scheduler's lookup
        });

        // One row per payment made: the record of every distribution
        Schema::create('allowance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allowance_schedule_id')->constrained()->cascadeOnDelete();
            $table->date('due_on');
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // The same due date can never be paid twice
            $table->unique(['allowance_schedule_id', 'due_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allowance_payments');
        Schema::dropIfExists('allowance_schedules');
    }
};