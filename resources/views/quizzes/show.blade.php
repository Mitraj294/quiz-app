<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $quiz->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-700 mb-4">{{ $quiz->description }}</p>

                <div class="flex gap-4">
                    @auth
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('quizzes.questions.select', $quiz->id) }}"   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                       >Add from existing questions</a>
                            @if($quiz->topics->isNotEmpty())
                                <a href="{{ route('topics.questions.create', $quiz->topics->first()) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Create new question
                                </a>
                            @else
                                <a href="{{ route('topics.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Create new topic first
                                </a>
                            @endif
                        @else
                            <p class="text-sm text-gray-600">You don't have permission to modify this quiz.</p>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
