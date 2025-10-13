<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $topic->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('topics.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                            ← Back to Topics
                        </a>
                    </div>

                    <h3 class="text-2xl font-bold mb-4">{{ $topic->name }}</h3>
                    
                    @if($topic->description)
                        <div class="mb-6">
                            <p class="text-gray-700">{{ $topic->description }}</p>
                        </div>
                    @endif

                    <div class="mt-8">
                        <h4 class="text-lg font-semibold mb-4">Related Quizzes</h4>
                        @if(isset($topic->quizzes) && $topic->quizzes->count() > 0)
                            <div class="space-y-4">
                                @foreach($topic->quizzes as $quiz)
                                    <div class="border border-gray-300 rounded-lg p-4">
                                        <h5 class="font-semibold mb-2">{{ $quiz->title }}</h5>
                                        @if($quiz->description)
                                            <p class="text-sm text-gray-600 mb-2">{{ $quiz->description }}</p>
                                        @endif
                                        <a href="#" class="text-sm text-indigo-600 hover:text-indigo-900">
                                            Take Quiz →
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No quizzes available for this topic yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
