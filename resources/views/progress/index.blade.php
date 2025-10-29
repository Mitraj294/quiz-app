<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Your Progress</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
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
        </div>
    </div>
</x-app-layout>
