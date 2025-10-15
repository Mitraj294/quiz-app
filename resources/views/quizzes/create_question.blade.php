<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Question to {{ $quiz->name }}</h2>
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

                    <!-- Toggle Button -->
                    <div class="mb-4">
                        <button type="button" id="toggle-media-btn" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            + Add Media
                        </button> <p class="text-xs text-gray-500 mt-2">   Attach image, figure, or diagram related to this question.</p>
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
                            <button type="button" onclick="addOptionField()" class="px-3 py-1 bg-gray-100 rounded">+ Add option</button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Mark the correct option(s) using the checkbox. For Single Answer type only one may be selected.</p>
                    </div>

                    <div id="text-answer" class="mb-4" style="display:none;">
                        <label for="text_answer_input" class="block text-sm font-medium mb-2">Answer (for Fill in the Blank)</label>
                        <input type="text" name="text_answer" id="text_answer_input" class="w-full rounded-md border-gray-300">
                    </div>

                    <div class="mb-4 grid grid-cols-3 gap-3">
                        <div>
                            <label for="marks" class="block text-sm font-medium mb-2">Marks</label>
                            <input type="number" name="marks" id="marks" step="0.01" value="1" class="w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="negative_marks" class="block text-sm font-medium mb-2">Negative Marks</label>
                            <select name="negative_marks" id="negative_marks" data-selected="{{ old('negative_marks', 0) }}" class="w-full rounded-md border-gray-300">
                                <!-- populated dynamically -->
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
            div.className = 'flex items-center gap-2 mt-2';
            div.innerHTML = `
                <span class="w-6 text-sm font-medium">${labelForIndex(idx)}</span>
                <input type="checkbox" name="correct[]" value="${idx}" class="correct-checkbox">
                <input type="text" name="options[]" class="w-full rounded-md border-gray-300" placeholder="Option ${idx+1}" value="${escapeHtml(value)}">
                <button type="button" class="ml-2 text-red-500 hover:text-red-700 remove-option" onclick="removeOptionField(this)" title="Remove option">Remove</button>
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
            let label = '';
            i = i + 1;
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
            // remove existing listeners by cloning nodes to avoid duplicate handlers
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
            const list = document.getElementById('options-list');
            if (list.children.length === 0) {
                addOptionField(); addOptionField(); addOptionField(); addOptionField();
            }
            onTypeChange();

            // Initialize negative options via shared module
            const marksInput = document.getElementById('marks');
            const sel = document.getElementById('negative_marks');
            if (window.NegativeMarks) {
                window.NegativeMarks.updateNegativeOptionsForSelect(sel, marksInput.value, sel ? sel.getAttribute('data-selected') : undefined);
                marksInput.addEventListener('input', function() {
                    window.NegativeMarks.updateNegativeOptionsForSelect(sel, marksInput.value);
                });
            } else {
                // fallback to local implementation
                marksInput.addEventListener('input', updateNegativeOptions);
                updateNegativeOptions();
            }
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

        function formatNumber(n) {
            if (typeof n !== 'number') n = parseFloat(n) || 0;
            if (Math.abs(n - Math.round(n)) < 1e-9) return String(Math.round(n));
            return String(Math.round(n * 100) / 100);
        }

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

        // Media Upload Functionality
        (function() {
            const dropzone = document.getElementById('media-dropzone');
            const fileInput = document.getElementById('media-input');
            const preview = document.getElementById('media-preview');
            const uploadProgress = document.getElementById('upload-progress');
            const progressBar = document.getElementById('progress-bar');

            // Drag and drop handlers
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => {
                    dropzone.classList.add('border-indigo-500', 'bg-indigo-50');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => {
                    dropzone.classList.remove('border-indigo-500', 'bg-indigo-50');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files.length) handleFile(files[0]);
            }, false);

            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length) handleFile(e.target.files[0]);
            });

            function handleFile(file) {
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    alert('File size must be less than 10MB');
                    return;
                }

                const validTypes = ['image/', 'audio/', 'video/'];
                if (!validTypes.some(type => file.type.startsWith(type))) {
                    alert('Please upload an image, audio, or video file');
                    return;
                }

                uploadFile(file);
            }

            function uploadFile(file) {
                const formData = new FormData();
                formData.append('media', file);
                formData.append('_token', '{{ csrf_token() }}');

                uploadProgress.classList.remove('hidden');
                progressBar.style.width = '0%';

                const xhr = new XMLHttpRequest();
                
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressBar.style.width = percentComplete + '%';
                    }
                });

                xhr.addEventListener('load', () => {
                    uploadProgress.classList.add('hidden');
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        document.getElementById('media_url').value = response.url;
                        document.getElementById('media_type').value = response.type;
                        showPreview(file, response);
                    } else {
                        alert('Upload failed. Please try again.');
                    }
                });

                xhr.addEventListener('error', () => {
                    uploadProgress.classList.add('hidden');
                    alert('Upload failed. Please try again.');
                });

                xhr.open('POST', '{{ route("media.upload") }}');
                xhr.send(formData);
            }

            function showPreview(file, response) {
                const previewContent = document.getElementById('preview-content');
                previewContent.innerHTML = '';

                if (response.type === 'image') {
                    previewContent.innerHTML = `<img src="${response.url}" class="h-12 w-12 object-cover rounded">`;
                } else if (response.type === 'audio') {
                    previewContent.innerHTML = `<svg class="h-12 w-12 text-purple-500" fill="currentColor" viewBox="0 0 20 20"><path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/></svg>`;
                } else if (response.type === 'video') {
                    previewContent.innerHTML = `<svg class="h-12 w-12 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg>`;
                }

                document.getElementById('media-filename').textContent = file.name;
                document.getElementById('media-size').textContent = formatFileSize(file.size);
                preview.classList.remove('hidden');
                dropzone.classList.add('hidden');
            }

            window.removeMedia = function() {
                document.getElementById('media_url').value = '';
                document.getElementById('media_type').value = '';
                document.getElementById('media-input').value = '';
                preview.classList.add('hidden');
                dropzone.classList.remove('hidden');
            };

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            }
        })();
    </script>
</x-app-layout>
