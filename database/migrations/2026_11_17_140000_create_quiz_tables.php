<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 80);
            $table->string('slug', 80)->unique();      // used in the URL: /child/quizzes/saving-smart
            $table->string('description', 200);
            $table->string('icon', 40);
            $table->string('color', 7);
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->string('question', 255);
            $table->json('options');                     // ["A", "B", "C", "D"]
            $table->unsignedTinyInteger('correct_index'); // 0-3
            $table->string('explanation', 255);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');         // correct answers
            $table->unsignedTinyInteger('total');         // questions in the quiz
            $table->unsignedInteger('points')->default(0); // NEW points earned by this attempt
            $table->json('answers');                      // {"question_id": chosen_index}
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->index(['user_id', 'quiz_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
    }
};