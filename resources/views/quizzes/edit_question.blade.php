<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Question in {{ $quiz->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-ful mx-auto sm:px-6 lg:px-8">
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

                <form method="POST" action="{{ route('quizzes.questions.update', ['quiz' => $quiz->id, 'question' => $question->id]) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="question_type" class="block text-sm font-medium mb-2">Question Type</label>
                        <select name="question_type" id="question_type" onchange="onTypeChange()" class="w-full rounded-md border-gray-300">
                            @foreach($questionTypes as $key => $label)
                                <option value="{{ $key }}" {{ $currentType == $key ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $label)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="question_text" class="block text-sm font-medium mb-2">Question Text</label>
                        <textarea name="question_text" id="question_text" rows="3" class="w-full rounded-md border-gray-300">{{ old('question_text', $question->name ?? '') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <button type="button" id="toggle-media-btn" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition ease-in-out duration-150">
                            + Add Media
                        </button> <p class="text-xs text-gray-500 mt-2">   Attach image, figure, or diagram related to this question.</p>
                    </div>

                    <div id="media-section" class="mb-4 hidden">
                        <label for="media-input" class="block text-sm font-medium mb-2">Add Media To Question</label>
                        <div id="media-dropzone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-gray-400 transition-colors">
                            <p class="mt-2 text-sm text-gray-600">
                                <button type="button" onclick="document.getElementById('media-input').click()" class="text-indigo-600 hover:text-indigo-500">+ Add Media</button>
                                or drag and drop
                            </p>
                            <p class="text-xs text-gray-500">Image, Audio, or Video (Max 10MB)</p>
                        </div>
                        <input type="file" id="media-input" name="media" accept="image/*,audio/*,video/*" class="hidden">
                        <input type="hidden" name="media_url" id="media_url" value="{{ old('media_url', $question->media_url ?? '') }}">
                        <input type="hidden" name="media_type" id="media_type" value="{{ old('media_type', $question->media_type ?? '') }}">
                        
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
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="mcq-options" class="mb-4">
                        <label for="options-list" class="block text-sm font-medium mb-2">Options</label>
                        <div id="options-list" class="space-y-2">
                            <!-- option rows populated by script -->
                        </div>
                        <div class="mt-2">
                            <button type="button" onclick="addOptionField()" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition ease-in-out duration-150">+ Add option</button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Mark the correct option(s) using the checkbox. For Single Answer type only one may be selected.</p>
                    </div>

                    <div id="text-answer" class="mb-4" style="display:none;">
                        <label for="text_answer_input" class="block text-sm font-medium mb-2">Answer (for Fill in the Blank)</label>
                        <input type="text" name="text_answer" id="text_answer_input" class="w-full rounded-md border-gray-300" value="{{ old('text_answer', optional($question->correct_options()->first())->name ?? '') }}">
                    </div>

                    <div class="mb-4 grid grid-cols-3 gap-3">
                        <div>
                            <label for="marks" class="block text-sm font-medium mb-2">Marks</label>
                            <input type="number" name="marks" id="marks" step="0.01" value="{{ old('marks', $quizQuestion->marks ?? 1) }}" class="w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="negative_marks" class="block text-sm font-medium mb-2">Negative Marks</label>
                            <select name="negative_marks" id="negative_marks" data-selected="{{ old('negative_marks', $quizQuestion->negative_marks ?? 0) }}" class="w-full rounded-md border-gray-300">
                                <!-- populated dynamically -->
                            </select>
                        </div>
                        <div>
                            <label for="is_optional" class="block text-sm font-medium mb-2">Is Optional</label>
                            <select name="is_optional" id="is_optional" class="w-full rounded-md border-gray-300">
                                <option value="0" {{ (old('is_optional', $quizQuestion->is_optional ?? 0) == 0) ? 'selected' : '' }}>No</option>
                                <option value="1" {{ (old('is_optional', $quizQuestion->is_optional ?? 0) == 1) ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition ease-in-out duration-150">Save Changes</button>
                        <a href="{{ route('quizzes.questions.select', $quiz->id) }}" class="px-4 py-2 text-gray-700">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Inject server data as JSON into a hidden textarea so the script can safely parse it. --}} 
    <textarea id="question-data" class="hidden">{!! json_encode([
        'options' => $question->options->map(function($o){ return ['name' => $o->name, 'is_correct' => (bool)$o->is_correct]; }),
        'currentType' => $currentType,
        'textAnswer' => optional($question->correct_options()->first())->name ?? '',
        'mediaUrl' => $question->media_url ?? '',
        'mediaType' => $question->media_type ?? '',
        'questionText' => $question->name ?? '',
    ]) !!}</textarea>

    <script>
        // reuse same client-side logic as questions/edit
        function escapeHtml(unsafe) { return String(unsafe).replace(/[&<>"]+/g, function(match) { const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }; return map[match] || match; }); }
        function labelForIndex(i) { let label = ''; i = i + 1; while (i > 0) { const rem = (i - 1) % 26; label = String.fromCharCode(65 + rem) + label; i = Math.floor((i - 1) / 26); } return label; }

        function onTypeChange() {
            const tp = document.getElementById('question_type')?.value;
            const mcq = document.getElementById('mcq-options');
            const text = document.getElementById('text-answer');
            if (!mcq || !text) return;
            if (tp == '1' || tp == '2') { mcq.style.display = 'block'; text.style.display = 'none'; } else { mcq.style.display = 'none'; text.style.display = 'block'; }
            enforceCheckboxMode(tp);
        }

        function addOptionField(value = '', checked = false) {
            const list = document.getElementById('options-list');
            const idx = list.children.length;
            const div = document.createElement('div');
            div.className = 'flex items-center gap-2 mt-2';
            div.innerHTML = `
                <span class="w-6 text-sm font-medium">${labelForIndex(idx)}</span>
                <input type="checkbox" name="correct[]" value="${idx}" class="correct-checkbox" ${checked ? 'checked' : ''}>
                <input type="text" name="options[]" class="w-full rounded-md border-gray-300" placeholder="Option ${idx+1}" value="${escapeHtml(value)}">
                <button type="button" class="ml-2 text-red-500 hover:text-red-700 remove-option" onclick="removeOptionField(this)" title="Remove option">Remove</button>
            `;
            list.appendChild(div);
        }

        function removeOptionField(btn) { const row = btn.closest('div'); if (!row) return; row.remove(); refreshOptionIndices(); }
        function refreshOptionIndices() { const list = document.getElementById('options-list'); Array.from(list.children).forEach((row, i) => { const labelSpan = row.querySelector('span'); if (labelSpan) labelSpan.textContent = labelForIndex(i); const checkbox = row.querySelector('.correct-checkbox'); if (checkbox) checkbox.value = i; const input = row.querySelector('input[type="text"]'); if (input) input.placeholder = `Option ${i+1}`; }); }

        function enforceCheckboxMode(tp) {
            const checkboxes = document.querySelectorAll('.correct-checkbox');
            // remove existing listeners by replacing nodes
            checkboxes.forEach(cb => {
                const clone = cb.cloneNode(true);
                cb.parentNode.replaceChild(clone, cb);
            });

            if (tp == '1') {
                // Single-answer mode: uncheck all so user must explicitly choose one
                document.querySelectorAll('.correct-checkbox').forEach(cb => { cb.checked = false; });

                // Make checkboxes behave like radios (only one may be checked)
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
            const dataEl = document.getElementById('question-data');
            let serverData = {};
            try { serverData = dataEl && dataEl.value ? JSON.parse(dataEl.value) : {}; } catch (e) { serverData = {}; }

            const options = serverData.options || [];
            const list = document.getElementById('options-list');
            if (options && options.length > 0) { options.forEach((opt) => addOptionField(opt.name, opt.is_correct)); } else { addOptionField(); addOptionField(); addOptionField(); addOptionField(); }

            document.getElementById('question_type').value = serverData.currentType || 1;
            onTypeChange(); enforceCheckboxMode(document.getElementById('question_type').value);

            const textAns = document.getElementById('text_answer_input'); if (textAns) textAns.value = serverData.textAnswer || '';

            const mediaUrl = serverData.mediaUrl || '';
            const mediaType = serverData.mediaType || '';
            if (mediaUrl) {
                document.getElementById('media_url').value = mediaUrl;
                document.getElementById('media_type').value = mediaType;
                const preview = document.getElementById('media-preview');
                const filename = document.getElementById('media-filename');
                const content = document.getElementById('preview-content');
                if (preview && filename && content) {
                    preview.classList.remove('hidden');
                    filename.textContent = mediaUrl.split('/').pop();
                    content.innerHTML = mediaType.startsWith('image') ? `<img src="${mediaUrl}" class="max-h-20">` : `<div class="text-sm text-gray-700">${mediaUrl}</div>`;
                }
            }

            // Initialize negative options using shared helper if available
            (function initNeg() {
                const marksInput = document.getElementById('marks');
                const sel = document.getElementById('negative_marks');
                if (!marksInput || !sel) return;
                if (window.NegativeMarks) {
                    window.NegativeMarks.updateNegativeOptionsForSelect(sel, marksInput.value, sel.getAttribute('data-selected'));
                    marksInput.addEventListener('input', function() { window.NegativeMarks.updateNegativeOptionsForSelect(sel, marksInput.value); });
                } else {
                    marksInput.addEventListener('input', updateNegativeOptions);
                    updateNegativeOptions();
                }
            })();

            document.querySelectorAll('.remove-option').forEach(btn => btn.addEventListener('click', function(){ removeOptionField(this); }));
        });

        function updateNegativeOptions() {
            const marks = parseFloat(document.getElementById('marks').value) || 0;
            const sel = document.getElementById('negative_marks');
            const prev = sel.getAttribute('data-selected') || sel.value || '0';
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
            const found = Array.from(sel.options).find(o => parseFloat(o.value) === parseFloat(prev));
            if (found) sel.value = found.value; else sel.selectedIndex = 0;
        }

        function formatNumber(n) { if (typeof n !== 'number') n = parseFloat(n) || 0; if (Math.abs(n - Math.round(n)) < 1e-9) return String(Math.round(n)); return String(Math.round(n * 100) / 100); }
    </script>
</x-app-layout>
