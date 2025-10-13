<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $topic->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

                    <!-- Action Buttons -->
                    @if(Auth::user()->isAdmin())
                        <div class="flex gap-4 mb-6">
                            <button type="button" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                                    onclick="document.getElementById('create-subtopic-form').classList.toggle('hidden')">
                                + Create Sub-Topic
                            </button>
                            <button type="button" 
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                    onclick="document.getElementById('create-quiz-form').classList.toggle('hidden')">
                                + Create Quiz
                            </button>
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
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                        Create Sub-Topic
                                    </button>
                                    <button type="button" 
                                            class="text-sm text-gray-600 hover:text-gray-900"
                                            onclick="document.getElementById('create-subtopic-form').classList.add('hidden')">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Create Quiz Form -->
                        <div id="create-quiz-form" class="hidden mb-6 p-4 border border-gray-300 rounded-lg bg-gray-50">
                            <h4 class="text-lg font-medium mb-4">Create Quiz</h4>
                            <form method="POST" action="{{ route('quizzes.store') }}">
                                @csrf
                                <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                                
                                <div class="mb-4">
                                    <label for="quiz_name" class="block text-sm font-medium mb-2">Quiz Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="quiz_name" name="name" required 
                                           class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="mb-4">
                                    <label for="quiz_description" class="block text-sm font-medium mb-2">Description (Optional)</label>
                                    <textarea id="quiz_description" name="description" rows="3" 
                                              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="total_marks" class="block text-sm font-medium mb-2">Total Marks</label>
                                        <input type="number" id="total_marks" name="total_marks" step="0.01" 
                                               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label for="pass_marks" class="block text-sm font-medium mb-2">Pass Marks</label>
                                        <input type="number" id="pass_marks" name="pass_marks" step="0.01" 
                                               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300 text-indigo-600">
                                        <span class="ml-2 text-sm">Publish immediately</span>
                                    </label>
                                </div>
                                <div class="flex items-center gap-4">
                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
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

                    <!-- Sub-Topics Section -->
                    @php
                        $subTopics = \App\Models\Topic::where('parent_id', $topic->id)->get();
                    @endphp
                    
                    @if($subTopics->count() > 0)
                        <div class="mt-8 mb-8">
                            <h4 class="text-lg font-semibold mb-4">Sub-Topics</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($subTopics as $subTopic)
                                    <a href="{{ route('topics.show', $subTopic) }}" 
                                       class="block p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                        <h5 class="font-semibold mb-2">{{ $subTopic->name }}</h5>
                                        @if($subTopic->description)
                                            <p class="text-sm text-gray-600">{{ $subTopic->description }}</p>
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
