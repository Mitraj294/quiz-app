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
                    <input type="hidden" name="timezone" id="timezone">
                    <input type="hidden" id="server-tz" value="{{ config('app.timezone') }}">

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
                        <select id="topic_id" name="topic_id" class="w-full  mx-auto rounded-md border-gray-300 bg-white px-3 py-2 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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
                
                <div class="mt-6">
                    @include('quizzes._authors', ['quiz' => $quiz, 'users' => $users])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // populate timezone hidden input
        try {
            var tz = (Intl && Intl.DateTimeFormat && Intl.DateTimeFormat().resolvedOptions) ? Intl.DateTimeFormat().resolvedOptions().timeZone : null;
            if (!tz) {
                var st = document.getElementById('server-tz');
                tz = st ? st.value : 'UTC';
            }
            var tzInput = document.getElementById('timezone');
            if (tzInput) tzInput.value = tz;
        } catch (e) {
            // ignore
        }

        // Helper to format a Date to yyyy-MM-ddTHH:mm for datetime-local
        function toLocalDateTimeInputValue(date) {
            var yr = date.getFullYear();
            var mo = String(date.getMonth() + 1).padStart(2, '0');
            var da = String(date.getDate()).padStart(2, '0');
            var hr = String(date.getHours()).padStart(2, '0');
            var mi = String(date.getMinutes()).padStart(2, '0');
            return yr + '-' + mo + '-' + da + 'T' + hr + ':' + mi;
        }

        // Convert any inputs with data-utc attribute into local datetime-local values
        ['valid_from', 'valid_upto'].forEach(function(id) {
            var inp = document.getElementById(id);
            if (!inp) return;
            // if user had old input (validation error) we should not override
            if (inp.value && inp.value.length) return;
            var utc = inp.getAttribute('data-utc');
            if (!utc) return;
            try {
                var d = new Date(utc); // utc parsing
                if (!isNaN(d.getTime())) {
                    inp.value = toLocalDateTimeInputValue(d);
                }
            } catch (e) {
                // ignore
            }
        });
    });
</script>
