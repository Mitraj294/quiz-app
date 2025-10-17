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

                @if($quiz->authors && $quiz->authors->count() > 0)
                <div class="mb-4">
                    <h4 class="text-sm font-semibold mb-2">Authors</h4>
                    <ul class="list-disc list-inside text-sm text-gray-700">
                        @foreach($quiz->authors as $author)
                            <li>{{ $author->name }} @if($author->pivot->author_role) <small class="text-gray-500">({{ $author->pivot->author_role }})</small> @endif</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="grid grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                    @php
                    $totalQuestions = $quiz->questions->count();
                    $mandatoryCount = $quiz->questions->where('is_optional', false)->count();
                    $optionalCount = $quiz->questions->where('is_optional', true)->count();
                    $userAttempts = auth()->check() ? $quiz->attemptsCountForUser(auth()->id()) : 0;
                    @endphp

                    <!-- Row 1: Questions -->
                    <div>
                        <span class="text-sm text-gray-600">Total Questions</span>
                        <p class="text-lg font-semibold">{{ $totalQuestions }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Mandatory</span>
                        <p class="text-lg font-semibold">{{ $mandatoryCount }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Optional</span>
                        <p class="text-lg font-semibold">{{ $optionalCount }}</p>
                    </div>

                    <!-- Row 2: Marks (two cells) + empty -->
                    <div>
                        <span class="text-sm text-gray-600">Total Marks</span>
                        <p class="text-lg font-semibold">{{ $quiz->total_marks }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Pass Marks</span>
                        <p class="text-lg font-semibold">{{ $quiz->pass_marks }}</p>
                    </div>
                    <div>
                        <!-- intentionally left empty for spacing -->
                    </div>

                    <!-- Row 3: Attempts and Status -->
                    <div>
                        <span class="text-sm text-gray-600">Max Attempts</span>
                        <p class="text-lg font-semibold">{{ $quiz->max_attempts ?: 'Unlimited' }}</p>
                    </div>

                    @auth
                    @if(!Auth::user()->isAdmin())
                    <div>
                        <span class="text-sm text-gray-600">Your Attempts</span>
                        <p class="text-lg font-semibold">{{ $userAttempts }}</p>
                    </div>
                    @else
                    <div><!-- intentionally left empty for spacing --></div>
                    @endif
                    @else
                    <div><!-- intentionally left empty for spacing --></div>
                    @endauth

                    <div>
                        <span class="text-sm text-gray-600">Status</span>
                        <p class="text-lg font-semibold">
                            @auth
                            @if(Auth::user()->isAdmin())
                            @if($quiz->is_published)
                            <span class="text-green-600">Published</span>
                            @else
                            <span class="text-yellow-600">Draft</span>
                            @endif
                            @else
                            @if($userAttempts > 0)
                            <span class="text-green-600">Submitted</span>
                            @else
                            <span class="text-yellow-600">Yet To Attempt</span>
                            @endif
                            @endif
                            @else
                            @if($quiz->is_published)
                            <span class="text-green-600">Published</span>
                            @else
                            <span class="text-yellow-600">Draft</span>
                            @endif
                            @endauth
                        </p>
                    </div>
                </div>

                @auth
                @if(Auth::user()->isAdmin())
                <div class="flex gap-4 justify-between">
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
                    <div class="flex gap-4">
                        <a href="{{ route('quizzes.edit', $quiz->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                            Edit Quiz
                        </a>

                        <form action="{{ route('quizzes.publish', $quiz->id) }}" method="POST" class="inline mr-2">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 {{ $quiz->is_published ? 'bg-yellow-500 hover:bg-yellow-600 focus:bg-yellow-600 active:bg-yellow-700 focus:ring-yellow-500' : 'bg-green-600 hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:ring-green-500' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ $quiz->is_published ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>

                        <form action="{{ route('quizzes.destroy', $quiz->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete this quiz? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition ease-in-out duration-150">
                                Delete Quiz
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <!-- Regular user: show Start Quiz button -->
                @if($quiz->questions->count() > 0 && $quiz->is_published)
                @php
                $attempts = $quiz->attemptsCountForUser(auth()->id());
                @endphp

                @if($attempts === 0)
                <div class="flex gap-4">
                    <a href="{{ route('quizzes.attempt', $quiz->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Start Quiz
                    </a>
                </div>
                @else
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1 text-left">
                        @if($quiz->max_attempts && $attempts >= $quiz->max_attempts)
                            <span class="text-sm font-semibold text-green-600">You've completed {{ $quiz->name }} quiz successfully. Thank you for participating.</span>
                        @else
                            <span class="text-sm text-gray-700">You have attempted this quiz {{ $attempts }} {{ $attempts === 1 ? 'time' : 'times' }}.</span>
                        @endif
                    </div>
                    @if($attempts > 0)
                        <div class="flex-none">
                            <a href="{{ route('quizzes.result_index', $quiz->id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 focus:outline-none border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150">
                                See Result
                            </a>
                        </div>
                    @endif

                    <div class="flex-none">
                        @if(!($quiz->max_attempts && $attempts >= $quiz->max_attempts))
                            <a href="{{ route('quizzes.attempt', $quiz->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Retake Quiz (Attempt {{ $attempts + 1 }})
                            </a>
                        @endif
                    </div>
                </div>
                @endif
                @endif
                @endif
                @endauth
            </div>

            @auth
            @if(Auth::user()->isAdmin())
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
                                                    <!-- Placeholder track file: replace with real captions (.vtt) if available -->
                                                    <track kind="captions" srclang="en" label="English captions" src="{{ asset('media/captions/placeholder.vtt') }}">
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
                                    <div class="text-center w-full">
                                        <div class="flex flex-row items-center justify-center gap-4">
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
                                        </div>
                                        @if($quizQuestion->is_optional)
                                        <div>
                                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded">Optional</span>
                                        </div>
                                        @endif
                                        @if($quizQuestion->question->question_type->id == 3)
                                        <div>
                                            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">
                                                Can answer multiple options.
                                            </span>
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
                    <div class="text-left py-8">

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
            @endif
            @endauth
        </div>
    </div>
</x-app-layout>