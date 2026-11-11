<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();

            // "credit" = money in, "debit" = money out
            $table->string('type', 10);

            // WHY the money moved: "pocket_money", "allowance", "purchase",
            // "savings_contribution", etc.
            $table->string('source', 30);

            $table->decimal('amount', 12, 2);          // always positive
            $table->decimal('balance_after', 12, 2);   // wallet balance right after this transaction

            // Only expenses have a category
            $table->foreignId('category_id')->nullable()
                  ->constrained('spending_categories')->nullOnDelete();

            $table->string('description')->nullable();

            // Which user caused it, e.g. the parent who added money
            $table->foreignId('performed_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            // Points at the record that caused this transaction
            // (a purchase request, an allowance schedule, a savings goal...)
            $table->nullableMorphs('reference');

            $table->timestamps();

            // Speeds up "this wallet's history, newest first"
            $table->index(['wallet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};