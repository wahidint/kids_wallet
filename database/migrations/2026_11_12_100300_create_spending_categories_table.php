<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spending_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();   // "Food"
            $table->string('slug', 50)->unique();   // "food" - safe to use in URLs and filters
            $table->string('icon', 50)->nullable(); // Bootstrap icon name, e.g. "bi-cup-hot"
            $table->string('color', 7)->nullable(); // Hex colour for charts, e.g. "#E2136E"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spending_categories');
    }
};