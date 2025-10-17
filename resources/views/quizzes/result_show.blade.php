<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Result - {{ $quiz->name }}</h2>
    </x-slot>

    @php

    // Shared helpers & config
    $letters = ['A','B','C','D','E','F','G'];

    $optionClasses = function(bool $isCorrect, bool $isSelected) {
    $base = 'flex items-center gap-3 px-2 py-1 border-2 rounded mr-6 pr-6 ';
    if ($isCorrect && $isSelected) return $base . 'bg-green-100 border-green-400 text-green-700 font-semibold';
    if ($isCorrect) return $base . 'bg-green-100 border-green-400 text-green-700';
    if ($isSelected) return $base . 'bg-red-100 border-red-400 text-red-700 font-semibold';
    return $base . 'border-gray-300 text-gray-800';
    };

    $stateBoxClass = function(bool $hasAnswer, bool $isCorrect) {
    $base = 'ml-4 mt-4 p-4 rounded-lg text-base w-40 flex flex-col items-center text-lg ';
    if (! $hasAnswer) return $base . 'bg-red-100 border border-red-400 text-red-700';
    return $isCorrect ? $base . 'bg-green-100 border border-green-400 text-green-700' : $base . 'bg-red-100 border border-red-400 text-red-700';
    };

    $formatBlankAnswerClass = function(bool $isCorrect) {
    return $isCorrect
    ? 'inline-block text-green-700 bg-green-100 border border-green-400 px-2 py-1 rounded font-semibold'
    : 'inline-block text-red-700 bg-red-100 border border-red-400 px-2 py-1 rounded font-semibold';
    };
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">
                    {{ $quiz->name }}
                    <span class="text-sm font-normal text-gray-600">({{ $quiz->questions->count() }} Questions)</span>
                    <span class="float-right text-lg font-semibold">Score: {{ $attempt->score }}</span>
                </h3>

                <div class="space-y-4">
                    @foreach($quiz->questions as $index => $quizQuestion)
                    @php
                    $question = $quizQuestion->question;
                    $qAnswers = $answersByQuestion->get($question->id) ?? collect();
                    $selectedOptionIds = $qAnswers->pluck('option_id')->filter()->values()->all();
                    $userText = $qAnswers->pluck('answer_text')->filter()->first();
                    $correctOptions = $question->options->where('is_correct', 1)->pluck('id')->all();

                    // fill-in-the-blank specifics
                    $isBlank = $question->question_type && $question->question_type->name === 'fill_the_blank';
                    if ($isBlank) {
                    $correctAnswers = $question->options
                    ->where('is_correct', 1)
                    ->pluck('option')
                    ->map(fn($v) => trim((string)$v))
                    ->filter()
                    ->values()
                    ->all();

                    $submitted = trim((string)($userText ?? ''));
                    $submittedNorm = strtolower($submitted);
                    $isBlankCorrect = collect($correctAnswers)
                    ->map(fn($v) => strtolower($v))
                    ->contains($submittedNorm);
                    $answerLabel = $submitted === '' ? 'No answer' : $submitted;
                    }

                    // determine answer presence & correctness
                    if ($isBlank) {
                    $hasAnswer = trim((string)($userText ?? '')) !== '';
                    $isQuestionCorrect = $isBlankCorrect ?? false;
                    } else {
                    $hasAnswer = !empty($selectedOptionIds);
                    $sel = $selectedOptionIds ?? [];
                    $corr = $correctOptions ?? [];
                    $isQuestionCorrect = $hasAnswer && empty(array_diff($sel, $corr)) && empty(array_diff($corr, $sel));
                    }

                    $boxClass = $stateBoxClass($hasAnswer, $isQuestionCorrect);
                    @endphp

                    <div class="p-4 border rounded-lg hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-start gap-3">
                                    <span class="font-bold text-gray-800 mr-3 text-lg">Q{{ $index + 1 }}.</span>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900 mb-3 text-xl">{!! $question->question !!}</p>
                                        @if($quizQuestion->question->media_url)
                                        <div class="my-4 mb-2 mt-2">
                                            @if($quizQuestion->question->media_type === 'image')
                                            <img src="{{ asset($quizQuestion->question->media_url) }}" alt="Question Media" class="max-w-md rounded-lg shadow-md border border-gray-200">
                                            @elseif($quizQuestion->question->media_type === 'video')
                                            <video controls class="max-w-md rounded-lg shadow-md border border-gray-200">
                                                <source src="{{ asset($quizQuestion->question->media_url) }}" type="video/mp4">
                                                <!-- Placeholder track file: replace with real captions (.vtt) if available -->
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
                                        @if($isBlank)
                                        <div class="mb-3 text-gray-800">
                                            <span class="font-semibold">Your answer:</span>
                                            <span class="{{ $formatBlankAnswerClass($isBlankCorrect) }}">{{ $answerLabel }}</span>
                                        </div>

                                        <div class="mb-3 text-gray-800">
                                            <span class="font-semibold">Correct:</span>
                                            <span class="text-green-700 font-semibold">{{ implode(', ', $correctAnswers) }}</span>
                                        </div>
                                        @else
                                        <ul class="space-y-2 mt-3">
                                            @foreach($question->options as $optIndex => $opt)
                                            @php
                                            $isSelected = in_array($opt->id, $selectedOptionIds ?? []);
                                            $isCorrect = (bool) $opt->is_correct;
                                            $classes = $optionClasses($isCorrect, $isSelected);
                                            @endphp

                                            <li class="flex items-center gap-3 text-base mt-2 pr-4">
                                                <div class="{{ $classes }}">
                                                    <span class="w-8 h-8 flex items-center justify-center border rounded bg-white text-lg">
                                                        {{ $letters[$optIndex] ?? $optIndex + 1 }}
                                                    </span>
                                                    <span class="text-lg ml-2 mr-4 pr-2">{!! $opt->option !!}</span>
                                                </div>
                                                <div>
                                                    @if(! $isBlank && count($correctOptions) > 1 && $isCorrect && ! $isSelected)
                                                    <div class="ml-3 inline-block text-red-700 bg-red-100 border border-red-400 px-2 py-1 rounded font-semibold">
                                                        Correct, But not selected
                                                    </div>
                                                    @endif
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex-8 gap-3">
                                <div class="ml-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg text-base w-40 flex flex-col items-center">
                                    <div class="text-center w-full">
                                        <div class="flex flex-row items-center justify-center gap-4">
                                            <div>
                                                <div class="text-sm text-gray-600">Marks</div>
                                                <div class="font-semibold text-indigo-700 text-xl">{{ $quizQuestion->marks }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-gray-600">Negative</div>
                                                <div class="font-semibold text-red-600 text-xl"> - {{ $quizQuestion->negative_marks }}</div>
                                            </div>
                                        </div>
                                        @if(!empty($quizQuestion->is_optional))
                                        <div class="mt-3">
                                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded">Optional</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="{{ $boxClass }}">
                                    @if($isQuestionCorrect)
                                    <div class="text-sm text-green-700 font-semibold">Marks: {{ $quizQuestion->marks }}</div>
                                    @elseif($hasAnswer && ! $isQuestionCorrect)
                                    <div class="text-sm text-red-700 font-semibold">Marks: -{{ $quizQuestion->negative_marks }}</div>
                                    @else
                                    <div class="text-sm text-gray-700 font-semibold">Marks: 0</div>
                                    @endif
                                    <div class="text-sm mt-2 font-semibold">
                                        {{ !$hasAnswer ? 'Not answered' : ($isQuestionCorrect ? 'Correct' : 'Wrong') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @php
                // Summary / pass-fail
                $totalMarks = $quiz->questions->sum('marks') ?? 0;
                if (isset($quiz->pass_mark) && $quiz->pass_mark !== null) {
                $passed = $attempt->score >= $quiz->pass_mark;
                } elseif (isset($quiz->pass_percentage) && $quiz->pass_percentage !== null) {
                $passed = $attempt->score >= ($totalMarks * ($quiz->pass_percentage / 100));
                } else {
                $passed = $totalMarks ? ($attempt->score >= ($totalMarks * 0.5)) : ($attempt->score > 0);
                }

                $summaryBox = $passed
                ? 'ml-4 px-3 py-2 rounded text-base w-36 flex items-center justify-center h-12 bg-green-100 border border-green-200 text-green-700'
                : 'ml-4 px-3 py-2 rounded text-base w-36 flex items-center justify-center h-12 bg-red-100 border border-red-200 text-red-700';
                @endphp

                <div class="flex items-center justify-between px-4 py-8">
                    <a href="{{ route('quizzes.result_index', $quiz->id) }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Back to Results
                    </a>

                    <div class="{{ $summaryBox }}">
                        Score: {{ $attempt->score }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>