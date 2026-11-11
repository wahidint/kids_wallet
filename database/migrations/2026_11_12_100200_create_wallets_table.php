<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            // unique() = each child has exactly ONE wallet
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Money is ALWAYS decimal, never float: 12 digits total, 2 after the point
            $table->decimal('balance', 12, 2)->default(0);

            // Feature 9: Emergency Wallet Lock
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('lock_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};