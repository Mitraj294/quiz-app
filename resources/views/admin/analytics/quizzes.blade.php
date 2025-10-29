<x-app-layout>
    <style>
        /* Disable Tailwind's .min-h-screen for this page only */
        .min-h-screen {
            min-height: unset !important;
        }
    </style>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Analytics</h2>
        </div>
    </x-slot>
</x-app-layout>
@extends('admin.analytics.layout')
@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="{{ url('/admin/analytics/topics') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block">
                    <h4 class="text-sm text-gray-500">Topics</h4>
                    <div class="text-2xl font-bold">{{ $topicsCount ?? 0 }}</div>
                    <p class="text-sm text-gray-600 mt-2">Quiz topics in the system</p>
                </a>
                <a href="{{ url('/admin/analytics/quizzes') }}" class="w-full text-left bg-indigo-50 border-2 border-indigo-300 shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block ring-2 ring-indigo-200">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="text-sm text-indigo-700 font-semibold">Quizzes</h4>
                            <div class="text-2xl font-bold text-indigo-800">{{ $quizzesCount ?? 0 }}</div>
                            <p class="text-sm text-indigo-700 mt-2">Total quizzes in the platform</p>
                        </div>
                    </div>
                </a>
                <a href="{{ url('/admin/analytics/users') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block">
                    <h4 class="text-sm text-gray-500">Users</h4>
                    <div class="text-2xl font-bold">{{ $usersCountNonAdmin ?? 0 }}</div>
                    <p class="text-sm text-gray-600 mt-2">Registered users</p>
                </a>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Quizzes Analytics</h3>
            <div data-fragment="quizzes" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">All Quizzes</h3>
                <div class="space-y-4">
                    @foreach($quizzes as $quiz)
                    @php $stats = $quizStats[$quiz->id] ?? null; @endphp
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="text-lg font-semibold">{{ $quiz->title ?? $quiz->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $quiz->description ?? '' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg mt-4">
                            <div>
                                <span class="text-sm text-gray-600">Total Questions</span>
                                <p class="text-lg font-semibold">{{ $stats['totalQuestions'] ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Mandatory</span>
                                <p class="text-lg font-semibold">{{ $stats['mandatory'] ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Optional</span>
                                <p class="text-lg font-semibold">{{ $stats['optional'] ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Total Marks</span>
                                <p class="text-lg font-semibold">{{ $stats['totalMarks'] ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Pass Marks</span>
                                <p class="text-lg font-semibold">{{ $stats['passMarks'] ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Max Attempts</span>
                                <p class="text-lg font-semibold">{{ $stats['maxAttempts'] ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg mt-4">
                            <div>
                                <span class="text-sm text-gray-600">Users Attempted</span>
                                <p class="text-lg font-semibold ">{{ $stats['usersAttempted'] !== null ? $stats['usersAttempted'] : 0 }}</p>
                               
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Total Attempts</span>
                                <p class="text-lg font-semibold">{{ $stats['totalAttempts'] ?? 0 }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Average Score</span>
                                @if(is_null($stats['average_score']))
                                <p class="text-lg font-semibold text-gray-600">N/A</p>
                                @else
                                <p class="text-lg font-semibold {{ $stats['average_score'] >= ($stats['passMarks'] ?? 0) ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $stats['average_score'] }}
                                </p>
                                @endif
                            </div>
                        </div>

                        @if(isset($stats['usersAttempted']) && $stats['usersAttempted'] > 0)
                            <button type="button" aria-expanded="false" aria-controls="users-list-{{ $quiz->id }}" class="text-sm text-indigo-600 hover:underline focus:outline-none" onclick="(function(btn){var el=document.getElementById('users-list-{{ $quiz->id }}'); el.classList.toggle('hidden'); btn.setAttribute('aria-expanded', (!el.classList.contains('hidden')).toString());})(this);">
                                View users who attempted this quiz
                            </button>
                        @endif

                        <!-- Hidden list of users who attempted this quiz (toggle visible on click) -->
                        @php
                        // Get attempts for this quiz (non-deleted) and user names
                        $attemptsForQuiz = \App\Models\Attempt::where('quiz_id', $quiz->id)
                        ->whereNotNull('user_id')
                        ->whereNull('deleted_at')
                        ->get();

                        $userIds = $attemptsForQuiz->pluck('user_id')->unique();
                        $userNames = \App\Models\User::whereIn('id', $userIds)->pluck('name', 'id');

                        // Precompute attempts count and average score per user to avoid N+1 queries
                        $attemptCounts = $attemptsForQuiz->groupBy('user_id')->map->count(); // [user_id => count]
                        $attemptAverages = $attemptsForQuiz->groupBy('user_id')->map(function ($group) {
                        // Assumes attempts have a 'score' column; adjust if different
                        $avg = $group->avg('score');
                        return is_null($avg) ? null : round($avg, 2);
                        });
                        @endphp
                        <div id="users-list-{{ $quiz->id }}" class="hidden mt-4 p-4 bg-white border border-gray-200 rounded-lg">
                            @if($userNames->isEmpty())
                            <p class="text-sm text-gray-500">No users have attempted this quiz yet.</p>
                            @else
                            <!-- Grid with three columns: Name (with quick summary), Attempts, Average Score -->
                            <div class="grid grid-cols-3 gap-4 mb-2 text-sm font-semibold text-gray-600">
                                <div>Name</div>
                                <div>Attempts</div>
                                <div>Average Score</div>
                            </div>

                            <ol class="space-y-1 max-h-48 overflow-auto">
                                @foreach($userNames as $uid => $name)
                                @php
                                $count = $attemptCounts->get($uid, 0);
                                $avg = $attemptAverages->get($uid);
                                $avgDisplay = is_null($avg) ? 'N/A' : number_format($avg, 2);
                                @endphp
                                <li class="grid grid-cols-3 gap-4 items-center py-1">

                                    <div class="text-lg font-semibold">
                                        {{ $name }}

                                    </div>

                                    <div class="text-lg font-semibold">
                                        {{ $count }}
                                    </div>


                                    @if(is_null($avg))
                                        <div class="text-lg font-semibold">N/A</div>
                                    @else
                                        <div class="text-lg font-semibold {{ ($avg >= ($stats['passMarks'] ?? 0)) ? 'text-green-600' : 'text-red-600' }}">
                                            {{ number_format($avg, 2) }}
                                        </div>
                                    @endif
                                </li>
                                @endforeach
                            </ol>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    <div class="mt-4">
                        {{ $quizzes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection