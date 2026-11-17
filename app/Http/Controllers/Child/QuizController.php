<?php

namespace App\Http\Controllers\Child;

use App\Http\Controllers\Controller;
use App\Http\Requests\Child\CheckAnswerRequest;
use App\Http\Requests\Child\FinishQuizRequest;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(Request $request, QuizService $quizzes): View
    {
        return view('child.quizzes.index', $quizzes->progress($request->user()));
    }

    public function play(Quiz $quiz): View
    {
        // Only id, question and options go to the browser: NOT the answers
        $questions = $quiz->questions()->get()->map(fn ($question) => [
            'id' => $question->id,
            'question' => $question->question,
            'options' => $question->options,
        ])->values();

        return view('child.quizzes.play', compact('quiz', 'questions'));
    }

    /** Called by JavaScript after each answer. Returns JSON. */
    public function check(CheckAnswerRequest $request, Quiz $quiz, QuizService $quizzes): JsonResponse
    {
        return response()->json(
            $quizzes->check($quiz, $request->integer('question_id'), $request->integer('answer'))
        );
    }

    public function finish(FinishQuizRequest $request, Quiz $quiz, QuizService $quizzes): RedirectResponse
    {
        $attempt = $quizzes->finish($request->user(), $quiz, $request->validated('answers'));

        return redirect()->route('child.quizzes.result', $attempt);
    }

    public function result(Request $request, QuizAttempt $attempt): View
    {
        abort_unless($attempt->user_id === $request->user()->id, 404); // only your own results

        $attempt->load('quiz.questions');

        return view('child.quizzes.result', compact('attempt'));
    }
}