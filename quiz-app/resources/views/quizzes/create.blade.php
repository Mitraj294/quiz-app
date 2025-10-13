<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Quiz') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Create a New Quiz</h3>
                    
                    <form method="POST" action="{{ route('quizzes.store') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium mb-2">Quiz Title</label>
                            <input type="text" id="title" name="title" required 
                                   class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium mb-2">Description</label>
                            <textarea id="description" name="description" rows="3" 
                                      class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Create Quiz') }}</x-primary-button>
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
