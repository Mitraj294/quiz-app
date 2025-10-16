<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Results - {{ $quiz->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Your Attempts for {{ $quiz->name }}</h3>

                @if($attempts->count() === 0)
                    <p class="text-gray-600">No attempts found for this quiz.</p>
                @else
                    <div class="space-y-6">
                        @foreach($attempts as $attempt)
                            <div class="p-6 border rounded-lg bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-gray-600">Score</div>
                                        <div class="text-lg font-semibold">
                                            @if($attempt->score >= (float)($quiz->pass_mark ?? 0))
                                                <span class="text-green-600">{{ $attempt->score }}</span>
                                            @else
                                                <span class="text-red-600">{{ $attempt->score }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-600">Passed</div>
                                        <div class="text-lg">
                                            @if($attempt->passed)
                                                <span class="text-green-600 font-semibold">Yes</span>
                                            @else
                                                <span class="text-red-600 font-semibold">No</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-600">Started At</div>
                                        <div class="text-lg">{{ $attempt->created_at }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-600">Completed At</div>
                                        <div class="text-lg">{{ $attempt->completed_at }}</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="#" class="inline-flex items-center px-3 py-2 bg-white border border-gray-200 rounded text-sm text-blue-600 hover:bg-gray-50">View</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $attempts->links() }}
                    </div>
                @endif

                <div class="mt-6">
                    <a href="{{ route('quizzes.show', $quiz->id) }}" class="text-sm text-gray-600 hover:underline">Back to Quiz</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
