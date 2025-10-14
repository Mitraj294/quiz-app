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
                        <label class="block text-sm font-medium mb-2">Question Type</label>
                        <select name="question_type" id="question_type" onchange="onTypeChange()" class="w-full rounded-md border-gray-300">
                            @foreach($questionTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Question Text</label>
                        <textarea name="question_text" id="question_text" rows="3" class="w-full rounded-md border-gray-300"></textarea>
                    </div>

                    <div id="mcq-options" class="mb-4">
                        <label class="block text-sm font-medium mb-2">Options (for MCQ)</label>
                        <div id="options-list" class="space-y-2">
                            <div class="flex gap-2">
                                <input type="text" name="options[]" class="w-full rounded-md border-gray-300" placeholder="Option 1">
                                <button type="button" onclick="addOptionField()" class="px-3 bg-gray-100 rounded">+</button>
                            </div>
                        </div>
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
        function onTypeChange() {
            const tp = document.getElementById('question_type').value;
            const mcq = document.getElementById('mcq-options');
            if (tp == '1' || tp == '2') {
                mcq.style.display = 'block';
            } else {
                mcq.style.display = 'none';
            }
        }

        function addOptionField() {
            const list = document.getElementById('options-list');
            const idx = list.children.length + 1;
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `<input type=\"text\" name=\"options[]\" class=\"w-full rounded-md border-gray-300\" placeholder=\"Option ${idx}\">`;
            list.appendChild(div);
        }

        document.addEventListener('DOMContentLoaded', function() {
            onTypeChange();
        });
    </script>
</x-app-layout>
