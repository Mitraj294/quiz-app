<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                @php
                $isAdmin = false;
                if(auth()->user()) {
                $isAdmin = \Illuminate\Support\Facades\DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('role_user.user_id', auth()->id())
                ->where('roles.role', 'admin')
                ->exists();
                }
                @endphp
                @if($isAdmin && request()->has('user_id'))
                {{ \App\Models\User::find(request()->input('user_id'))?->name ?? 'User' }}'s Attempts for {{ $quiz->name }}
                @else
                Your Attempts for {{ $quiz->name }}
                @endif
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">
                        @if(Auth::user() && Auth::user()->isAdmin() && request()->has('user_id'))
                            <div class="flex flex-col">
                                <div class="text-lg font-bold">User: {{ optional(\App\Models\User::find(request()->input('user_id')))->name ?? 'User' }}</div>
                                <div class="text-lg font-bold">Quiz: {{ $quiz->name }}</div>
                            </div>
                        @else
                        {{ $quiz->name }}
                        @endif
                    </h3>
                    <div class="grid grid-cols-2 gap-6 bg-gray-50 rounded-lg p-4 min-w-[340px]">
                        <div>
                            <div class="text-xs text-gray-600">Total Mark</div>
                            <div class="text-base font-semibold">{{ $quiz->total_marks ?? $quiz->total_mark ?? $quiz->total ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Average Mark</div>
                            <div class="text-base font-semibold">
                                @php
                                $completedAttempts = $attempts->filter(fn($a) => $a->completed_at !== null);
                                $avgValue = $completedAttempts->count() ? $completedAttempts->avg('score') : null;
                                $avgMark = $avgValue !== null ? number_format($avgValue, 2, '.', '') : null;
                                $passMark = $quiz->pass_marks !== null ? (float) $quiz->pass_marks : null;
                                $colorClass = ($avgValue !== null && $passMark !== null)
                                ? ($avgValue >= $passMark ? 'text-green-600' : 'text-red-600')
                                : '';
                                @endphp
                                @if($avgMark !== null)
                                <span class="{{ $colorClass }}">{{ $avgMark }}</span>
                                @else
                                —
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Exam Duration</div>
                            <div class="text-base font-semibold">{{ $quiz->duration ? $quiz->duration . ' min' : '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-600">Average Time Taken</div>
                            <div class="text-base font-semibold">
                                @php
                                $avgTime = null;
                                if ($completedAttempts->count()) {
                                $totalSeconds = $completedAttempts->reduce(function($carry, $a) {
                                $start = $a->created_at ? \Carbon\Carbon::parse($a->created_at) : null;
                                $end = $a->completed_at ? \Carbon\Carbon::parse($a->completed_at) : null;
                                return $carry + (($start && $end) ? $start->diffInSeconds($end) : 0);
                                }, 0);
                                $avgTime = $totalSeconds ? number_format($totalSeconds / 60 / $completedAttempts->count(), 2, '.', '') : null;
                                }
                                @endphp
                                {{ $avgTime !== null ? $avgTime . ' min' : '—' }}
                            </div>
                        </div>
                    </div>
                </div>
                @if($attempts->count() === 0)
                <p class="text-gray-600">No attempts found for this quiz.</p>
                @else
                <div class="space-y-6">
                    @foreach($attempts as $attempt)
                    @if($attempt->completed_at)
                    <div class="p-6 border rounded-lg bg-gray-50">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <div class="text-sm text-gray-600">Total Mark</div>
                                <div class="text-lg font-semibold">
                                    {{ $quiz->total_marks ?? $quiz->total_mark ?? $quiz->total ?? '—' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-sm text-gray-600">Passing Mark</div>
                                <div class="text-lg font-semibold">
                                    {{ $quiz->pass_marks ?? '—' }}
                                </div>
                            </div>



                            <div>
                                <div class="text-sm text-gray-600">Score</div>
                                <div class="text-lg font-semibold">
                                    <span class="{{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">{{ $attempt->score }}</span>
                                </div>
                            </div>

                            <div>
                                <div class="text-sm text-gray-600">Passed</div>
                                <div class="text-lg">
                                    @if($attempt->passed)
                                    <span class="text-green-600 font-semibold">Yes</span>
                                    @else
                                    <span class="text-red-600 font-semibold">No</span>
                                    @endif
                                </div>
                            </div>


                            <div>
                                <div class="text-sm text-gray-600">Exam Duration</div>
                                <div class="text-lg font-semibold">
                                    {{ $quiz->duration ? $quiz->duration . ' min' : '—' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Time Taken</div>
                                <div class="text-lg font-semibold">
                                    @php
                                    $start = $attempt->created_at ? \Carbon\Carbon::parse($attempt->created_at) : null;
                                    $end = $attempt->completed_at ? \Carbon\Carbon::parse($attempt->completed_at) : null;
                                    if ($start && $end) {
                                    $seconds = $start->diffInSeconds($end);
                                    $minutesFloat = $seconds / 60;
                                    // show exactly 4 digits after the decimal point
                                    $minutes = number_format($minutesFloat, 2, '.', '');
                                    } else {
                                    $minutes = null;
                                    }
                                    @endphp
                                    {{ $minutes !== null ? $minutes . ' min' : '—' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-sm text-gray-600">Started At</div>
                                <div class="text-lg" data-utc="{{ $attempt->created_at ? \Carbon\Carbon::parse($attempt->created_at)->setTimezone('UTC')->toIso8601String() : '' }}">
                                    <span class="no-js">{{ $attempt->created_at }}</span>
                                </div>
                            </div>

                            <div>
                                <div class="text-sm text-gray-600">Completed At</div>
                                <div class="text-lg" data-utc="{{ $attempt->completed_at ? \Carbon\Carbon::parse($attempt->completed_at)->setTimezone('UTC')->toIso8601String() : '' }}">
                                    <span class="no-js">{{ $attempt->completed_at }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @php
                                    $params = ['quiz' => $quiz->id, 'attempt' => $attempt->id];
                                    if($isAdmin && request()->has('user_id')) {
                                        $params['user_id'] = request()->input('user_id');
                                    }
                                @endphp
                                <a href="{{ route('quizzes.attempt_show', $params) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-200 rounded text-sm text-blue-600 hover:bg-gray-50">View</a>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $attempts->links() }}
                </div>
                @endif

                <x-utc-converter />
                @if($isAdmin)
                    <div class="mt-6">
                        <a href="{{ route('admin.analytics.users') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Back to Users Analytics</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>