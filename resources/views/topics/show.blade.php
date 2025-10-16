<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $topic->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-ful mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if(session('success'))
            <div id="success-message" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
            <script>
                (function() {
                    // Hide the success message after 5 seconds
                    const el = document.getElementById('success-message');
                    if (!el) return;
                    setTimeout(() => {
                        try {
                            el.style.transition = 'opacity 0.4s ease';
                            el.style.opacity = '0';
                            setTimeout(() => el.remove(), 400);
                        } catch (e) {
                            el.remove();
                        }
                    }, 5000);
                })();
            </script>
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
                    <div class="mb-6">
                        <a href="{{ route('topics.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                            ← Back to Topics
                        </a>
                    </div>

                    <h3 class="text-2xl font-bold mb-4">{{ $topic->name }}</h3>

                    @if($topic->description)
                    <div class="mb-6">
                        <p class="text-gray-800">{{ $topic->description }}</p>
                    </div>
                    @endif

                    <!-- Admin Action Buttons -->
                    @auth
                    @if(Auth::user()->isAdmin())
                    <div class="flex justify-between gap-4 mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="flex gap-4">
                            <button type="button"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                onclick="document.getElementById('create-subtopic-form').classList.toggle('hidden')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Create Sub-Topic
                            </button>
                            <button type="button"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                onclick="document.getElementById('create-quiz-form').classList.toggle('hidden')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Create Quiz
                            </button>
                            <a href="{{ route('topics.questions.create', $topic->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Questions
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('topics.edit', $topic->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                Edit Topic
                            </a>
                            <form method="POST" action="{{ route('topics.destroy', $topic->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this topic? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Create Sub-Topic Form -->
                    <div id="create-subtopic-form" class="hidden mb-6 p-4 border border-gray-300 rounded-lg bg-gray-50">
                        <h4 class="text-lg font-medium mb-4">Create Sub-Topic</h4>
                        <form method="POST" action="{{ route('topics.store') }}">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $topic->id }}">

                            <div class="mb-4">
                                <label for="subtopic_name" class="block text-sm font-medium mb-2">Sub-Topic Name <span class="text-red-500">*</span></label>
                                <input type="text" id="subtopic_name" name="name" required
                                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="mb-4">
                                <label for="subtopic_description" class="block text-sm font-medium mb-2">Description (Optional)</label>
                                <textarea id="subtopic_description" name="description" rows="2"
                                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                            <div class="flex items-center gap-4">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Create Sub-Topic
                                </button>
                                <button type="button"
                                    class="text-sm text-gray-700 hover:text-gray-900"
                                    onclick="document.getElementById('create-subtopic-form').classList.add('hidden')">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Create Quiz Form -->
                    <div id="create-quiz-form" class="hidden mb-6 p-4 border border-gray-300 rounded-lg bg-gray-50">
                        <h4 class="text-lg font-medium mb-4">Create Quiz for {{ $topic->name }}</h4>
                        <form method="POST" action="{{ route('quizzes.store') }}">
                            @csrf
                            <!-- Pre-select this topic -->
                            <input type="hidden" name="topic_option" value="existing">
                            <input type="hidden" name="topic_id" value="{{ $topic->id }}">

                            <div class="mb-4">
                                <label for="quiz_name" class="block text-sm font-medium mb-2">Quiz Name <span class="text-red-500">*</span></label>
                                <input type="text" id="quiz_name" name="name" required
                                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="e.g., {{ $topic->name }} - Basic Quiz">
                            </div>
                            <div class="mb-4">
                                <label for="quiz_description" class="block text-sm font-medium mb-2">Description (Optional)</label>
                                <textarea id="quiz_description" name="description" rows="3"
                                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Describe what this quiz covers..."></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="total_marks" class="block text-sm font-medium mb-2">Total Marks</label>
                                    <input type="number" id="total_marks" name="total_marks" step="0.01" value="100"
                                        class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="pass_marks" class="block text-sm font-medium mb-2">Pass Marks</label>
                                    <input type="number" id="pass_marks" name="pass_marks" step="0.01" value="40"
                                        class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Create Quiz
                                </button>
                                <button type="button"
                                    class="text-sm text-gray-600 hover:text-gray-900"
                                    onclick="document.getElementById('create-quiz-form').classList.add('hidden')">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                    @endauth

                    <!-- Sub-Topics Section -->
                    @if($topic->children && $topic->children->count() > 0)
                    <div class="mt-8 mb-8">
                        <h4 class="text-lg font-semibold mb-4">Sub-Topics</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($topic->children as $subTopic)
                            <a href="{{ route('topics.show', $subTopic->id) }}"
                                class="block p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                <h5 class="font-semibold mb-2">{{ $subTopic->name }}</h5>
                                @if($subTopic->description)
                                <p class="text-sm text-gray-700">{{ $subTopic->description }}</p>
                                @endif
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="mt-8">
                        <h4 class="text-lg font-semibold mb-4">Related Quizzes</h4>
                        @if(isset($topic->quizzes) && $topic->quizzes->count() > 0)
                        <div class="space-y-4">
                            @foreach($topic->quizzes as $quiz)
                            <div class="border border-gray-300 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h5 class="font-semibold mb-2">{{ $quiz->name }}</h5>
                                        @if($quiz->description)
                                        <p class="text-sm text-gray-700 mb-2">{{ $quiz->description }}</p>
                                        @endif
                                        <div class="flex items-center gap-4 text-xs text-gray-500 mb-2">
                                            <span> Total: {{ $quiz->total_marks }} marks</span>
                                            <span>Pass: {{ $quiz->pass_marks }} marks</span>
                                            @if($quiz->duration > 0)
                                            <span> {{ $quiz->duration }} min</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if(!$quiz->is_published)
                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Draft</span>
                                    @endif
                                </div>
                                <div class="flex gap-3 mt-3">

                                    @auth
                                    @if(Auth::user()->isAdmin())
                                    <a href="{{ route('quizzes.show', $quiz->id) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                                        View Details
                                    </a>
                                    @else
                                    @if($quiz->is_published && $quiz->questions->count() > 0)
                                    <a href="{{ route('quizzes.show', $quiz->id) }}" class="text-sm text-blue-600 hover:text--900 font-medium">
                                    Quiz Details
                                    </a>
                                    @else
                                    <span class="text-sm text-gray-500">Not available</span>
                                    @endif
                                    @endif
                                    @endauth
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-gray-600">No quizzes available for this topic yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>