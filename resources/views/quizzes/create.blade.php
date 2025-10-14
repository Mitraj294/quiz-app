<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create New Quiz
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
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
                    <h3 class="text-2xl font-bold mb-6">Let's Create a Quiz!</h3>

                    <form method="POST" action="{{ route('quizzes.store') }}" id="quiz-form">
                        @csrf

                        <!-- Step 1: Select or Create Topic -->
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold mb-4">Step 1: Choose a Topic</h4>
                            
                            <!-- Topic Selection Options -->
                            <div class="space-y-4">
                                <!-- Option 1: Select Existing Topic -->
                                <label class="flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                    <input type="radio" name="topic_option" value="existing" checked 
                                           class="mt-1 mr-3" 
                                           onchange="toggleTopicInputs('existing')">
                                    <div class="flex-1">
                                        <span class="font-medium text-gray-900">Select Existing Topic</span>
                                        <div id="existing-topic-select" class="mt-3">
                                            <select name="topic_id" id="topic_id" 
                                                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">-- Choose a Topic --</option>
                                                @foreach($topics as $topic)
                                                    <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </label>

                                <!-- Option 2: Create New Topic -->
                                <label class="flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                    <input type="radio" name="topic_option" value="new" 
                                           class="mt-1 mr-3"
                                           onchange="toggleTopicInputs('new')">
                                    <div class="flex-1">
                                        <span class="font-medium text-gray-900">Create New Topic</span>
                                        <div id="new-topic-inputs" class="mt-3 space-y-3 hidden">
                                            <div>
                                                <input type="text" name="new_topic_name" id="new_topic_name" 
                                                       placeholder="Enter new topic name"
                                                       class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            </div>
                                            <div>
                                                <textarea name="new_topic_description" id="new_topic_description" 
                                                          rows="2" placeholder="Topic description (optional)"
                                                          class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <hr class="my-8">

                        <!-- Step 2: Quiz Details -->
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold mb-4">Step 2: Quiz Details</h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="quiz_name" class="block text-sm font-medium mb-2">
                                        Quiz Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="quiz_name" name="name" required 
                                           value="{{ old('name') }}"
                                           class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                           placeholder="e.g., Basic Programming Quiz">
                                </div>

                                <div>
                                    <label for="quiz_description" class="block text-sm font-medium mb-2">
                                        Description
                                    </label>
                                    <textarea id="quiz_description" name="description" rows="3" 
                                              class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                              placeholder="Describe what this quiz covers...">{{ old('description') }}</textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="total_marks" class="block text-sm font-medium mb-2">
                                            Total Marks
                                        </label>
                                        <input type="number" id="total_marks" name="total_marks" step="0.01" 
                                               value="{{ old('total_marks', 100) }}"
                                               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label for="pass_marks" class="block text-sm font-medium mb-2">
                                            Pass Marks
                                        </label>
                                        <input type="number" id="pass_marks" name="pass_marks" step="0.01" 
                                               value="{{ old('pass_marks', 40) }}"
                                               class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label for="max_attempts" class="block text-sm font-medium mb-2">Max Attempts</label>
                                        <input type="number" id="max_attempts" name="max_attempts" value="{{ old('max_attempts', 0) }}" class="w-full rounded-md border-gray-300">
                                    </div>
                                    <div>
                                        <label for="time_between_attempts" class="block text-sm font-medium mb-2">Time Between Attempts (seconds)</label>
                                        <input type="number" id="time_between_attempts" name="time_between_attempts" value="{{ old('time_between_attempts', 0) }}" class="w-full rounded-md border-gray-300">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label for="duration" class="block text-sm font-medium mb-2">Duration (seconds)</label>
                                        <input type="number" id="duration" name="duration" value="{{ old('duration', 0) }}" class="w-full rounded-md border-gray-300">
                                    </div>
                                    <div>
                                        <label for="is_published" class="block text-sm font-medium mb-2">Publish</label>
                                        <select name="is_published" id="is_published" class="w-full rounded-md border-gray-300">
                                            <option value="0" {{ old('is_published') == 0 ? 'selected' : '' }}>Draft</option>
                                            <option value="1" {{ old('is_published') == 1 ? 'selected' : '' }}>Published</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label for="valid_from" class="block text-sm font-medium mb-2">Valid From</label>
                                        <input type="datetime-local" name="valid_from" id="valid_from" value="{{ old('valid_from') }}" class="w-full rounded-md border-gray-300">
                                    </div>
                                    <div>
                                        <label for="valid_upto" class="block text-sm font-medium mb-2">Valid Upto</label>
                                        <input type="datetime-local" name="valid_upto" id="valid_upto" value="{{ old('valid_upto') }}" class="w-full rounded-md border-gray-300">
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label for="negative_marking_settings" class="block text-sm font-medium mb-2">Negative Marking Settings (JSON)</label>
                                    <textarea name="negative_marking_settings" id="negative_marking_settings" rows="3" class="w-full rounded-md border-gray-300">{{ old('negative_marking_settings') }}</textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label for="media_url" class="block text-sm font-medium mb-2">Media URL</label>
                                        <input type="text" name="media_url" id="media_url" value="{{ old('media_url') }}" class="w-full rounded-md border-gray-300">
                                    </div>
                                    <div>
                                        <label for="media_type" class="block text-sm font-medium mb-2">Media Type</label>
                                        <input type="text" name="media_type" id="media_type" value="{{ old('media_type') }}" class="w-full rounded-md border-gray-300" placeholder="image,video,audio">
                                    </div>
                                </div>

                             
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-4 pt-4">
                            <button type="submit" 
                                   class="px-6 py-3 text-gray-700 hover:text-gray-900 transition">
                                Create Quiz
                            </button>
                            <a href="{{ route('topics.index') }}" 
                               class="px-6 py-3 text-gray-700 hover:text-gray-900 transition">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleTopicInputs(option) {
            const existingSelect = document.getElementById('existing-topic-select');
            const newInputs = document.getElementById('new-topic-inputs');
            const topicIdSelect = document.getElementById('topic_id');
            const newTopicName = document.getElementById('new_topic_name');

            if (option === 'existing') {
                existingSelect.classList.remove('hidden');
                newInputs.classList.add('hidden');
                topicIdSelect.required = true;
                newTopicName.required = false;
            } else {
                existingSelect.classList.add('hidden');
                newInputs.classList.remove('hidden');
                topicIdSelect.required = false;
                newTopicName.required = true;
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleTopicInputs('existing');
        });
    </script>
</x-app-layout>
