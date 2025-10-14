<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold mb-4">{{ __("Welcome back!") }}</h3>
                    
                    <div class="space-y-3">
                        <!-- Name -->
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <div>
                                <span class="text-sm text-gray-600">Name:</span>
                                <span class="ml-2 font-semibold">{{ Auth::user()->name }}</span>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <span class="text-sm text-gray-600">Email:</span>
                                <span class="ml-2 font-semibold">{{ Auth::user()->email }}</span>
                            </div>
                        </div>

                        <!-- Role -->
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <div>
                                <span class="text-sm text-gray-600">Role:</span>
                                <span class="ml-2 font-semibold capitalize">
                                    @if(Auth::user()->roles->isNotEmpty())
                                        {{ Auth::user()->roles->pluck('role')->join(', ') }}
                                    @else
                                        User
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            @if(Auth::check() && Auth::user()->isAdmin())
             

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Create Quiz Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-lg mb-4">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Manage Quiz</h3>
                            <p class="text-sm text-gray-600 mb-4">Add new quizzes to the platform</p>
                            <a href="{{ route('quizzes.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Create →</a>
                        </div>
                    </div>

                    <!-- Manage Topics Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Manage Topics</h3>
                            <p class="text-sm text-gray-600 mb-4">Organize quiz categories</p>
                            <a href="/topics" class="text-green-600 hover:text-green-800 text-sm font-medium">Manage →</a>
                        </div>
                    </div>

                    <!-- View Users Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-lg mb-4">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Users</h3>
                            <p class="text-sm text-gray-600 mb-4">Manage user accounts</p>
                            <a href="#" class="text-yellow-600 hover:text-yellow-800 text-sm font-medium">View All →</a>
                        </div>
                    </div>

                    <!-- Analytics Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg mb-4">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Analytics</h3>
                            <p class="text-sm text-gray-600 mb-4">View platform statistics</p>
                            <a href="#" class="text-purple-600 hover:text-purple-800 text-sm font-medium">View Stats →</a>
                        </div>
                    </div>
                </div>
            @else
                <!-- Regular User Dashboard -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Take Quiz Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Take a Quiz</h3>
                            <p class="text-sm text-gray-600 mb-4">Start a new quiz and test your knowledge</p>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Start Now →</a>
                        </div>
                    </div>

                    <!-- Browse Topics Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Browse Topics</h3>
                            <p class="text-sm text-gray-600 mb-4">Explore quizzes by topic</p>
                            <a href="/topics" class="text-green-600 hover:text-green-800 text-sm font-medium">View Topics →</a>
                        </div>
                    </div>

                    <!-- View Progress Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                        <div class="p-6">
                            <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg mb-4">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Your Progress</h3>
                            <p class="text-sm text-gray-600 mb-4">Track your quiz history and scores</p>
                            <a href="#" class="text-purple-600 hover:text-purple-800 text-sm font-medium">View Stats →</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
