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
                <a href="{{ route('admin.analytics.topics') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block">
                    <h4 class="text-sm text-gray-500">Topics</h4>
                    <div class="text-2xl font-bold">{{ $topicsCount ?? 0 }}</div>
                    <p class="text-sm text-gray-600 mt-2">Quiz topics in the system</p>
                </a>
                <a href="{{ route('admin.analytics.quizzes') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block">
                    <h4 class="text-sm text-gray-500">Quizzes</h4>
                    <div class="text-2xl font-bold">{{ $quizzesCount ?? 0 }}</div>
                    <p class="text-sm text-gray-600 mt-2">Total quizzes in the platform</p>
                </a>

                <a href="{{ route('admin.analytics.users') }}" class="w-full text-left bg-indigo-50 border-2 border-indigo-300 shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block ring-2 ring-indigo-200">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="text-sm text-indigo-700 font-semibold">Users</h4>
                            <div class="text-2xl font-bold text-indigo-800">{{ $usersCountNonAdmin ?? 0 }}</div>
                            <p class="text-sm text-indigo-700 mt-2">Registered users</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Users Analytics</h2>
            <div data-fragment="users" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">All Users</h3>
                <div class="space-y-4">
                   
                    @if($authorsList->count())
                    <div>
                        <h4 class="text-lg font-semibold mb-2">Authors</h4>
                        <div class="grid grid-cols-1  gap-4">
                            @foreach($authorsList as $user)
                            @php
                            $attempts = $user->attempts_count ?? 0;
                            $registered = optional($user->created_at)->toDayDateTimeString() ?? '';
                            $roles = method_exists($user, 'roles') ? $user->roles->pluck('role')->join(', ') : '';
                            @endphp
                            <div class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg border border-gray-200">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="text-2xl font-bold mb-1">{{ $user->name }}</h3>
                                        <p class="text-sm text-gray-600">{{ $user->email }}</p>
                                        @if($roles)
                                        <p class="text-xs text-gray-500 mt-1">Roles: {{ $roles }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg mt-4">
                                    <div>
                                        <span class="text-sm text-gray-600">Attempts</span>
                                        <p class="text-lg font-semibold">{{ $attempts }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-600">Registered</span>
                                        <p class="text-lg font-semibold">{{ $registered }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-600">Status</span>
                                        <p class="text-lg font-semibold">{{ $user->active ? 'Active' : 'Inactive' }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
           

                    <h4 class="text-lg font-semibold mb-2">Users</h4>

                    @if($nonAdminAuthors->isEmpty())
                        <div class="p-6 text-sm text-gray-500">No users to display.</div>
                    @else
                        @foreach($nonAdminAuthors as $user)
                        @php
                        $attempts = $user->attempts_count ?? 0;
                        $registered = optional($user->created_at)->toDayDateTimeString() ?? '';
                        $roles = method_exists($user, 'roles') ? $user->roles->pluck('role')->join(', ') : '';
                        @endphp
                        <div class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg border border-gray-200">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-2xl font-bold mb-1">{{ $user->name }}</h3>
                                    <p class="text-sm text-gray-600">{{ $user->email }}</p>
                                    @if($roles)
                                    <p class="text-xs text-gray-500 mt-1">Roles: {{ $roles }}</p>
                                    @endif
                                </div>
                            </div>
                            <div>
                                @php $quizAttempts = $userQuizAttempts[$user->id] ?? []; @endphp
                                @if(empty($quizAttempts))
                                <div class="col-span-3 text-sm text-gray-500">No quiz attempts</div>
                                @else
                                <ul class="grid grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg mt-4">
                                    @foreach($quizAttempts as $qa)
                                    <li class="p-3 bg-white border rounded flex items-center justify-between hover:bg-indigo-50 cursor-pointer">
                                        <a href="{{ route('quizzes.result_index', ['quiz' => $qa['quizId'], 'user_id' => $user->id]) }}" style="display:block;width:100%;height:100%;text-decoration:none;color:inherit">
                                            <div>
                                                <div class="text-xs text-gray-500">{{ $qa['topicName'] }}</div>
                                                <div class="font-semibold">{{ $qa['quizTitle'] }}</div>
                                            </div>
                                            <div class="text-right text-sm">
                                                <div>Attempts: <strong>{{ $qa['totalAttempts'] }}</strong></div>
                                                <div>Total Marks: <strong>{{ $qa['totalMarks'] }}</strong></div>
                                                <div>Avg Score: <strong>{{ $qa['avgScore'] }}</strong></div>
                                            </div>
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @endif
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
