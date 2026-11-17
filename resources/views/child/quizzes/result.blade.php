@extends('layouts.app')

@section('title', 'Quiz result')

@section('content')
    @php
        $quiz = $attempt->quiz;
        $percent = $attempt->total > 0 ? (int) round($attempt->score / $attempt->total * 100) : 0;
        $message = match (true) {
            $percent === 100 => ['Perfect score! You are a money genius!', 'bi-emoji-sunglasses'],
            $percent >= 60 => ['Great job! You know your stuff.', 'bi-emoji-smile'],
            default => ['Good try! Read the answers below and play again.', 'bi-emoji-neutral'],
        };
    @endphp

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="kw-card p-4 text-center mb-4">
                <div class="quiz-icon mx-auto mb-3" style="--quiz-color: {{ $quiz->color }};"><i class="bi {{ $quiz->icon }}"></i></div>
                <div class="small text-secondary">{{ $quiz->title }}</div>
                <div class="result-score">{{ $attempt->score }} / {{ $attempt->total }}</div>
                <p class="fs-5 fw-semibold mb-2"><i class="bi {{ $message[1] }} text-kw"></i> {{ $message[0] }}</p>

                @if ($attempt->points > 0)
                    <span class="score-pill fs-6"><i class="bi bi-star-fill"></i> +{{ $attempt->points }} points earned</span>
                @else
                    <p class="small text-secondary mb-0">No new points this time. Beat your best score to earn more!</p>
                @endif

                <div class="d-flex justify-content-center gap-2 mt-4">
                    <a href="{{ route('child.quizzes.play', $quiz) }}" class="btn btn-kw rounded-pill px-4">
                        <i class="bi bi-arrow-repeat"></i> Play again
                    </a>
                    <a href="{{ route('child.quizzes.index') }}" class="btn btn-light border rounded-pill px-4">All quizzes</a>
                </div>
            </div>

            <div class="kw-card">
                <h3 class="h6 fw-semibold mb-3">Review your answers</h3>
                @foreach ($quiz->questions as $number => $question)
                    @php
                        $given = $attempt->answers[$question->id] ?? null;
                        $right = $given === $question->correct_index;
                    @endphp
                    <div class="review-item">
                        <div class="d-flex gap-2">
                            <i class="bi {{ $right ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} mt-1"></i>
                            <div>
                                <div class="fw-medium">{{ $number + 1 }}. {{ $question->question }}</div>
                                @if (! $right)
                                    <div class="small text-danger">
                                        Your answer: {{ $given !== null ? $question->options[$given] : 'No answer' }}
                                    </div>
                                @endif
                                <div class="small text-success">Correct answer: {{ $question->options[$question->correct_index] }}</div>
                                <div class="small text-secondary">{{ $question->explanation }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if ($percent === 100)
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
            <script>confetti({ particleCount: 180, spread: 100, origin: { y: 0.5 } });</script>
        @endpush
    @endif
@endsection