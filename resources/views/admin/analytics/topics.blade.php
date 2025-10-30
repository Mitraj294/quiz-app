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
                <a href="{{ route('admin.analytics.topics') }}" class="w-full text-left bg-indigo-50 border-2 border-indigo-300 shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block ring-2 ring-indigo-200">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="text-sm text-indigo-700 font-semibold">Topics</h4>
                            <div class="text-2xl font-bold text-indigo-800">{{ $topicsCount }}</div>
                            <p class="text-sm text-indigo-700 mt-2">Quiz topics in the system</p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('admin.analytics.quizzes') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block">
                    <h4 class="text-sm text-gray-500">Quizzes</h4>
                    <div class="text-2xl font-bold">{{ $quizzesCount ?? 0}}</div>
                    <p class="text-sm text-gray-600 mt-2">Total quizzes in the platform</p>
                </a>
                <a href="{{ route('admin.analytics.users') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block">
                    <h4 class="text-sm text-gray-500">Users</h4>
                    <div class="text-2xl font-bold">{{ $usersCountNonAdmin ?? 0}}</div>
                    <p class="text-sm text-gray-600 mt-2">Registered users</p>
                </a>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Topics Analytics</h3>
            <div class="space-y-6">
                @foreach($topics as $topic)
                <div class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg border border-gray-200">
                    <h3 class="text-2xl font-bold mb-4">{{ $topic->name }}</h3>
                    <div class="mb-2">
                        <p class="text-gray-800">{{ $topic->description ?? $topic->name }}</p>
                    </div>
                    <div class="flex gap-3 mt-3">
                                        <a href="{{ route('topics.show', $topic->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View Details</a>
                    </div>
                    @php
                        $quizStats = $topicQuizStats[$topic->id] ?? [];
                        $hasChildren = !empty($topic->children) && $topic->children->isNotEmpty();
                        $hasQuizzes = !empty($quizStats) && count($quizStats) > 0;
                    @endphp

                    <div class="grid grid-cols-1 {{ ($hasChildren && $hasQuizzes) ? 'md:grid-cols-2' : 'md:grid-cols-1' }} gap-4">
                        @if($hasChildren)
                        <div class="mt-2 p-3 border border-gray-100 bg-gray-50 rounded-lg">
                            <h4 class="text-lg font-semibold mb-4">Subtopic</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($topic->children as $sub)
                                <div>
                                    <div class="block text-left w-full p-4 border border-gray-100 rounded-lg bg-white">
                                        <h5 class="font-semibold mb-2">{{ $sub->name }}</h5>
                                        <p class="text-sm text-gray-700">{{ $sub->description ?? $sub->name }}</p>
                                        <div class="flex gap-3 mt-3">
                                            <a href="{{ route('topics.show', $sub->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View Details</a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="mt-2 p-3 border border-gray-100 bg-gray-50 rounded-lg">
                            <h4 class="text-lg font-semibold mb-4">Related Quizzes</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @forelse($quizStats as $quiz)
                                <div class="border border-gray-100 rounded-lg p-4 hover:shadow-sm transition bg-white">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h5 class="font-semibold mb-2">{{ $quiz['title'] }}</h5>
                                            <div class="flex items-center gap-4 text-xs text-gray-500 mb-2">
                                                <span> Total: {{ $quiz['totalMarks'] }} marks</span>
                                                <span>Pass: {{ $quiz['passMarks'] }} marks</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-3 mt-3">
                                        <a href="{{ route('quizzes.show', $quiz['id']) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View Details</a>
                                    </div>
                                </div>
                                @empty
                                <p class="text-sm text-gray-600">No quizzes found for this topic.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                <div class="mt-4">
                    {{ $topics->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection