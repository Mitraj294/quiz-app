<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Quiz Progress</h2>
        </div>
    </x-slot>
    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Overview</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <a href="{{ route('progress.topic') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block">
                        <h4 class="text-sm text-gray-500">Topics</h4>
                        @php
                        $userTopicCount = Auth::user()->attempts()->whereNotNull('completed_at')
                        ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                        ->join('topicables', function($join) {
                        $join->on('quizzes.id', '=', 'topicables.topicable_id')
                        ->where('topicables.topicable_type', 'App\\Models\\Quiz');
                        })
                        ->distinct('topicables.topic_id')
                        ->count('topicables.topic_id');
                        @endphp
                        <div class="text-2xl font-bold">{{ $userTopicCount }}</div>
                        <p class="text-sm text-gray-600 mt-2">Topics you've participated in</p>
                    </a>
                    <a href="{{ route('progress.quiz') }}" class="w-full text-left bg-indigo-50 border-2 border-indigo-300 shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block ring-2 ring-indigo-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="text-sm text-indigo-700 font-semibold">Quizzes</h4>
                                @php
                                $userQuizCount = Auth::user()->attempts()->whereNotNull('completed_at')->distinct('quiz_id')->count('quiz_id');
                                @endphp
                                <div class="text-2xl font-bold text-indigo-800">{{ $userQuizCount }}</div>
                                <p class="text-sm text-indigo-700 mt-2">Quizzes you've completed</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Quizzes you've completed</h3>
                <div class="space-y-4">
                    @forelse($quizzes as $quiz)
                    @php
                    $totalQuestions = $quiz->questions_count ?? $quiz->questions->count() ?? 0;
                    $computedTotalMarks = $quiz->questions ? $quiz->questions->sum('marks') : 0;
                    $passMarks = $quiz->pass_marks ?? (int) round($computedTotalMarks / 3);
                    $maxAttempts = $quiz->max_attempts ?? 'Unlimited';
                    @endphp
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="text-lg font-semibold">{{ $quiz->title ?? $quiz->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $quiz->description ?? '' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 md:grid-cols-6 gap-4 mb-6 p-4 bg-gray-50 rounded-lg mt-4">
                            <div>
                                <span class="text-sm text-gray-600">Total Questions</span>
                                <p class="text-lg font-semibold">{{ $totalQuestions }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Total Marks</span>
                                <p class="text-lg font-semibold">{{ $computedTotalMarks }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Pass Marks</span>
                                <p class="text-lg font-semibold">{{ $passMarks }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Max Attempts</span>
                                <p class="text-lg font-semibold">{{ $maxAttempts }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Exam Duration</span>
                                <p class="text-lg font-semibold">{{ $quiz->duration ? $quiz->duration . ' min' : '—' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Valid Upto</span>
                                @php
                                    $validUptoUtc = $quiz->valid_upto ? \Carbon\Carbon::parse($quiz->valid_upto)->setTimezone('UTC')->toIso8601String() : '';
                                @endphp
                                <p class="text-lg font-semibold" data-utc="{{ $validUptoUtc }}">
                                    <span class="no-js">{{ $quiz->valid_upto ?? '—' }}</span>
                                </p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Your Attempt</span>
                                @php
                                    $stats = $userQuizStats[$quiz->id] ?? null;
                                @endphp
                                <p class="text-lg font-semibold">{{ $stats && $stats['attemptCount'] > 0 ? $stats['attemptCount'] : '—' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Avg. Time Taken</span>
                                <p class="text-lg font-semibold">
                                    {{ !empty($stats['avgTimeTaken']) ? $stats['avgTimeTaken'] . ' min' : '—' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Avg. Score</span>
                                <p class="text-lg font-semibold">
                                    {{ !empty($stats['avgScore']) ? $stats['avgScore'] : '—' }}
                                </p>
                            </div>
                        </div>
                        <x-utc-converter />
                        <div class="flex gap-3 mt-3">
                            <a href="{{ url('/quizzes/' . $quiz->id . '/results') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View Details</a>
                        </div>
                    </div>
                    @empty
                    <div class="text-gray-500">No quiz progress data yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Quizzes You Can Attempt</h3>
                <div class="space-y-4">
                    @forelse($remainingQuizzes as $quiz)
                    @php
                    $totalQuestions = $quiz->questions_count ?? $quiz->questions->count() ?? 0;
                    $computedTotalMarks = $quiz->questions ? $quiz->questions->sum('marks') : 0;
                    $passMarks = $quiz->pass_marks ?? (int) round($computedTotalMarks / 3);
                    $maxAttempts = $quiz->max_attempts ?? 'Unlimited';
                    @endphp
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="text-lg font-semibold">{{ $quiz->title ?? $quiz->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $quiz->description ?? '' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 md:grid-cols-5 gap-4 mb-6 p-4 bg-gray-50 rounded-lg mt-4">
                            <div>
                                <span class="text-sm text-gray-600">Total Questions</span>
                                <p class="text-lg font-semibold">{{ $totalQuestions }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Total Marks</span>
                                <p class="text-lg font-semibold">{{ $computedTotalMarks }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Pass Marks</span>
                                <p class="text-lg font-semibold">{{ $passMarks }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Max Attempts</span>
                                <p class="text-lg font-semibold">{{ $maxAttempts }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Exam Duration</span>
                                <p class="text-lg font-semibold">{{ $quiz->duration ? $quiz->duration . ' min' : '—' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Valid Upto</span>
                                @php
                                    $validUptoUtc = $quiz->valid_upto ? \Carbon\Carbon::parse($quiz->valid_upto)->setTimezone('UTC')->toIso8601String() : '';
                                @endphp
                                <p class="text-lg font-semibold" data-utc="{{ $validUptoUtc }}">
                                    <span class="no-js">{{ $quiz->valid_upto ?? '—' }}</span>
                                </p>
                            </div>
                        </div>
                        <x-utc-converter />
                        <div class="flex gap-3 mt-3">
                            <a href="{{ route('quizzes.show', $quiz->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">Start Quiz</a>
                        </div>
                    </div>
                    @empty
                    <div class="text-gray-500">No remaining quizzes available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>