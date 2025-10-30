<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Users</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex justify-end">
                <button id="showAddUser" type="button" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Add User</button>
            </div>

            <div id="addUserForm" class="bg-white shadow-sm sm:rounded-lg p-6 mt-4 hidden">
                <h3 class="text-lg font-semibold mb-4">Add User</h3>

                @if(session('status'))
                    <div class="mb-4 text-green-700 bg-green-100 border border-green-300 rounded p-2">
                        {{ session('status') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 text-red-700 bg-red-100 border border-red-300 rounded p-2">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('users.add') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input id="name" name="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Enter name" value="{{ old('name') }}" required />
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" name="email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Enter email" value="{{ old('email') }}" required />
                    </div>
                    <div class="mb-4">
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Select role</option>
                            @if(isset($roles) && $roles->isNotEmpty())
                                @foreach($roles as $role)
                                    @if($role->role !== 'admin')
                                        <option value="{{ $role->role }}" {{ old('role') == $role->role ? 'selected' : '' }}>{{ ucfirst($role->role) }}</option>
                                    @endif
                                @endforeach
                            @else
                                <option disabled>No roles available</option>
                            @endif
                        </select>
                    </div>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Create User</button>
                </form>
            </div>

            <script>
                document.getElementById('showAddUser').addEventListener('click', function() {
                    var form = document.getElementById('addUserForm');
                    form.classList.toggle('hidden');
                });
            </script>

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