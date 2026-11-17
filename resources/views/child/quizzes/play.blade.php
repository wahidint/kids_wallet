@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
    <a href="{{ route('child.quizzes.index') }}" class="text-secondary text-decoration-none small">
        <i class="bi bi-arrow-left"></i> All quizzes
    </a>

    <div class="row justify-content-center mt-3">
        <div class="col-lg-8 col-xl-7">
            <div class="kw-card p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="quiz-icon" style="--quiz-color: {{ $quiz->color }};"><i class="bi {{ $quiz->icon }}"></i></div>
                    <div class="flex-grow-1">
                        <h2 class="h5 fw-bold mb-0">{{ $quiz->title }}</h2>
                        <div class="small text-secondary" id="progressText"></div>
                    </div>
                    <div class="score-pill"><i class="bi bi-star-fill"></i> <span id="scoreText">0</span> pts</div>
                </div>

                <div class="progress quiz-progress mb-4" role="progressbar" aria-label="Quiz progress">
                    <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                </div>

                <h3 class="h5 fw-semibold mb-3" id="questionText"></h3>
                <div class="d-grid gap-2" id="options"></div>

                <div class="quiz-feedback d-none mt-3" id="feedback" aria-live="polite"></div>

                <div class="text-end mt-3">
                    <button type="button" class="btn btn-kw rounded-pill px-4 d-none" id="nextButton"></button>
                </div>
            </div>

            {{-- Submitted at the end; the server re-scores everything --}}
            <form method="POST" action="{{ route('child.quizzes.finish', $quiz) }}" id="finishForm">
                @csrf
                <div id="answerInputs"></div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const questions = @json($questions);
        const checkUrl = @json(route('child.quizzes.check', $quiz));
        const csrfToken = document.querySelector('#finishForm input[name="_token"]').value;
        const letters = ['A', 'B', 'C', 'D'];

        const progressText = document.getElementById('progressText');
        const progressBar = document.getElementById('progressBar');
        const questionText = document.getElementById('questionText');
        const optionsBox = document.getElementById('options');
        const feedback = document.getElementById('feedback');
        const nextButton = document.getElementById('nextButton');
        const scoreText = document.getElementById('scoreText');

        let index = 0;
        let score = 0;
        const answers = {};

        function render() {
            const question = questions[index];
            progressText.textContent = `Question ${index + 1} of ${questions.length}`;
            progressBar.style.width = `${(index / questions.length) * 100}%`;
            questionText.textContent = question.question;

            optionsBox.innerHTML = '';
            question.options.forEach((text, i) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'quiz-option';
                button.innerHTML = `<span class="quiz-letter">${letters[i]}</span><span></span>`;
                button.lastChild.textContent = text; // textContent = safe, never runs as HTML
                button.addEventListener('click', () => choose(i));
                optionsBox.appendChild(button);
            });

            feedback.classList.add('d-none');
            nextButton.classList.add('d-none');
        }

        async function choose(choice) {
            const question = questions[index];
            const buttons = optionsBox.querySelectorAll('.quiz-option');
            buttons.forEach((b) => (b.disabled = true));

            try {
                const response = await fetch(checkUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ question_id: question.id, answer: choice }),
                });
                if (!response.ok) throw new Error('Check failed');
                const result = await response.json();

                answers[question.id] = choice;
                buttons[result.correct_index].classList.add('is-correct');
                if (result.correct) {
                    score += 10;
                    scoreText.textContent = score;
                } else {
                    buttons[choice].classList.add('is-wrong');
                }

                feedback.className = 'quiz-feedback mt-3 ' + (result.correct ? 'is-correct' : 'is-wrong');
                feedback.innerHTML = '<div class="fw-semibold mb-1"></div><div class="small"></div>';
                feedback.firstChild.textContent = result.correct ? 'Correct! +10 points' : 'Not quite!';
                feedback.lastChild.textContent = result.explanation;

                nextButton.textContent = index === questions.length - 1 ? 'See my score' : 'Next question';
                nextButton.classList.remove('d-none');
                nextButton.focus();
            } catch (error) {
                buttons.forEach((b) => (b.disabled = false));
                alert('Could not check your answer. Please try again.');
            }
        }

        nextButton.addEventListener('click', () => {
            if (index < questions.length - 1) {
                index++;
                render();
                return;
            }

            // Last question: send all answers to the server for final scoring
            const box = document.getElementById('answerInputs');
            Object.entries(answers).forEach(([id, answer]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `answers[${id}]`;
                input.value = answer;
                box.appendChild(input);
            });
            nextButton.disabled = true;
            nextButton.textContent = 'Scoring...';
            document.getElementById('finishForm').submit();
        });

        render();
    </script>
@endpush