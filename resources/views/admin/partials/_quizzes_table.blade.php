<div data-fragment="quizzes" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <h3 class="text-lg font-semibold mb-4">All Quizzes</h3>

    <div class="space-y-4">
        @foreach($quizzes as $quiz)
        @php
        // derive some values with sensible fallbacks
        $totalQuestions = $quiz->questions_count ?? 0;
        // assume mandatory/optional data not present; show placeholders or calculate if available
        $mandatory = $quiz->mandatory_questions_count ?? ($totalQuestions > 0 ? $totalQuestions - 1 : 0);
        $optional = $quiz->optional_questions_count ?? ($totalQuestions > 0 ? 1 : 0);
        $totalMarks = $quiz->total_marks ?? 0;
        $passMarks = $quiz->pass_marks ?? 0;
        $maxAttempts = $quiz->max_attempts ?? ($quiz->attempts_count > 0 ? $quiz->attempts_count : 1);
        $statusLabel = ($quiz->published ?? false) ? '<span class="text-green-600">Published</span>' : '<span class="text-gray-600">Draft</span>';
        @endphp

        <div class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-200">
            <div class="flex items-start justify-between">
                <div>
                    <h4 class="text-lg font-semibold">{{ $quiz->title ?? $quiz->name }}</h4>
                    <p class="text-sm text-gray-500">{{ $quiz->description ?? '' }}</p>
                </div>

            </div>

            <div class="grid grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg mt-4">
                <div>
                    <span class="text-sm text-gray-600">Total Questions</span>
                    <p class="text-lg font-semibold">{{ $totalQuestions }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Mandatory</span>
                    <p class="text-lg font-semibold">{{ $mandatory }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Optional</span>
                    <p class="text-lg font-semibold">{{ $optional }}</p>
                </div>

                <div>
                    <span class="text-sm text-gray-600">Total Marks</span>
                    <p class="text-lg font-semibold">{{ $totalMarks }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Pass Marks</span>
                    <p class="text-lg font-semibold">{{ $passMarks }}</p>
                </div>



                <div>
                    <span class="text-sm text-gray-600">Max Attempts</span>
                    <p class="text-lg font-semibold">{{ $maxAttempts }}</p>
                </div>




            </div>
            @php
                // fallbacks for different possible attribute names
                $usersAttempted = $quiz->users_attempted_count 
                    ?? $quiz->unique_attempts_count 
                    ?? $quiz->attempts_users_count 
                    ?? null;
                // prefer stored attempts_count when it's a positive number, otherwise fall back to counting rows
                $storedAttempts = isset($quiz->attempts_count) ? (int)$quiz->attempts_count : null;

                // calculate sums and counts from quiz_attempts table
                $sumScores = \DB::table('quiz_attempts')->where('quiz_id', $quiz->id)->sum('score');
                $attemptsCount = \DB::table('quiz_attempts')->where('quiz_id', $quiz->id)->count();

                // safe average calculation
                $average_score = $attemptsCount > 0 ? round($sumScores / $attemptsCount, 2) : null;

                // use stored positive value if available, otherwise use the actual attempts count
                $totalAttempts = ($storedAttempts > 0) ? $storedAttempts : $attemptsCount;

                // derive users attempted: prefer model attributes but fall back to distinct user count
                $usersAttemptedFromTable = \DB::table('quiz_attempts')->where('quiz_id', $quiz->id)->distinct()->count('user_id');
                $usersAttempted = $quiz->users_attempted_count 
                    ?? $quiz->unique_attempts_count 
                    ?? $quiz->attempts_users_count 
                    ?? $usersAttemptedFromTable
                    ?? null;
            
            @endphp

            <div class="grid grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg mt-4">
                <div>
                    <span class="text-sm text-gray-600">Users Attempted</span>
                    <p class="text-lg font-semibold">
                        {{ $usersAttempted !== null ? $usersAttempted : 0 }}
                    </p>
                </div>

                <div>
                    <span class="text-sm text-gray-600">Total Attempts</span>
                    <p class="text-lg font-semibold">{{ $totalAttempts ?? 0 }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Average Score</span>
                    @if(is_null($average_score))
                        <p class="text-lg font-semibold text-gray-600">N/A</p>
                    @else
                        <p class="text-lg font-semibold {{ $average_score >= ($passMarks ?? 0) ? 'text-green-600' : 'text-red-600' }}">
                            {{ $average_score }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        <div class="mt-4">
            {{ $quizzes->links() }}
        </div>
    </div>
</div>