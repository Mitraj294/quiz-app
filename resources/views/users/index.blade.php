<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Users</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Authors card -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Authors</h3>
                    <div class="text-sm text-gray-500">Total: {{ $authors->count() }}</div>
                </div>

                @if($authors->isEmpty())
                    <div class="text-gray-500">No authors yet.</div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($authors as $user)
                            <li class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                                <div class="flex items-center space-x-3">
                             

                                    {{-- Remove author role --}}
                                    <form method="POST" action="{{ route('users.roles.remove', [$user->id, 'author']) }}" class="inline" onsubmit="return confirm('Remove author role from {{ addslashes($user->name) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700">Remove</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Users card -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Users</h3>
                    <div class="text-sm text-gray-500">Total: {{ $users->count() }}</div>
                </div>

                @if($users->isEmpty())
                    <div class="text-gray-500">No regular users found.</div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($users as $user)
                            <li class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    {{-- Make Author button if not author --}}
                                    @php $hasAuthor = $user->roles->contains('role', 'author'); @endphp
                                    @if(! $hasAuthor)
                                        <form method="POST" action="{{ route('users.roles.assign', $user->id) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="role" value="author">
                                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700">Make Author</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('users.roles.remove', [$user->id, 'author']) }}" class="inline" onsubmit="return confirm('Remove author role from {{ addslashes($user->name) }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700">Remove Author</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
