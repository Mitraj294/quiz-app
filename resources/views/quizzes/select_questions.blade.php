<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Select Questions for: {{ $quiz->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('quizzes.questions.attach', $quiz->id) }}" id="attach-questions-form" onsubmit="console.log('Form is submitting to:', this.action);">
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
                         onchange="applyToAll('marks', this.value); updateDefaultNegativeOptions();">
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
                                            <option value="0">No negative marking</option>
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
                                            
                                            @if($question->media_url)
                                                <div class="my-4 mb-2 mt-2">
                                                    @if($question->media_type === 'image')
                                                        <img src="{{ asset($question->media_url) }}" alt="Question Media" class="max-w-md rounded-lg shadow-md border border-gray-200">
                                                    @elseif($question->media_type === 'video')
                                                        <video controls class="max-w-md rounded-lg shadow-md border border-gray-200">
                                                            <source src="{{ asset($question->media_url) }}" type="video/mp4">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    @elseif($question->media_type === 'audio')
                                                        <audio controls class="w-full max-w-md">
                                                            <source src="{{ asset($question->media_url) }}" type="audio/mpeg">
                                                            Your browser does not support the audio tag.
                                                        </audio>
                                                    @endif
                                                </div>
                                            @endif
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
                                                    <select id="negative_marks_{{ $question->id }}"
                                                            name="negative_marks[{{ $question->id }}]" 
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
                                    
                                    <!-- Edit and Delete Actions -->
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ route('questions.edit', $question->id) }}" 
                                           class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                            Edit
                                        </a>
                                        <button type="button" 
                                                class="delete-question-btn px-3 py-1 text-sm bg-red-500 text-white rounded hover:bg-red-600 transition"
                                                data-question-id="{{ $question->id }}">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-600">No available questions found in the topics attached to this quiz.</p>
                        @endforelse
                   </div>
                    <div class="mt-6 flex gap-4">
                        <button type="submit" onclick="return validateForm()" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Attach Selected
                        </button>
                        <a href="{{ route('quizzes.show', $quiz->id) }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded">Cancel</a>
                   </div>
                 </form>
            </div>
        </div>
    </div>

    <script>
        // Delegate negative-mark helpers to the shared NegativeMarks module
        function updateNegativeOptionsForQuestion(questionId) {
            const marksInput = document.getElementById(`marks_${questionId}`);
            const negativeSelect = document.getElementById(`negative_marks_${questionId}`);
            if (!marksInput || !negativeSelect) return;
            const marks = parseFloat(marksInput.value) || 1;
            const currentValue = negativeSelect.value;
            if (window.NegativeMarks) {
                window.NegativeMarks.updateNegativeOptionsForSelect(negativeSelect, marks, currentValue);
            }
        }

        function toggleDefaultNegativeMarks(value) {
            const dropdown = document.getElementById('default_negative_marks');
            const toggle = document.getElementById('default_negative_marks_enabled');
            if (window.NegativeMarks) {
                window.NegativeMarks.toggleNegativeSelectVisibility(toggle, dropdown);
            } else if (dropdown) {
                if (value === 'yes') dropdown.classList.remove('hidden'); else dropdown.classList.add('hidden');
            }
        }

        function updateDefaultNegativeOptions() {
            const marksInput = document.getElementById('default_marks');
            const dropdown = document.getElementById('default_negative_marks');
            if (!marksInput || !dropdown) return;
            const marks = parseFloat(marksInput.value) || 1;
            const current = dropdown.value;
            if (window.NegativeMarks) {
                window.NegativeMarks.updateNegativeOptionsForSelect(dropdown, marks, current);
            } else {
                // fallback behaviour (should not be needed when bundled)
                dropdown.innerHTML = '';
                const options = [
                    { value: 0, label: 'No negative marking' },
                    { value: +(marks / 4).toFixed(2), label: `1/4 (${(marks / 4).toFixed(2)})` },
                    { value: +(marks / 3).toFixed(2), label: `1/3 (${(marks / 3).toFixed(2)})` },
                    { value: +(marks / 2).toFixed(2), label: `1/2 (${(marks / 2).toFixed(2)})` },
                    { value: +marks.toFixed(2), label: `Full (${marks.toFixed(2)})` },
                ];
                options.forEach(opt => {
                    const o = document.createElement('option');
                    o.value = String(opt.value);
                    o.textContent = opt.label;
                    dropdown.appendChild(o);
                });
                if (current) dropdown.value = current;
            }
        }

        function toggleQuestionNegativeMarks(elOrId, value) {
            let toggleEl;
            if (typeof elOrId === 'object' && elOrId !== null) toggleEl = elOrId;
            else toggleEl = document.getElementById(`negative_enabled_${elOrId}`);
            if (!toggleEl) return;
            const questionId = toggleEl.dataset ? toggleEl.dataset.questionId : elOrId;
            const dropdown = document.querySelector(`.question-negative-marks[data-question-id="${questionId}"]`);
            if (window.NegativeMarks) {
                window.NegativeMarks.toggleNegativeSelectVisibility(toggleEl, dropdown);
                if (toggleEl.value === 'yes' && dropdown) {
                    const marksInput = document.getElementById(`marks_${questionId}`);
                    const marks = marksInput ? marksInput.value : 1;
                    window.NegativeMarks.updateNegativeOptionsForSelect(dropdown, marks, dropdown.value);
                } else if (dropdown) {
                    dropdown.value = '0';
                }
            } else {
                if (!dropdown) return;
                if (value === 'yes') dropdown.classList.remove('hidden'); else { dropdown.classList.add('hidden'); dropdown.value = '0'; }
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
                    if (negativeMarksDropdown) {
                        // Rebuild options according to the (now-updated) marks value and set selected value
                        if (window.NegativeMarks) {
                            window.NegativeMarks.updateNegativeOptionsForSelect(negativeMarksDropdown, defaultMarks, defaultNegativeMarks);
                            // ensure visibility is correct
                            const toggleEl = select;
                            window.NegativeMarks.toggleNegativeSelectVisibility(toggleEl, negativeMarksDropdown);
                        } else {
                            // fallback: try to set the value (options might not match)
                            negativeMarksDropdown.value = defaultNegativeMarks;
                        }
                    }
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
                    // Also update default negative options when marks change
                    updateDefaultNegativeOptions();
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

        // Validate form before submission
        function validateForm() {
            console.log('=== VALIDATE FORM CALLED ===');
            const checkedBoxes = document.querySelectorAll('.question-checkbox:checked');
            console.log('Checked boxes count:', checkedBoxes.length);
            
            if (checkedBoxes.length === 0) {
                console.log('No questions selected!');
                alert('Please select at least one question to attach!');
                return false;
            }
            
            // Log form data being submitted
            const formData = new FormData(document.getElementById('attach-questions-form'));
            console.log('Form data being submitted:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ':', value);
            }
            
            console.log('Validation passed, submitting form...');
            return true;
        }

        // Initialize dynamic negative marks on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Handle delete button clicks with event delegation
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('delete-question-btn') || e.target.closest('.delete-question-btn')) {
                    const btn = e.target.classList.contains('delete-question-btn') ? e.target : e.target.closest('.delete-question-btn');
                    const questionId = btn.dataset.questionId;
                    
                    console.log('Delete question called for ID:', questionId);
                    
                    if (!confirm('Permanently delete this question? This cannot be undone!')) {
                        console.log('Delete cancelled by user');
                        return;
                    }
                    
                    // Create a temporary form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/questions/${questionId}`;
                    
                    // Add CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                                   || document.querySelector('input[name="_token"]')?.value;
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                    
                    // Add DELETE method
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    
                    // Append to body and submit
                    document.body.appendChild(form);
                    console.log('Submitting delete form for question:', questionId);
                    form.submit();
                }
            });
            // Initialize negative marks using shared module (if available)
            if (window.NegativeMarks && typeof window.NegativeMarks.initNegativeMarksForPage === 'function') {
                window.NegativeMarks.initNegativeMarksForPage();
            } else {
                // Fallback to inline initialization
                updateDefaultNegativeOptions();
                document.querySelectorAll('.question-marks').forEach(input => {
                    const questionId = input.dataset.questionId;
                    updateNegativeOptionsForQuestion(questionId);
                    input.addEventListener('input', function() {
                        updateNegativeOptionsForQuestion(questionId);
                    });
                });
            }
        });
    </script>
</x-app-layout>
