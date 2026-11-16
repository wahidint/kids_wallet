<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('spending_categories')->nullOnDelete();

            $table->string('item_name', 100);
            $table->decimal('amount', 12, 2);
            $table->string('description', 300)->nullable(); // why the child wants it

            // pending / approved / rejected / completed / cancelled
            $table->string('status', 20)->default('pending');

            // The parent's decision
            $table->string('parent_note', 200)->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();

            // The purchase itself: exactly ONE transaction per request
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('transaction_id')->nullable()->unique()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index(['wallet_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};