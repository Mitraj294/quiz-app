<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Topic Progress</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Overview</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Topics Overview -->
                    <a href="{{ route('progress.topic') }}" class="w-full text-left bg-indigo-50 border-2 border-indigo-300 shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block ring-2 ring-indigo-200">
                        <div>
                            <h4 class="text-sm text-indigo-700 font-semibold">Topics</h4>
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
                            <div class="text-2xl font-bold text-indigo-800">{{ $userTopicCount }}</div>
                            <p class="text-sm text-indigo-700 mt-2">Topics you've participated in</p>
                        </div>
                    </a>
                    <!-- Quizzes Overview -->
                    <a href="{{ route('progress.quiz') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block">
                        <h4 class="text-sm text-gray-500">Quizzes</h4>
                        @php
                        $userQuizCount = Auth::user()->attempts()->whereNotNull('completed_at')->distinct('quiz_id')->count('quiz_id');
                        @endphp
                        <div class="text-2xl font-bold">{{ $userQuizCount }}</div>
                        <p class="text-sm text-gray-600 mt-2">Quizzes you've completed</p>
                    </a>
                </div>
            </div>
        </div>

        <!-- Participated Topics -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Topics you've participated in</h3>
                <div class="space-y-4">
                    @forelse($topics as $topic)
                    <div class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg border border-gray-200">
                        <h3 class="text-2xl font-bold mb-4">{{ $topic->name }}</h3>
                        <div class="mb-2">
                            <p class="text-gray-800">{{ $topic->description ?? $topic->name }}</p>
                        </div>
                        <div class="flex gap-3 mt-3">
                            <a href="{{ url('/topics/' . $topic->id ) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View Details</a>
                        </div>
                        @php
                        $hasSubtopics = $topic->children->isNotEmpty();
                        $userQuizIds = \App\Models\Attempt::where('user_id', Auth::id())
                        ->whereNotNull('completed_at')
                        ->pluck('quiz_id')->unique()->values()->all();
                        $topicableIds = \DB::table('topicables')
                        ->where('topic_id', $topic->id)
                        ->where('topicable_type', 'App\\Models\\Quiz')
                        ->pluck('topicable_id')->all();
                        $relatedQuizzes = \App\Models\Quiz::whereIn('id', $topicableIds)
                        ->whereIn('id', $userQuizIds)
                        ->get();
                        $hasRelated = $relatedQuizzes->isNotEmpty();
                        @endphp
                        <div class="grid grid-cols-1 {{ ($hasSubtopics && $hasRelated) ? 'md:grid-cols-2' : 'md:grid-cols-1' }} gap-4">
                            <!-- Subtopics -->
                            @if($hasSubtopics)
                            <div class="mt-2 p-3 border border-gray-100 bg-gray-50 rounded-lg">
                                <h4 class="text-lg font-semibold mb-4">Subtopics</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($topic->children as $sub)
                                    <div>
                                        <div class="block text-left w-full p-4 border border-gray-100 rounded-lg bg-white">
                                            <h5 class="font-semibold mb-2">{{ $sub->name }}</h5>
                                            <p class="text-sm text-gray-700">{{ $sub->description ?? $sub->name }}</p>
                                            <div class="flex gap-3 mt-3">
                                                <a href="{{ url('/topics/' . $sub->id ) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Related Quizzes -->
                            <div class="mt-2 p-3 border border-gray-100 bg-gray-50 rounded-lg">
                                <h4 class="text-lg font-semibold mb-4">Related Quizzes</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @forelse($relatedQuizzes as $quiz)
                                    <div class="border border-gray-100 rounded-lg p-4 hover:shadow-sm transition bg-white">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h5 class="font-semibold mb-2">{{ $quiz->title ?? $quiz->name }}</h5>
                                                <div class="flex items-center gap-4 text-xs text-gray-500 mb-2">
                                                    <span> Total: {{ $quiz->total_marks ?? 0 }} marks</span>
                                                    <span>Pass: {{ $quiz->pass_marks ?? 0 }} marks</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex gap-3 mt-3">
                                            <a href="{{ url('/quizzes/' . $quiz->id . '/results') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View Details</a>
                                        </div>
                                    </div>
                                    @empty
                                    <p class="text-sm text-gray-600">No related quizzes found for you in this topic.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-gray-500">No topic progress data yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Remaining Topics (only top-level, no subtopics) -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Topics You Can Participate In</h3>
            <div class="space-y-4">
                @php
                $topLevelRemaining = collect($remainingTopics)->whereNull('parent_id')->values();
                @endphp

                @forelse($topLevelRemaining as $topic)
                <div class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg border border-gray-200">
                <h3 class="text-2xl font-bold mb-4">{{ $topic->name }}</h3>
                <div class="mb-2">
                    <p class="text-gray-800">{{ $topic->description ?? $topic->name }}</p>
                </div>
                <div class="flex gap-3 mt-3">
                    <a href="{{ url('/topics/' . $topic->id ) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View Details</a>
                </div>

                @php
                    $hasSubtopics = $topic->children->isNotEmpty();
                    $topicableIds = \DB::table('topicables')
                    ->where('topic_id', $topic->id)
                    ->where('topicable_type', 'App\\Models\\Quiz')
                    ->pluck('topicable_id')
                    ->all();
                    $relatedQuizzes = \App\Models\Quiz::whereIn('id', $topicableIds)->get();
                    $hasRelated = $relatedQuizzes->isNotEmpty();
                @endphp

                <div class="grid grid-cols-1 {{ ($hasSubtopics && $hasRelated) ? 'md:grid-cols-2' : 'md:grid-cols-1' }} gap-4 mt-6">
                    <!-- Subtopics -->
                    @if($hasSubtopics)
                    <div class="mt-2 p-3 border border-gray-100 bg-gray-50 rounded-lg">
                    <h4 class="text-lg font-semibold mb-4">Subtopics</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($topic->children as $sub)
                        @php
                            $subQuizIds = \DB::table('topicables')
                            ->where('topic_id', $sub->id)
                            ->where('topicable_type', 'App\\Models\\Quiz')
                            ->pluck('topicable_id')
                            ->all();
                            $subTotal = count($subQuizIds);
                            $subAttempted = \App\Models\Attempt::where('user_id', Auth::id())
                            ->whereNotNull('completed_at')
                            ->whereIn('quiz_id', $subQuizIds)
                            ->distinct()
                            ->count('quiz_id');
                        @endphp
                        <div>
                            <div class="block text-left w-full p-4 border border-gray-100 rounded-lg bg-white">
                            <h5 class="font-semibold mb-2">{{ $sub->name }}</h5>
                            <p class="text-sm text-gray-700">{{ $sub->description ?? $sub->name }}</p>
                            <div class="mt-3 text-sm text-gray-600">
                                @if($subTotal > 0)
                                <span>Progress: {{ $subAttempted }} / {{ $subTotal }} quizzes attempted</span>
                                @else
                                <span>No quizzes in this subtopic yet.</span>
                                @endif
                            </div>
                            <div class="flex gap-3 mt-3">
                                <a href="{{ url('/topics/' . $sub->id ) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View Details</a>
                            </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    </div>
                    @endif

                    <!-- Related Quizzes -->
                    <div class="mt-2 p-3 border border-gray-100 bg-gray-50 rounded-lg">
                    <h4 class="text-lg font-semibold mb-4">Related Quizzes</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse($relatedQuizzes as $quiz)
                        @php
                            $attempt = \App\Models\Attempt::where('user_id', Auth::id())
                            ->where('quiz_id', $quiz->id)
                            ->whereNotNull('completed_at')
                            ->orderByDesc('completed_at')
                            ->first();
                            $score = $attempt ? ($attempt->score ?? $attempt->marks ?? $attempt->marks_obtained ?? null) : null;
                            $passed = (!is_null($score) && !is_null($quiz->pass_marks)) ? ($score >= $quiz->pass_marks) : null;
                        @endphp

                        <div class="border border-gray-100 rounded-lg p-4 hover:shadow-sm transition bg-white">
                            <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h5 class="font-semibold mb-2">{{ $quiz->title ?? $quiz->name }}</h5>
                                <div class="flex items-center gap-4 text-xs text-gray-500 mb-2">
                                <span>Total: {{ $quiz->total_marks ?? 0 }} marks</span>
                                <span>Pass: {{ $quiz->pass_marks ?? 0 }} marks</span>
                                </div>

                                <div class="text-sm">
                                @if($attempt)
                                    <div class="text-gray-700">
                                    <span class="font-medium">Last attempt:</span>
                                    @if(!is_null($score))
                                        <span> Score: {{ $score }}</span>
                                        @if(!is_null($passed))
                                        <span class="ml-2 {{ $passed ? 'text-green-600' : 'text-red-600' }}">
                                            ({{ $passed ? 'Passed' : 'Failed' }})
                                        </span>
                                        @endif
                                    @else
                                        <span> Completed</span>
                                    @endif
                                    </div>
                                @else
                                    <div class="text-gray-600">Not attempted yet</div>
                                @endif
                                </div>
                            </div>
                            </div>

                            <div class="flex gap-3 mt-3">
                                <a href="{{ url('/quizzes/' . $quiz->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">Attempt Quiz</a>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-600">No related quizzes found for this topic.</p>
                        @endforelse
                    </div>
                    </div>
                </div>
                </div>
                @empty
                <div class="text-gray-500">No remaining top-level topics available.</div>
                @endforelse
            </div>
            </div>
        </div>
    </div>
</x-app-layout>