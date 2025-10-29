<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Analytics</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <a href="{{ url('/admin/analytics/topics') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block">
                            <h4 class="text-sm text-gray-500">Topics</h4>
                            <div class="text-2xl font-bold">{{ $topicsCount }}</div>
                            <p class="text-sm text-gray-600 mt-2">Quiz topics in the system</p>
                        </a>
                        <a href="{{ url('/admin/analytics/quizzes') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block">
                            <h4 class="text-sm text-gray-500">Quizzes</h4>
                            <div class="text-2xl font-bold">{{ $quizzesCount }}</div>
                            <p class="text-sm text-gray-600 mt-2">Total quizzes in the platform</p>
                        </a>
                        <a href="{{ url('/admin/analytics/users') }}" class="w-full text-left bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition p-6 cursor-pointer focus:outline-none block">
                            <h4 class="text-sm text-gray-500">Users</h4>
                            <div class="text-2xl font-bold">{{ $usersCountNonAdmin }}</div>
                            <p class="text-sm text-gray-600 mt-2">Registered users</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>