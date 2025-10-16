<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Result - {{ $quiz->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">
                    {{ $quiz->name }}
                    <span class="text-sm font-normal text-gray-600">({{ $quiz->questions->count() }} Questions)</span>
                    <span class="float-right text-lg font-semibold">Score: {{ $attempt->score }}</span>
                </h3>

                <div>
                    <div class="space-y-4">
                        @php
                        $letters = ['A','B','C','D','E','F','G'];
                        @endphp

                        @foreach($quiz->questions as $index => $quizQuestion)
                        @php
                        $question = $quizQuestion->question;
                        $qAnswers = $answersByQuestion->get($question->id) ?? collect();
                        $selectedOptionIds = $qAnswers->pluck('option_id')->filter()->values()->all();
                        $userText = $qAnswers->pluck('answer_text')->filter()->first();
                        $correctOptions = $question->options->where('is_correct', 1)->pluck('id')->all();
                        @endphp

                        <div class="p-4 border rounded-lg hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-start gap-3">
                                        <span class="font-bold text-gray-800 mr-3 text-lg">Q{{ $index + 1 }}.</span>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900 mb-3 text-xl">{!! $question->question !!}</p>

                                            @if($question->question_type && $question->question_type->name === 'fill_the_blank')
                                                @php
                                                    // gather correct answers (array of strings)
                                                    $correctAnswers = $question->options->where('is_correct', 1)->pluck('option')->map(fn($v) => trim((string)$v))->filter()->values()->all();

                                                    // normalize submitted answer for comparison
                                                    $submitted = trim((string)($userText ?? ''));
                                                    $submittedNorm = strtolower($submitted);

                                                    $isBlankCorrect = collect($correctAnswers)
                                                        ->map(fn($v) => strtolower($v))
                                                        ->contains($submittedNorm);

                                                    $answerLabel = $submitted === '' ? 'No answer' : $submitted;

                                                    $answerClass = $isBlankCorrect
                                                        ? 'inline-block text-green-700 bg-green-100 border border-green-400 px-2 py-1 rounded font-semibold'
                                                        : 'inline-block text-red-700 bg-red-100 border border-red-400 px-2 py-1 rounded font-semibold';
                                                @endphp

                                                <div class="mb-3 text-gray-800">
                                                    <span class="font-semibold">Your answer:</span>
                                                    <span class="{{ $answerClass }}">{{ $answerLabel }}</span>
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

                                                // Determine state classes
                                                $classes = 'flex items-center gap-3 px-2 py-1 border-2 rounded mr-6 pr-6 ';
                                                if ($isCorrect && $isSelected) {
                                                // ✅ User selected the correct answer
                                                $classes .= 'bg-green-100 border-green-500 text-green-700 font-semibold';
                                                } elseif ($isCorrect && !$isSelected) {
                                                // ✅ Correct answer, but user missed it
                                                $classes .= 'bg-green-100 border-green-500 text-green-700';
                                                } elseif ($isSelected && !$isCorrect) {
                                                // ❌ User selected wrong answer
                                                $classes .= 'bg-red-100 border-red-500 text-red-700 font-semibold';
                                                } else {
                                                // Neutral / unselected wrong options
                                                $classes .= 'border-gray-300 text-gray-800';
                                                }
                                                @endphp

                                                <li class="flex items-center gap-3 text-base mt-2 pr-4">
                                                    <div class="{{ $classes }}">
                                                        <span class="w-8 h-8 flex items-center justify-center border rounded bg-white text-lg">
                                                            {{ $letters[$optIndex] ?? $optIndex + 1 }}
                                                        </span>
                                                        <span class="text-lg ml-2 mr-4 pr-2">{!! $opt->option !!}</span>
                                                    </div>
                                                </li>
                                                @endforeach
                                            </ul>

                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="ml-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg text-base w-40 flex flex-col items-center">
                                    <div class="text-center w-full">
                                        <div class="flex flex-row items-center justify-center gap-4">
                                            <div>
                                                <div class="text-sm text-gray-600">Marks</div>
                                                <div class="font-semibold text-indigo-700 text-xl">{{ $quizQuestion->marks }}</div>
                                            </div>
                                            <div>
                                                <div class="text-sm text-gray-600">Negative</div>
                                                <div class="font-semibold text-red-600 text-xl">{{ $quizQuestion->negative_marks }}</div>
                                            </div>
                                        </div>
                                        @if(!empty($quizQuestion->is_optional))
                                        <div class="mt-3">
                                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded">Optional</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Admin actions placeholder kept if needed -->
                        </div>
                        @endforeach
                    </div>

                    <div class="text-left py-8">
                        <a href="{{ route('quizzes.result_index', $quiz->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Back to Results
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>