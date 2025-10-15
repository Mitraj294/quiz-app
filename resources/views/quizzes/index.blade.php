<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quizzes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-ful mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Error Messages -->
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">All Quizzes</h3>
                        @auth
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('quizzes.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Create Quiz</a>
                            @endif
                        @endauth
                    </div>

                    <!-- Quizzes List -->
                    @if(isset($quizzes) && $quizzes->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($quizzes as $quiz)
                                <a href="{{ route('quizzes.show', $quiz->id) }}" 
                                   class="block p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                    <h4 class="font-semibold mb-2">{{ $quiz->name }}</h4>
                                    @if($quiz->description)
                                        <p class="text-sm text-gray-600">{{ Str::limit($quiz->description, 120) }}</p>
                                    @endif
                                </a>
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
