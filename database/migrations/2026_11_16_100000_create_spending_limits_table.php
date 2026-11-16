<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spending_limits', function (Blueprint $table) {
            $table->id();

            // One set of limits per wallet
            $table->foreignId('wallet_id')->unique()->constrained()->cascadeOnDelete();

            // NULL = no limit for that period
            $table->decimal('daily_limit', 12, 2)->nullable();
            $table->decimal('weekly_limit', 12, 2)->nullable();
            $table->decimal('monthly_limit', 12, 2)->nullable();

            // Which parent last changed the limits
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spending_limits');
    }
};