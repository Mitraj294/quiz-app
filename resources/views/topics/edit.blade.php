<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Topic: {{ $topic->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-ful mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('topics.update', $topic->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium mb-2">Topic Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $topic->name) }}" required class="w-full rounded-md border-gray-300">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium mb-2">Description</label>
                        <textarea id="description" name="description" rows="4" class="w-full rounded-md border-gray-300">{{ old('description', $topic->description) }}</textarea>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Save Changes</button>
                        <a href="{{ route('topics.show', $topic->id) }}" class="px-4 py-2 text-gray-700">Cancel</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
