<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Question to {{ $topic->name }}</h2>
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

                <form method="POST" action="{{ route('topics.questions.store', $topic) }}">
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
                    <!-- Toggle Button -->
                    <div class="mb-4">
                        <button type="button" id="toggle-media-btn" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-black
                         uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            + Add Media
                        </button>
                    </div>

                    <!-- Media Upload Section (hidden by default) -->
                    <div id="media-section" class="mb-4 hidden">
                        <label class="block text-sm font-medium mb-2">Add Media To Question</label>
                        <div id="media-dropzone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-gray-400 transition-colors">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">
                                <button type="button" onclick="document.getElementById('media-input').click()" class="text-indigo-600 hover:text-indigo-500">+ Add Media</button>
                                or drag and drop
                            </p>
                            <p class="text-xs text-gray-500">Image, Audio, or Video (Max 10MB)</p>
                        </div>
                        <input type="file" id="media-input" name="media" accept="image/*,audio/*,video/*" class="hidden">
                        <input type="hidden" name="media_url" id="media_url">
                        <input type="hidden" name="media_type" id="media_type">
                        
                        <!-- Media Preview -->
                        <div id="media-preview" class="mt-3 hidden">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div id="preview-content"></div>
                                    <div>
                                        <p id="media-filename" class="text-sm font-medium text-gray-700"></p>
                                        <p id="media-size" class="text-xs text-gray-500"></p>
                                    </div>
                                </div>
                                <button type="button" onclick="removeMedia()" class="text-red-500 hover:text-red-700">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div id="upload-progress" class="mt-2 hidden">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div id="progress-bar" class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <div id="mcq-options" class="mb-4">
                        <label for="options-list" class="block text-sm font-medium mb-2">Options</label>
                        <div id="options-list" class="space-y-2">
                            <!-- Option rows will be inserted here -->
                        </div>
                        <div class="mt-2">
                            <button type="button" onclick="addOptionField()" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">+ Add option</button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Mark the correct option(s) using the checkbox. For Single Answer type only one may be selected.</p>
                    </div>

                    <div id="text-answer" class="mb-4" style="display:none;">
                        <label for="text_answer_input" class="block text-sm font-medium mb-2">Answer (for Text / Short Answer)</label>
                        <input type="text" name="text_answer" id="text_answer_input" class="w-full rounded-md border-gray-300">
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Add Question</button>
                        <a href="{{ route('topics.show', $topic->id) }}" class="px-4 py-2 text-gray-700">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle media upload section
        document.addEventListener('DOMContentLoaded', function() {
            const mediaBtn = document.getElementById('toggle-media-btn');
            const mediaSection = document.getElementById('media-section');
            
            if (mediaBtn && mediaSection) {
                mediaBtn.addEventListener('click', function() {
                    if (mediaSection.classList.contains('hidden')) {
                        mediaSection.classList.remove('hidden');
                        mediaBtn.textContent = '- Hide Media';
                        mediaBtn.classList.remove('bg-indigo-600');
                        mediaBtn.classList.add('bg-gray-600');
                    } else {
                        mediaSection.classList.add('hidden');
                        mediaBtn.textContent = '+ Add Media';
                        mediaBtn.classList.remove('bg-gray-600');
                        mediaBtn.classList.add('bg-indigo-600');
                    }
                });
            }
        });

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
            div.className = 'flex items-center gap-2 mt-2';
            div.innerHTML = `
                <span class="w-6 text-sm font-medium">${labelForIndex(idx)}</span>
                <input type="checkbox" name="correct[]" value="${idx}" class="correct-checkbox">
                <input type="text" name="options[]" class="w-full rounded-md border-gray-300" placeholder="Option ${idx+1}" value="${escapeHtml(value)}">
                <button type="button" class="ml-2 text-red-500 hover:text-red-700 remove-option" onclick="removeOptionField(this)" title="Remove option">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            `;
            list.appendChild(div);
            refreshOptionIndices();
            enforceCheckboxMode(document.getElementById('question_type').value);
        }

        function removeOptionField(btn) {
            const row = btn.closest('div');
            if (!row) return;
            row.remove();
            refreshOptionIndices();
        }

        function refreshOptionIndices() {
            const list = document.getElementById('options-list');
            Array.from(list.children).forEach((row, i) => {
                const labelSpan = row.querySelector('span');
                if (labelSpan) labelSpan.textContent = labelForIndex(i);
                const checkbox = row.querySelector('.correct-checkbox');
                if (checkbox) checkbox.value = i;
                const input = row.querySelector('input[type="text"]');
                if (input) input.placeholder = `Option ${i+1}`;
            });
        }

        function labelForIndex(i) {
            // 0 -> A, 1 -> B, ... supports more than 26 if needed
            let label = '';
            i = i + 1; // make 1-indexed for lettering
            while (i > 0) {
                const rem = (i - 1) % 26;
                label = String.fromCharCode(65 + rem) + label;
                i = Math.floor((i - 1) / 26);
            }
            return label;
        }

        function escapeHtml(unsafe) {
            return String(unsafe).replace(/[&<>"]+/g, function(match) {
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' };
                return map[match] || match;
            });
        }

        function enforceCheckboxMode(tp) {
            const checkboxes = document.querySelectorAll('.correct-checkbox');
            // remove existing listeners by cloning
            checkboxes.forEach(cb => {
                const clone = cb.cloneNode(true);
                cb.parentNode.replaceChild(clone, cb);
            });
            if (tp == '1') {
                // single answer - make checkboxes act like radios
                document.querySelectorAll('.correct-checkbox').forEach(cb => {
                    cb.addEventListener('change', function() {
                        if (this.checked) {
                            document.querySelectorAll('.correct-checkbox').forEach(other => { if (other !== this) other.checked = false; });
                        }
                    });
                });
            }
        }

        // Initialize with 4 option rows
        document.addEventListener('DOMContentLoaded', function() {
            const list = document.getElementById('options-list');
            if (list.children.length === 0) {
                addOptionField();
                addOptionField();
                addOptionField();
                addOptionField();
            }
            onTypeChange();

            // populate negative options based on marks
            function initNegative() {
                const marksInput = document.getElementById('marks');
                marksInput.addEventListener('input', updateNegativeOptions);
                updateNegativeOptions();
            }
            initNegative();

            // client-side submit validation
            document.querySelector('form').addEventListener('submit', function(e) {
                const tp = document.getElementById('question_type').value;
                if (tp == '1' || tp == '2') {
                    const checkboxes = document.querySelectorAll('.correct-checkbox');
                    let any = false;
                    checkboxes.forEach(cb => { if (cb.checked) any = true; });
                    if (!any) {
                        e.preventDefault();
                        alert('Please select at least one correct option for MCQ.');
                        return false;
                    }
                    if (tp == '1') {
                        // ensure only one selected
                        let count = 0; document.querySelectorAll('.correct-checkbox').forEach(cb => { if (cb.checked) count++; });
                        if (count > 1) {
                            e.preventDefault();
                            alert('Single-answer type allows only one correct option.');
                            return false;
                        }
                    }
                } else {
                    const ans = document.getElementById('text_answer_input').value.trim();
                    if (!ans) {
                        e.preventDefault();
                        alert('Please provide the answer for the text/short answer question.');
                        return false;
                    }
                }
            });
        });

        function updateNegativeOptions() {
            const marks = parseFloat(document.getElementById('marks').value) || 0;
            const sel = document.getElementById('negative_marks');
            const prev = sel.getAttribute('data-selected') || sel.value || '0';
            // define fractions to show: 0, 1/4, 1/3, 1/2, full
            const fractions = [0, 0.25, 1/3, 0.5, 1];
            sel.innerHTML = '';
            fractions.forEach(fr => {
                const val = (fr === 0) ? 0 : (marks * fr);
                const opt = document.createElement('option');
                opt.value = String(val);
                const label = (fr === 0) ? 'No negative marking' : `${formatNumber(fr)} of marks (${formatNumber(val)})`;
                opt.textContent = label;
                sel.appendChild(opt);
            });
            // try to restore previous selection by matching numeric value
            const found = Array.from(sel.options).find(o => parseFloat(o.value) === parseFloat(prev));
            if (found) sel.value = found.value; else sel.selectedIndex = 0;
        }

        function formatNumber(n) {
            if (typeof n !== 'number') n = parseFloat(n) || 0;
            if (Math.abs(n - Math.round(n)) < 1e-9) return String(Math.round(n));
            return String(Math.round(n * 100) / 100);
        }
    </script>
</x-app-layout>
