<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quizzes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">All Quizzes</h3>
                        @auth
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('quizzes.create') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Create Quiz
                                </a>
                            @endif
                        @endauth
                    </div>

                    @if(isset($quizzes) && $quizzes->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($quizzes as $quiz)
                                @php
                                    $isAdmin = Auth::user()->isAdmin();
                                    $canView = $isAdmin || $quiz->is_published;
                                @endphp

                                @if($canView)
                                    <a href="{{ route('quizzes.show', $quiz->id) }}" 
                                       class="block p-4 border border-gray-300 rounded-lg hover:{{ $isAdmin ? 'bg-gray-50' : 'bg-blue-50' }} transition">
                                        <h4 class="font-semibold mb-2">{{ $quiz->name }}</h4>
                                        @if($quiz->description)
                                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($quiz->description, 120) }}</p>
                                        @endif
                              
                                        @if(!$isAdmin)
                                            <span class="inline-flex items-center text-sm text-blue-600 font-medium">
                                                Quiz Details
                                            </span>
                                        @endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No quizzes available yet. Create one to get started!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
