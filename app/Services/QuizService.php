<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

/**
 * Feature 6: Financial Learning Games.
 */
class QuizService
{
    public const POINTS_PER_CORRECT = 10;

    /** Points needed for each level. */
    public const LEVELS = [
        0 => 'Money Beginner',
        60 => 'Smart Saver',
        150 => 'Money Master',
    ];

    /** Check ONE answer (for instant feedback). The answer is checked on the server. */
    public function check(Quiz $quiz, int $questionId, int $answer): array
    {
        $question = $quiz->questions()->findOrFail($questionId); // must belong to THIS quiz

        return [
            'correct' => $answer === $question->correct_index,
            'correct_index' => $question->correct_index,
            'explanation' => $question->explanation,
        ];
    }

    /**
     * Score the whole quiz again on the server and save the attempt.
     * Points are only given for BEATING your previous best (no farming by replaying).
     */
    public function finish(User $child, Quiz $quiz, array $answers): QuizAttempt
    {
        $questions = $quiz->questions()->get();
        $score = 0;
        $clean = [];

        foreach ($questions as $question) {
            $given = $answers[$question->id] ?? null;
            $clean[$question->id] = $given === null ? null : (int) $given;

            if ($given !== null && (int) $given === $question->correct_index) {
                $score++;
            }
        }

        $previousBest = (int) QuizAttempt::where('user_id', $child->id)
            ->where('quiz_id', $quiz->id)
            ->max('score');

        return QuizAttempt::create([
            'user_id' => $child->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total' => $questions->count(),
            'points' => max(0, $score - $previousBest) * self::POINTS_PER_CORRECT,
            'answers' => $clean,
            'completed_at' => now(),
        ]);
    }

    /** Everything the quiz list page needs. */
    public function progress(User $child): array
    {
        $quizzes = Quiz::withCount('questions')->orderBy('id')->get();

        $stats = QuizAttempt::where('user_id', $child->id)
            ->selectRaw('quiz_id, MAX(score) AS best, COUNT(*) AS plays')
            ->groupBy('quiz_id')
            ->get()
            ->keyBy('quiz_id');

        $points = (int) QuizAttempt::where('user_id', $child->id)->sum('points');

        $level = 'Money Beginner';
        $nextLevel = null;
        foreach (self::LEVELS as $needed => $name) {
            if ($points >= $needed) {
                $level = $name;
            } elseif ($nextLevel === null) {
                $nextLevel = ['name' => $name, 'at' => $needed];
            }
        }

        return [
            'quizzes' => $quizzes,
            'stats' => $stats,
            'points' => $points,
            'maxPoints' => $quizzes->sum('questions_count') * self::POINTS_PER_CORRECT,
            'mastered' => (int) $stats->sum('best'),
            'totalQuestions' => (int) $quizzes->sum('questions_count'),
            'level' => $level,
            'nextLevel' => $nextLevel,
        ];
    }
}