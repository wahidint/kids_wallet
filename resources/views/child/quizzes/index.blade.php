@extends('layouts.app')

@section('title', 'Learn & play')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">Learn &amp; play</h2>
            <p class="text-secondary mb-0">Play money quizzes, earn points and level up!</p>
        </div>
    </div>

    {{-- Points and level --}}
    <div class="kw-card level-card mb-4">
        <div class="d-flex flex-wrap align-items-center gap-4">
            <div class="level-star"><i class="bi bi-star-fill"></i></div>
            <div class="flex-grow-1">
                <div class="small opacity-75">Your level</div>
                <div class="h4 fw-bold mb-1">{{ $level }}</div>
                <div class="small opacity-75">
                    {{ $points }} of {{ $maxPoints }} points
                    @if ($nextLevel)
                        · {{ $nextLevel['at'] - $points }} more to become <strong>{{ $nextLevel['name'] }}</strong>
                    @else
                        · Top level reached!
                    @endif
                </div>
            </div>
            <div class="text-center">
                <div class="small opacity-75">Questions mastered</div>
                <div class="h4 fw-bold mb-0">{{ $mastered }} / {{ $totalQuestions }}</div>
            </div>
        </div>
        <div class="progress level-progress mt-3" role="progressbar" aria-label="Learning progress"
             aria-valuenow="{{ $maxPoints ? round($points / $maxPoints * 100) : 0 }}" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar" style="width: {{ $maxPoints ? round($points / $maxPoints * 100) : 0 }}%"></div>
        </div>
    </div>

    <div class="row g-3">
        @foreach ($quizzes as $quiz)
            @php $stat = $stats->get($quiz->id); @endphp
            <div class="col-md-6">
                <div class="kw-card h-100 d-flex flex-column">
                    <div class="d-flex align-items-start gap-3">
                        <div class="quiz-icon" style="--quiz-color: {{ $quiz->color }};"><i class="bi {{ $quiz->icon }}"></i></div>
                        <div class="flex-grow-1">
                            <h3 class="h5 fw-bold mb-1">{{ $quiz->title }}</h3>
                            <p class="small text-secondary mb-0">{{ $quiz->description }}</p>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-3 small mt-3">
                        <span class="text-secondary"><i class="bi bi-question-circle"></i> {{ $quiz->questions_count }} questions</span>
                        @if ($stat)
                            <span class="quiz-stars" title="Best score">
                                @for ($i = 1; $i <= $quiz->questions_count; $i++)
                                    <i class="bi {{ $i <= $stat->best ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                            </span>
                            <span class="text-secondary">Best {{ $stat->best }}/{{ $quiz->questions_count }} · played {{ $stat->plays }}×</span>
                        @else
                            <span class="badge rounded-pill text-bg-light">New</span>
                        @endif
                    </div>

                    <div class="mt-auto pt-3">
                        <a href="{{ route('child.quizzes.play', $quiz) }}" class="btn btn-kw rounded-pill px-4">
                            <i class="bi bi-play-fill"></i> {{ $stat ? 'Play again' : 'Play' }}
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection