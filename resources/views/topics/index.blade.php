<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Topics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">All Topics</h3>
                        <button type="button" 
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                onclick="document.getElementById('create-topic-form').classList.toggle('hidden')">
                            Create New Topic
                        </button>
                    </div>

                    <!-- Create Topic Form -->
                    <div id="create-topic-form" class="@if($errors->any()) @else hidden @endif mb-6 p-4 border border-gray-300 rounded-lg bg-gray-50">
                        <h4 class="text-lg font-medium mb-4">Create New Topic</h4>
                        <form method="POST" action="{{ route('topics.store') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium mb-2">Topic Name <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required 
                                       class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium mb-2">Description (Optional)</label>
                                <textarea id="description" name="description" rows="3" 
                                          class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Create Topic') }}</x-primary-button>
                                <button type="button" 
                                        class="text-sm text-gray-600 hover:text-gray-900"
                                        onclick="document.getElementById('create-topic-form').classList.add('hidden')">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Topics List -->
                    @if(isset($topics) && $topics->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($topics as $topic)
                                <a href="{{ route('topics.show', $topic->id) }}" 
                                   class="block p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                    <h4 class="font-semibold mb-2">{{ $topic->name }}</h4>
                                    @if($topic->description)
                                        <p class="text-sm text-gray-600">{{ $topic->description }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No topics available yet. Create one to get started!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
