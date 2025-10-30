<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Quiz: {{ $quiz->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <x-flash-messages />

                <form method="POST" action="{{ route('quizzes.update', $quiz->id) }}">
                    @csrf
                    @method('PUT')
                    <x-timezone-handler />

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium mb-2">Quiz Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $quiz->name) }}" required class="w-full  mx-auto rounded-md border-gray-300 bg-white px-3 py-2 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium mb-2">Description</label>
                        <textarea id="description" name="description" rows="3" class="w-full  mx-auto rounded-md border-gray-300 bg-white px-3 py-2 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $quiz->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">

                        <div>
                            <label for="max_attempts" class="block text-sm font-medium mb-2">Max Attempts (0 = Unlimited)</label>
                            <input type="number" id="max_attempts" name="max_attempts" value="{{ old('max_attempts', $quiz->max_attempts) }}" class="w-full  mx-auto rounded-md border-gray-300 bg-white px-3 py-2 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <div>
                            <label for="time_between_attempts" class="block text-sm font-medium mb-2">Time Between Attempts (minutes)</label>
                            <input type="number" id="time_between_attempts" name="time_between_attempts" value="{{ old('time_between_attempts', $quiz->time_between_attempts) }}" class="w-full  mx-auto rounded-md border-gray-300 bg-white px-3 py-2 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="duration" class="block text-sm font-medium mb-2">Duration (minutes)</label>
                            <input type="number" id="duration" name="duration" value="{{ old('duration', $quiz->duration) }}" class="w-full  mx-auto rounded-md border-gray-300 bg-white px-3 py-2 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <div>
                            <label for="is_published" class="block text-sm font-medium mb-2">Publish</label>
                            <select name="is_published" id="is_published" class="w-full  mx-auto rounded-md border-gray-300 bg-white px-3 py-2 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="0" {{ (string) old('is_published', $quiz->is_published) === '0' ? 'selected' : '' }}>Draft</option>
                                <option value="1" {{ (string) old('is_published', $quiz->is_published) === '1' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                    
                        <div>
                            <label for="valid_from" class="block text-sm font-medium mb-2">Valid From</label>
                            @php
                                // We output the stored UTC value in a data attribute and let JS convert to client's local time
                                $vfUtc = $quiz->valid_from ? \Carbon\Carbon::parse($quiz->valid_from)->setTimezone('UTC')->toIso8601String() : '';
                                $vfOld = old('valid_from');
                            @endphp
                            <input type="datetime-local" id="valid_from" name="valid_from" value="{{ $vfOld ?? '' }}" data-utc="{{ $vfUtc }}" class="w-full  mx-auto rounded-md border-gray-300 bg-white px-3 py-2 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label for="valid_upto" class="block text-sm font-medium mb-2">Valid Upto</label>
                            @php
                                $vuUtc = $quiz->valid_upto ? \Carbon\Carbon::parse($quiz->valid_upto)->setTimezone('UTC')->toIso8601String() : '';
                                $vuOld = old('valid_upto');
                            @endphp
                            <input type="datetime-local" id="valid_upto" name="valid_upto" value="{{ $vuOld ?? '' }}" data-utc="{{ $vuUtc }}" class="w-full  mx-auto rounded-md border-gray-300 bg-white px-3 py-2 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="topic_id" class="block text-sm font-medium mb-2">Attach Topic (optional)</label>
                        @php
                            // Single-select: prefer old input, fall back to the first attached topic's id (if any)
                            $selectedTopicId = old('topic_id', optional($quiz->topics->first())->id);
                        @endphp
                        <select id="topic_id" name="topic_id" class="w-full  mx-auto rounded-md border-gray-300 bg-white px-3 py-2 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">-- Keep existing --</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}" {{ (string) $topic->id === (string) $selectedTopicId ? 'selected' : '' }}>{{ $topic->name }}</option>
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

    <x-utc-converter />
</x-app-layout>
