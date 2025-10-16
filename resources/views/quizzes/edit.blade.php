<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Quiz: {{ $quiz->name }}</h2>
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

                <form method="POST" action="{{ route('quizzes.update', $quiz->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium mb-2">Quiz Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $quiz->name) }}" required class="w-full rounded-md border-gray-300">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium mb-2">Description</label>
                        <textarea id="description" name="description" rows="3" class="w-full rounded-md border-gray-300">{{ old('description', $quiz->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="total_marks" class="block text-sm font-medium mb-2">Total Marks</label>
                            <input type="number" step="0.01" id="total_marks" name="total_marks" value="{{ old('total_marks', $quiz->total_marks) }}" class="w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="pass_marks" class="block text-sm font-medium mb-2">Pass Marks</label>
                            <input type="number" step="0.01" id="pass_marks" name="pass_marks" value="{{ old('pass_marks', $quiz->pass_marks) }}" class="w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="max_attempts" class="block text-sm font-medium mb-2">Max Attempts (0 = Unlimited)</label>
                            <input type="number" id="max_attempts" name="max_attempts" value="{{ old('max_attempts', $quiz->max_attempts) }}" class="w-full rounded-md border-gray-300">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="duration" class="block text-sm font-medium mb-2">Duration (minutes)</label>
                            <input type="number" id="duration" name="duration" value="{{ old('duration', $quiz->duration) }}" class="w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="valid_from" class="block text-sm font-medium mb-2">Valid From</label>
                            @php
                                $vf = old('valid_from');
                                if (! $vf && $quiz->valid_from) {
                                    $vf = \Carbon\Carbon::parse($quiz->valid_from)->format('Y-m-d\TH:i');
                                }
                            @endphp
                            <input type="datetime-local" id="valid_from" name="valid_from" value="{{ $vf }}" class="w-full rounded-md border-gray-300">
                        </div>
                        <div>
                            <label for="valid_upto" class="block text-sm font-medium mb-2">Valid Upto</label>
                            @php
                                $vu = old('valid_upto');
                                if (! $vu && $quiz->valid_upto) {
                                    $vu = \Carbon\Carbon::parse($quiz->valid_upto)->format('Y-m-d\TH:i');
                                }
                            @endphp
                            <input type="datetime-local" id="valid_upto" name="valid_upto" value="{{ $vu }}" class="w-full rounded-md border-gray-300">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="topic_id" class="block text-sm font-medium mb-2">Attach Topic (optional)</label>
                        <select id="topic_id" name="topic_id" class="w-full rounded-md border-gray-300">
                            <option value="">-- Keep existing --</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}" {{ $quiz->topics->contains($topic) ? 'selected' : '' }}>{{ $topic->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Save Changes</button>
                        <a href="{{ route('quizzes.show', $quiz->id) }}" class="px-4 py-2 text-gray-700">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
