<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Select Questions for: {{ $quiz->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('quizzes.questions.attach', $quiz) }}">
                    @csrf
                    <div class="space-y-4">
                        @forelse($questions as $question)
                            <div class="p-4 border rounded flex items-start gap-4">
                                <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" id="q_{{ $question->id }}" class="mt-1">
                                <div>
                                    <label for="q_{{ $question->id }}" class="font-semibold">{{ $question->name }}</label>
                                    @if($question->options && $question->options->count() > 0)
                                        <ul class="list-disc list-inside text-sm text-gray-700 mt-2">
                                            @foreach($question->options as $opt)
                                                <li>{{ $opt->name }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-600">No available questions found in the topics attached to this quiz.</p>
                        @endforelse
                   </div>
                    <div class="mt-6 flex gap-4">
                        <button type="submit"    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                     ">Attach Selected</button>
                        <a href="{{ route('quizzes.show', $quiz) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded">Cancel</a>
                   </div>
                 </form>
            </div>
        </div>
    </div>
</x-app-layout>
