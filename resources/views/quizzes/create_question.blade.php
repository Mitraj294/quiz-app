<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Question to {{ $quiz->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('quizzes.questions.store', $quiz->id) }}">
                    @csrf

                    <div class="mb-4">
                        <label for="question_type" class="block text-sm font-medium mb-2">Question Type</label>
                        <select name="question_type" id="question_type" onchange="onTypeChange()" class="w-full rounded-md border-gray-300">
                            @foreach($questionTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="question_text" class="block text-sm font-medium mb-2">Question Text</label>
                        <textarea name="question_text" id="question_text" rows="3" class="w-full rounded-md border-gray-300"></textarea>
                    </div>

                    <div id="mcq-options" class="mb-4">
                        <label for="options-list" class="block text-sm font-medium mb-2">Options</label>
                        <div id="options-list" class="space-y-2">
                            <!-- Option rows will be inserted here -->
                        </div>
                        <div class="mt-2">
                            <button type="button" onclick="addOptionField()" class="px-3 py-1 bg-gray-100 rounded">+ Add option</button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Mark the correct option(s) using the checkbox. For Single Answer type only one may be selected.</p>
                    </div>

                    <div id="text-answer" class="mb-4" style="display:none;">
                        <label for="text_answer_input" class="block text-sm font-medium mb-2">Answer (for Text / Short Answer)</label>
                        <input type="text" name="text_answer" id="text_answer_input" class="w-full rounded-md border-gray-300">
                    </div>

                    <div class="mb-4 grid grid-cols-3 gap-3">
                        <div>
                            <label for="marks" class="block text-sm font-medium mb-2">Marks</label>
                            <input type="number" name="marks" id="marks" step="0.01" value="1" class="w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="negative_marks" class="block text-sm font-medium mb-2">Negative Marks</label>
                            <select name="negative_marks" id="negative_marks" class="w-full rounded-md border-gray-300">
                                <option value="0">No negative marking</option>
                                <option value="0.25">1/4 (0.25)</option>
                                <option value="0.33">1/3 (0.33)</option>
                                <option value="0.5">1/2 (0.5)</option>
                                <option value="1">Full (1)</option>
                            </select>
                        </div>
                        <div>
                            <label for="is_optional" class="block text-sm font-medium mb-2">Is Optional</label>
                            <select name="is_optional" id="is_optional" class="w-full rounded-md border-gray-300">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                       Create & Attach</button>
                        <a href="{{ route('quizzes.show', $quiz->id) }}" class="px-4 py-2 text-gray-700">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function onTypeChange() {
            const tp = document.getElementById('question_type').value;
            const mcq = document.getElementById('mcq-options');
            const text = document.getElementById('text-answer');
            if (tp == '1' || tp == '2') {
                mcq.style.display = 'block';
                text.style.display = 'none';
            } else {
                mcq.style.display = 'none';
                text.style.display = 'block';
            }
            enforceCheckboxMode(tp);
        }

        function addOptionField(value = '') {
            const list = document.getElementById('options-list');
            const idx = list.children.length;
            const div = document.createElement('div');
            div.className = 'flex items-center gap-2';
            div.innerHTML = `
                <input type="checkbox" name="correct[]" value="${idx}" class="correct-checkbox">
                <input type="text" name="options[]" class="w-full rounded-md border-gray-300" placeholder="Option ${idx+1}" value="${value}">
            `;
            list.appendChild(div);
            enforceCheckboxMode(document.getElementById('question_type').value);
        }

        function enforceCheckboxMode(tp) {
            const checkboxes = document.querySelectorAll('.correct-checkbox');
            checkboxes.forEach(cb => {
                const clone = cb.cloneNode(true);
                cb.parentNode.replaceChild(clone, cb);
            });
            if (tp == '1') {
                document.querySelectorAll('.correct-checkbox').forEach(cb => {
                    cb.addEventListener('change', function() {
                        if (this.checked) {
                            document.querySelectorAll('.correct-checkbox').forEach(other => { if (other !== this) other.checked = false; });
                        }
                    });
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const list = document.getElementById('options-list');
            if (list.children.length === 0) {
                addOptionField(); addOptionField(); addOptionField(); addOptionField();
            }
            onTypeChange();
        });
    </script>
</x-app-layout>
