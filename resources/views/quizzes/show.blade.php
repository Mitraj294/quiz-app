<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $quiz->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Quiz Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xl font-bold mb-4">{{ $quiz->name }}</h3>
                <p class="text-gray-700 mb-4">{{ $quiz->description }}</p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <span class="text-sm text-gray-600">Total Marks</span>
                        <p class="text-lg font-semibold">{{ $quiz->total_marks }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Pass Marks</span>
                        <p class="text-lg font-semibold">{{ $quiz->pass_marks }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Max Attempts</span>
                        <p class="text-lg font-semibold">{{ $quiz->max_attempts ?: 'Unlimited' }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Status</span>
                        <p class="text-lg font-semibold">
                            @if($quiz->is_published)
                            <span class="text-green-600">Published</span>
                            @else
                            <span class="text-yellow-600">Draft</span>
                            @endif
                        </p>
                    </div>
                </div>

                @auth
                @if(Auth::user()->isAdmin())
                <div class="flex gap-4">
                    <a href="{{ route('quizzes.questions.select', $quiz->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Add from existing questions
                    </a>
                    <a href="{{ route('quizzes.questions.create', $quiz->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Create & Attach to Quiz
                    </a>

                    <a href="{{ route('topics.questions.create', $quiz->topics->first()->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Create new question
                    </a>


                </div>
                @endif
                @endauth
            </div>

            <!-- Questions List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">
                    All The Best For {{ $quiz->name }}
                    <span class="text-sm font-normal text-gray-600">({{ $quiz->questions->count() }} Questions)</span>
                </h3>

                @if($quiz->questions->count() > 0)
                <div>

                    <div class="space-y-4">
                        @foreach($quiz->questions as $index => $quizQuestion)
                        <div class="p-4 border rounded-lg hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-start gap-3">
                                        <span class="font-bold text-gray-800 mr-3 text-lg">Q{{ $index + 1 }}.</span>
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-900 mb-3 text-xl">{{ $quizQuestion->question->name }}</p>

                                            @if($quizQuestion->question->media_url)
                                            <div class="my-4 mb-2 mt-2">
                                                @if($quizQuestion->question->media_type === 'image')
                                                <img src="{{ asset($quizQuestion->question->media_url) }}" alt="Question Media" class="max-w-md rounded-lg shadow-md border border-gray-200">
                                                @elseif($quizQuestion->question->media_type === 'video')
                                                <video controls class="max-w-md rounded-lg shadow-md border border-gray-200">
                                                    <source src="{{ asset($quizQuestion->question->media_url) }}" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                                @elseif($quizQuestion->question->media_type === 'audio')
                                                <audio controls class="w-full max-w-md">
                                                    <source src="{{ asset($quizQuestion->question->media_url) }}" type="audio/mpeg">
                                                    Your browser does not support the audio tag.
                                                </audio>
                                                @endif
                                            </div>
                                            @endif

                                            @if($quizQuestion->question->options && $quizQuestion->question->options->count() > 0)
                                            <ul class="space-y-2 mt-3">
                                                @foreach($quizQuestion->question->options as $option)
                                                <li class="flex items-center gap-3 text-base text-gray-800 mt-2">
                                                    @if($quizQuestion->question->options->count() === 1)
                                                    <span class="w-12 h-8 flex items-center justify-center border rounded {{ $option->is_correct ? 'bg-green-100 border-green-500 text-green-700 font-semibold' : 'border-gray-300' }} text-lg">
                                                        Ans
                                                    </span>
                                                    @else
                                                    <span class="w-8 h-8 flex items-center justify-center border rounded {{ $option->is_correct ? 'bg-green-100 border-green-500 text-green-700 font-semibold' : 'border-gray-300' }} text-lg">
                                                        {{ chr(65 + $loop->index) }}
                                                    </span>
                                                    @endif

                                                    <span class="text-lg ml-2">{{ $option->name }}</span>
                                                </li>
                                                @endforeach
                                            </ul>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Question Settings Badge (fixed width, centered content) -->
                                <div class="ml-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg text-base w-40 flex flex-col items-center">
                                    <div class="text-center space-y-2 w-full">
                                        <div>
                                            <div class="text-sm text-gray-600">Marks</div>
                                            <div class="font-semibold text-indigo-700 text-xl">{{ $quizQuestion->marks }}</div>
                                        </div>
                                        @if($quizQuestion->negative_marks > 0)
                                        <div>
                                            <div class="text-sm text-gray-600">Negative</div>
                                            <div class="font-semibold text-red-600 text-xl">-{{ $quizQuestion->negative_marks }}</div>
                                        </div>
                                        @endif
                                        @if($quizQuestion->is_optional)
                                        <div>
                                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded">Optional</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Edit and Delete Actions (Admin only) -->
                            @auth
                            @if(Auth::user()->isAdmin())
                                <div class="mt-3 flex gap-2 justify-end">
                                <a href="{{ route('quizzes.questions.edit', ['quiz' => $quiz->id, 'question' => $quizQuestion->question->id]) }}"
                                    class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                    Edit Question
                                </a>
                                <form action="{{ route('quizzes.questions.detach', [$quiz->id, $quizQuestion->question->id]) }}"
                                    method="POST"
                                    onsubmit="return confirm('Remove this question from the quiz?')"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1 text-sm bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
                                        Remove from Quiz
                                    </button>
                                </form>
                                <form action="{{ route('questions.destroy', $quizQuestion->question->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Permanently delete this question? This cannot be undone!')"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1 text-sm bg-red-500 text-white rounded hover:bg-red-600 transition">
                                        Delete Question
                                    </button>
                                </form>
                            </div>
                            @endif
                            @endauth
                        </div>
                        @endforeach
                    </div>
                    <div class="text-center py-8">

                        @auth
                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('quizzes.questions.select', $quiz->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Add Questions
                        </a>
                        @endif
                        @endauth
                    </div>
                </div>
                @else
                <div class="text-center py-8">
                    <p class="text-gray-600 mb-4">No questions added yet.</p>
                    @auth
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('quizzes.questions.select', $quiz->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Add Questions Now
                    </a>
                    @endif
                    @endauth
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>