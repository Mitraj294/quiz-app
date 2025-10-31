<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-ful mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            @if(isset($user) && $user->authoredQuizzes->count())
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-semibold mb-4">Quizzes You Author</h3>
                    <ul class="space-y-2">
                        @foreach($user->authoredQuizzes as $quiz)
                            <li class="border-b pb-2">
                                <a href="{{ route('quizzes.show', $quiz->id) }}" class="text-indigo-700 font-semibold hover:underline">{{ $quiz->name }}</a>
                                <span class="text-xs text-gray-500 ml-2">{{ $quiz->description ? Str::limit($quiz->description, 60) : '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
