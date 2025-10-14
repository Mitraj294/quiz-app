<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Select Questions for: {{ $quiz->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('quizzes.questions.attach', $quiz->id) }}" id="attach-questions-form">
                    @csrf
                    
                    <!-- Global Settings for All Questions -->
                    <div class="mb-6 flex justify-end">
                        <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg" style="width: 500px;">
                            <h3 class="text-lg font-semibold mb-4 text-indigo-900">Default Settings</h3>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <label for="default_marks" class="text-sm font-medium w-36">Marks</label>
                                    <input type="number" id="default_marks" step="0.01" value="1" 
                                           class="w-36 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                           onchange="applyToAll('marks', this.value)">
                                </div>
                                
                                <!-- Negative Marks -->
                                <div class="flex items-center gap-3">
                                    <label for="default_negative_marks_enabled" class="text-sm font-medium w-36">Negative Marking</label>
                                    <div class="flex-1 flex items-center gap-2">
                                        <select id="default_negative_marks_enabled" 
                                                class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                onchange="toggleDefaultNegativeMarks(this.value)">
                                            <option value="no">No</option>
                                            <option value="yes">Yes</option>
                                        </select>
                                        <select id="default_negative_marks" 
                                                class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 hidden">
                                            <option value="0.25">1/4 (0.25)</option>
                                            <option value="0.33">1/3 (0.33)</option>
                                            <option value="0.5">1/2 (0.5)</option>
                                            <option value="1">Full (1)</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Is Optional -->
                                <div class="flex items-center gap-3">
                                    <label for="default_is_optional" class="text-sm font-medium w-36">Is Optional</label>
                                    <select id="default_is_optional" 
                                            class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                            onchange="applyToAll('is_optional', this.value)">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <button type="button" onclick="applyDefaultsToAll()" 
                                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >  Apply to All
                                </button>
                                <button type="button" onclick="selectAllQuestions()" 
                                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >   Select All
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Questions List -->
                    <div class="space-y-4">
                        @forelse($questions as $question)
                            @php
                                $isAttached = isset($attachedQuestions[$question->id]);
                                $attachedData = $isAttached ? $attachedQuestions[$question->id] : null;
                                $defaultMarks = $attachedData ? $attachedData->marks : 1;
                                $defaultNegativeMarks = $attachedData ? $attachedData->negative_marks : 0;
                                $defaultIsOptional = $attachedData ? $attachedData->is_optional : 0;
                                $hasNegativeMarks = $defaultNegativeMarks > 0;
                            @endphp
                            <div class="p-4 border rounded flex items-start gap-4 {{ $isAttached ? 'bg-green-50 border-green-300' : '' }}" id="question-{{ $question->id }}">
                                <input type="checkbox" name="question_ids[]" value="{{ $question->id }}" 
                                       id="q_{{ $question->id }}" class="mt-1 question-checkbox"
                                       {{ $isAttached ? 'checked' : '' }}>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <label for="q_{{ $question->id }}" class="font-semibold text-xl text-gray-800 cursor-pointer">
                                                {{ $question->name }}
                                                @if($isAttached)
                                                    <span class="ml-2 px-2 py-1 text-xs bg-green-600 text-white rounded">Already Added</span>
                                                @endif
                                            </label>
                                            @if($question->options && $question->options->count() > 0)
                                                <ul class="list-disc list-inside text-lg font-semibold text-gray-500 mt-2">
                                                    @foreach($question->options as $opt)
                                                        <li>{{ $opt->name }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                        
                                        <!-- Individual Question Settings (Right Side) -->
                                        <div class="ml-4 p-3 bg-gray-50 border rounded-md" style="min-width: 280px;">
                                            <h4 class="text-xs font-semibold text-gray-700 mb-2">Question Settings</h4>
                                            <div class="space-y-2">
                                                <!-- Marks -->
                                                <div>
                                                    <label for="marks_{{ $question->id }}" class="block text-xs text-gray-600 mb-1">Marks</label>
                                                    <input type="number" 
                                                           id="marks_{{ $question->id }}"
                                                           name="marks[{{ $question->id }}]" 
                                                           value="{{ $defaultMarks }}" 
                                                           step="0.01"
                                                           class="w-full text-sm rounded border-gray-300 question-marks"
                                                           data-question-id="{{ $question->id }}">
                                                </div>
                                                
                                                <!-- Negative Marks -->
                                                <div>
                                                    <label for="negative_enabled_{{ $question->id }}" class="block text-xs text-gray-600 mb-1">Negative Marks</label>
                            <select id="negative_enabled_{{ $question->id }}"
                                class="w-full text-sm rounded border-gray-300 mb-1 question-negative-enabled"
                                data-question-id="{{ $question->id }}"
                                onchange="toggleQuestionNegativeMarks(this, this.value)">
                                                        <option value="no" {{ !$hasNegativeMarks ? 'selected' : '' }}>No</option>
                                                        <option value="yes" {{ $hasNegativeMarks ? 'selected' : '' }}>Yes</option>
                                                    </select>
                                                    <select name="negative_marks[{{ $question->id }}]" 
                                                            class="w-full text-sm rounded border-gray-300 {{ $hasNegativeMarks ? '' : 'hidden' }} question-negative-marks"
                                                            data-question-id="{{ $question->id }}">
                                                        <option value="0" {{ $defaultNegativeMarks == 0 ? 'selected' : '' }}>No negative marking</option>
                                                        <option value="0.25" {{ $defaultNegativeMarks == 0.25 ? 'selected' : '' }}>1/4 (0.25)</option>
                                                        <option value="0.33" {{ $defaultNegativeMarks == 0.33 ? 'selected' : '' }}>1/3 (0.33)</option>
                                                        <option value="0.5" {{ $defaultNegativeMarks == 0.5 ? 'selected' : '' }}>1/2 (0.5)</option>
                                                        <option value="1" {{ $defaultNegativeMarks == 1 ? 'selected' : '' }}>Full (1)</option>
                                                    </select>
                                                </div>
                                                
                                                <!-- Is Optional -->
                                                <div>
                                                    <label for="is_optional_{{ $question->id }}" class="block text-xs text-gray-600 mb-1">Is Optional</label>
                                                    <select id="is_optional_{{ $question->id }}"
                                                            name="is_optional[{{ $question->id }}]" 
                                                            class="w-full text-sm rounded border-gray-300 question-optional"
                                                            data-question-id="{{ $question->id }}">
                                                        <option value="0" {{ $defaultIsOptional == 0 ? 'selected' : '' }}>No</option>
                                                        <option value="1" {{ $defaultIsOptional == 1 ? 'selected' : '' }}>Yes</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-600">No available questions found in the topics attached to this quiz.</p>
                        @endforelse
                   </div>
                    <div class="mt-6 flex gap-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Attach Selected
                        </button>
                        <a href="{{ route('quizzes.show', $quiz->id) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded">Cancel</a>
                   </div>
                 </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle default negative marks dropdown
        function toggleDefaultNegativeMarks(value) {
            const dropdown = document.getElementById('default_negative_marks');
            if (value === 'yes') {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }

        // Toggle individual question negative marks
        // Accept either the element (from onchange: this) or a questionId (when called programmatically)
        function toggleQuestionNegativeMarks(elOrId, value) {
            const questionId = (typeof elOrId === 'object' && elOrId !== null) ? elOrId.dataset.questionId : elOrId;
            const dropdown = document.querySelector(`.question-negative-marks[data-question-id="${questionId}"]`);
            if (!dropdown) return;

            if (value === 'yes') {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
                dropdown.value = '0';
            }
        }

        // Apply default settings to all questions
        function applyDefaultsToAll() {
            const defaultMarks = document.getElementById('default_marks').value;
            const defaultNegativeEnabled = document.getElementById('default_negative_marks_enabled').value;
            const defaultNegativeMarks = document.getElementById('default_negative_marks').value;
            const defaultOptional = document.getElementById('default_is_optional').value;

            // Apply marks
            document.querySelectorAll('.question-marks').forEach(input => {
                input.value = defaultMarks;
            });

            // Apply negative marks
            document.querySelectorAll('.question-negative-enabled').forEach(select => {
                select.value = defaultNegativeEnabled;
                const questionId = select.dataset.questionId;
                toggleQuestionNegativeMarks(questionId, defaultNegativeEnabled);
                
                if (defaultNegativeEnabled === 'yes') {
                    const negativeMarksDropdown = document.querySelector(`.question-negative-marks[data-question-id="${questionId}"]`);
                    negativeMarksDropdown.value = defaultNegativeMarks;
                }
            });

            // Apply optional
            document.querySelectorAll('.question-optional').forEach(select => {
                select.value = defaultOptional;
            });

            alert('Default settings applied to all questions!');
        }

        // Apply specific setting to all questions (for individual default field changes)
        function applyToAll(field, value) {
            if (field === 'marks') {
                document.querySelectorAll('.question-marks').forEach(input => {
                    input.value = value;
                });
            } else if (field === 'is_optional') {
                document.querySelectorAll('.question-optional').forEach(select => {
                    select.value = value;
                });
            }
        }

        // Select or deselect all questions
        function selectAllQuestions() {
            const checkboxes = document.querySelectorAll('.question-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
            });
        }
    </script>
</x-app-layout>
