<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Take Quiz: {{ $quiz->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Quiz Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold mb-4">{{ $quiz->name }}</h3>
                        <p class="text-gray-700 mb-4">{{ $quiz->description }}</p>
                    </div>
                    @if($quiz->duration > 0)
                  

                    <div class="flex items-center justify-between mt-4 mb-4">
                        <div>
                            <span class="text-sm text-gray-600">Time Remaining</span>
                            <div id="quiz-timer" data-seconds="{{ $remainingSeconds ?? 0 }}" class="text-xl font-semibold text-red-600">
                                @if($remainingSeconds >= 3600)
                                    {{ gmdate('H:i:s', $remainingSeconds) }}
                                @else
                                    {{ gmdate('i:s', $remainingSeconds) }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var timerEl = document.getElementById('quiz-timer');
                        if (!timerEl) return;

                        var seconds = parseInt(timerEl.getAttribute('data-seconds'), 10) || 0;
                        var form = document.getElementById('quiz-form');

                        function pad(n) { return String(n).padStart(2, '0'); }
                        function format(s) {
                            var h = Math.floor(s / 3600);
                            var m = Math.floor((s % 3600) / 60);
                            var sec = s % 60;
                            if (h > 0) return pad(h) + ':' + pad(m) + ':' + pad(sec);
                            return pad(m) + ':' + pad(sec);
                        }

                        function submitOnce() {
                            if (!form) return;
                            if (!form.dataset.submitted) {
                                form.dataset.submitted = '1';
                                form.submit();
                            }
                        }

                        // initialize display
                        timerEl.textContent = format(seconds);

                        // if time already up, submit immediately
                        if (seconds <= 0) {
                            submitOnce();
                            return;
                        }

                        var interval = setInterval(function () {
                            seconds--;
                            timerEl.textContent = format(Math.max(0, seconds));
                            if (seconds <= 0) {
                                clearInterval(interval);
                                submitOnce();
                            }
                        }, 1000);
                    });
                    </script>
                    @endif
                </div>
                {{-- computedTotalMarks and computedPassMarks are prepared in controller (AttemptController::start) --}}

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <span class="text-sm text-gray-600">Total Marks</span>
                        <p class="text-lg font-semibold">{{ $computedTotalMarks ?? $quiz->questions->sum('marks') }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Pass Marks</span>
                        <p class="text-lg font-semibold">{{ $computedPassMarks ?? (int) round($quiz->questions->sum('marks') / 3) }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Total Questions</span>
                        <p class="text-lg font-semibold">{{ $totalQuestions ?? $quiz->questions->count() }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Duration</span>
                        <p class="text-lg font-semibold">{{ $quiz->duration > 0 ? $quiz->duration . ' min' : 'No limit' }}</p>
                    </div>
                </div>

                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>Instructions:</strong> Answer all questions to the best of your ability. Click "Submit Quiz" when you're done.
                    </p>
                </div>
            </div>

            <!-- Quiz Form -->
            <form method="POST" action="{{ route('quizzes.submit', $quiz->id) }}" id="quiz-form">
                @csrf
                @if(isset($attempt) && $attempt)
                <input type="hidden" name="attempt_id" value="{{ $attempt->id }}">
                @endif

                <!-- Questions List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold mb-6">Questions</h3>

                    <div class="space-y-6">
                        @foreach($quiz->questions as $index => $quizQuestion)
                        <div class="p-6 border rounded-lg bg-gray-50">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <div class="flex items-start gap-3">
                                        <span class="font-bold text-gray-800 text-lg">Q{{ $index + 1 }}.</span>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900 mb-3 text-lg">{{ $quizQuestion->question->name }}</p>

                                            @if($quizQuestion->question->media_url)
                                            <div class="my-4 mb-4">
                                                @if($quizQuestion->question->media_type === 'image')
                                                <img src="{{ asset($quizQuestion->question->media_url) }}" alt="Question Media" class="max-w-md rounded-lg shadow-md border border-gray-200">
                                                @elseif($quizQuestion->question->media_type === 'video')
                                                <video controls class="max-w-md rounded-lg shadow-md border border-gray-200">
                                                    <source src="{{ asset($quizQuestion->question->media_url) }}" type="video/mp4">
                                                    <track kind="captions" srclang="en" label="English captions" src="{{ asset('media/captions/placeholder.vtt') }}">
                                                    Your browser does not support the video tag.
                                                </video>
                                                @elseif($quizQuestion->question->media_type === 'audio')
                                                <audio controls class="w-full max-w-md">
                                                    <source src="{{ asset($quizQuestion->question->media_url) }}" type="audio/mpeg">
                                                    Your browser does not support the audio tag.
                                                </audio>
                                                @endif
                                            </div>
                                            @endif

                                            @if($quizQuestion->question->question_type->name === 'fill_the_blank')
                                            <!-- Fill in the Blank Answer -->
                                            <div class="mt-4 w-full">
                                                <label for="answer_{{ $quizQuestion->question->id }}" class="block text-sm font-medium text-gray-700 mb-2">Your Answer:</label>
                                                <textarea
                                                    id="answer_{{ $quizQuestion->question->id }}"
                                                    name="answers[{{ $quizQuestion->question->id }}]"
                                                    rows="4"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="Type your answer here"
                                                    {{ $quizQuestion->is_optional ? '' : 'required' }}></textarea>
                                            </div>
                                            @else
                                            <!-- MCQ Options -->
                                            <div class="mt-4 space-y-3">
                                                @php
                                                $isSingleAnswer = $quizQuestion->question->question_type->name === 'multiple_choice_single_answer';
                                                $inputType = $isSingleAnswer ? 'radio' : 'checkbox';
                                                $inputName = $isSingleAnswer
                                                ? "answers[{$quizQuestion->question->id}]"
                                                : "answers[{$quizQuestion->question->id}][]";
                                                @endphp

                                                @foreach($quizQuestion->question->options as $option)
                                                <label class="flex items-center gap-3 p-3 border rounded-lg hover:bg-white transition cursor-pointer">
                                                    <input
                                                        type="{{ $inputType }}"
                                                        name="{{ $inputName }}"
                                                        value="{{ $option->id }}"
                                                        class="w-5 h-5 text-indigo-600 focus:ring-indigo-500"
                                                        {{ !$isSingleAnswer && !$quizQuestion->is_optional ? '' : '' }}>
                                                    <span class="w-8 h-8 flex items-center justify-center border rounded border-gray-300 text-base font-medium">
                                                        {{ chr(65 + $loop->index) }}
                                                    </span>
                                                    <span class="text-base text-gray-800">{{ $option->name }}</span>
                                                </label>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Question Info Badge -->
                                <div class="ml-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg text-base w-40 flex flex-col items-center">
                                    <div class="text-center space-y-2 w-full">
                                        <div class="flex flex-row gap-4 w-full justify-center">
                                            <div>
                                                <div class="text-sm text-gray-600">Marks</div>
                                                <div class="font-semibold text-indigo-700 text-xl">{{ $quizQuestion->marks }}</div>
                                            </div>
                                            @if($quizQuestion->negative_marks > 0)
                                            <div>
                                                <div class="text-sm text-gray-600">Negative</div>
                                                <div class="font-semibold text-red-600 text-xl">-{{ $quizQuestion->negative_marks }}</div>
                                            </div>
                                            @endif
                                        </div>
                                        @if($quizQuestion->is_optional)
                                        <div>
                                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded">Optional</span>
                                        </div>
                                        @endif
                                        @if($quizQuestion->question->question_type->id == 3)
                                        <div>
                                            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">
                                                Note: You can answer multiple options.
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4 mt-4">
                        <div>

                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                onclick="return confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.');">
                                Submit Quiz
                            </button>
                        </div>

                        <a href="{{ route('quizzes.show', $quiz->id) }}" class="px-4 py-2 text-gray-700">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>