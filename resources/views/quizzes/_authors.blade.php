@php
/**
 * Partial: Manage quiz authors
 * Expects: $quiz (App\Models\Quiz), $users (collection of App\Models\User for selection)
 */
@endphp

<div class="card p-4">
    <h4 class="mb-2">Authors</h4>

    <ul class="mb-4">
        @foreach($quiz->authors as $author)
            <li class="flex items-center justify-between mb-2">
                <div>
                    <strong>{{ $author->name }}</strong>
                    <div class="text-sm text-gray-600">Role: {{ $author->pivot->author_role ?? 'contributor' }}</div>
                </div>
                <form method="POST" action="{{ route('quizzes.authors.detach', [$quiz->id, $author->id]) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Remove</button>
                </form>
            </li>
        @endforeach
    </ul>

    <form method="POST" action="{{ route('quizzes.authors.attach', $quiz->id) }}" class="grid grid-cols-1 gap-2">
        @csrf
        <div>
            <label for="author_user" class="block text-sm">Select user</label>
            <select id="author_user" name="user_id" class="form-select w-full">
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="author_role" class="block text-sm">Role (optional)</label>
            <input id="author_role" type="text" name="author_role" class="form-input w-full" placeholder="creator, editor, contributor">
        </div>
        <div>
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" checked class="form-checkbox">
                <span class="ml-2">Active</span>
            </label>
        </div>
        <div>
            <button class="btn btn-primary">Add author</button>
        </div>
    </form>
</div>
