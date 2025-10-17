<div data-fragment="users" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <h3 class="text-lg font-semibold mb-4">All Users</h3>

    <div class="space-y-4">
        @php
            // If the controller didn't provide an $authors variable, derive authors
            // from the current $users collection by checking roles for 'author'.
            $usersCollection = null;
            if(isset($users) && (method_exists($users, 'getCollection') || $users instanceof \Illuminate\Support\Collection)) {
                $usersCollection = method_exists($users, 'getCollection') ? $users->getCollection() : $users;
            } else {
                $usersCollection = collect();
            }

            $authorsList = collect();
            if(!empty($authors) && $authors instanceof \Illuminate\Support\Collection) {
                $authorsList = $authors;
            } else {
                $authorsList = $usersCollection->filter(function($u) {
                    if(!method_exists($u, 'roles')) return false;
                    return $u->roles->pluck('role')->contains('author');
                })->values();
            }

            $authorIds = $authorsList->pluck('id')->all();
            $remainingUsers = $usersCollection->filter(function($u) use ($authorIds) {
                return !in_array($u->id, $authorIds);
            })->values();
        @endphp

        @if($authorsList->count())
            <div>
                <h4 class="text-lg font-semibold mb-2">Authors</h4>
                <div class="grid grid-cols-1  gap-4">
                    @foreach($authorsList as $user)
                        @php
                            $attempts = $user->attempts_count ?? 0;
                            $registered = optional($user->created_at)->toDayDateTimeString() ?? '';
                            $roles = method_exists($user, 'roles') ? $user->roles->pluck('role')->join(', ') : '';
                        @endphp

                        <div class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg border border-gray-200">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-2xl font-bold mb-1">{{ $user->name }}</h3>
                                    <p class="text-sm text-gray-600">{{ $user->email }}</p>
                                    @if($roles)
                                        <p class="text-xs text-gray-500 mt-1">Roles: {{ $roles }}</p>
                                    @endif
                                </div>

                            </div>

                            <div class="grid grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg mt-4">
                                <div>
                                    <span class="text-sm text-gray-600">Attempts</span>
                                    <p class="text-lg font-semibold">{{ $attempts }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Registered</span>
                                    <p class="text-lg font-semibold">{{ $registered }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Status</span>
                                    <p class="text-lg font-semibold">{{ $user->active ? 'Active' : 'Inactive' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
    @endif
      <h4 class="text-lg font-semibold mb-2">Users</h4>
    {{-- Render remaining users (from the current page/collection) --}}
    @foreach($remainingUsers as $user)
            @php
                $attempts = $user->attempts_count ?? 0;
                $registered = optional($user->created_at)->toDayDateTimeString() ?? '';
                $roles = method_exists($user, 'roles') ? $user->roles->pluck('role')->join(', ') : '';
            @endphp
      
            <div class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg border border-gray-200">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-2xl font-bold mb-1">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $user->email }}</p>
                        @if($roles)
                            <p class="text-xs text-gray-500 mt-1">Roles: {{ $roles }}</p>
                        @endif
                    </div>

                </div>

                <div>
                    @php
                        $quizAttempts = collect();

                        // Try to gather attempts for this user, prefer already-loaded relation to avoid extra queries
                        if(method_exists($user, 'attempts')) {
                            $attemptsCollection = $user->relationLoaded('attempts')
                                ? $user->attempts
                                : ($user->attempts()->with(['quiz.topics'])->get() ?? collect());

                            $grouped = $attemptsCollection->groupBy('quiz_id');

                            foreach($grouped as $quizId => $attemptsForQuiz) {
                                $quiz = $attemptsForQuiz->first()->quiz ?? null;
                                $quizTitle = data_get($quiz, 'title') ?? data_get($quiz, 'name') ?? 'Quiz #' . $quizId;
                                // quizzes are linked to topics via a relation 'topics' (many-to-many); use first topic name if present
                                $topicName = data_get($quiz, 'topics.0.name') ?? 'Unknown';
                                $totalAttempts = $attemptsForQuiz->count();
                                $avgScore = round($attemptsForQuiz->avg('score') ?? 0, 2);

                                $quizAttempts->push([
                                    'quizTitle' => $quizTitle,
                                    'topicName' => $topicName,
                                    'totalAttempts' => $totalAttempts,
                                    'avgScore' => $avgScore,
                                ]);
                            }
                        }
                    @endphp

                    @if($quizAttempts->isEmpty())
                        <div class="col-span-3 text-sm text-gray-500">No quiz attempts</div>
                    @else
                    
                            <ul class="grid grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg mt-4">
                                @foreach($quizAttempts as $qa)
                                    <li class="p-3 bg-white border rounded flex items-center justify-between">
                                        <div>
                                            <div class="text-xs text-gray-500">{{ $qa['topicName'] }}</div>
                                            <div class="font-semibold">{{ $qa['quizTitle'] }}</div>
                                        </div>
                                        <div class="text-right text-sm">
                                            <div>Attempts: <strong>{{ $qa['totalAttempts'] }}</strong></div>
                                            <div>Avg Score: <strong>{{ $qa['avgScore'] }}%</strong></div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                    
                    @endif
                </div>
            </div>
        @endforeach

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
</div>
