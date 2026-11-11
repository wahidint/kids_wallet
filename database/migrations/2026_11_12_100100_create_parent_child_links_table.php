<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_child_links', function (Blueprint $table) {
            $table->id();

            // Both columns point at the users table
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('child_id')->constrained('users')->cascadeOnDelete();

            $table->timestamps();

            // The same parent-child pair can't be linked twice
            $table->unique(['parent_id', 'child_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_child_links');
    }
};