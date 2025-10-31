<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Topic;
use App\Models\Attempt;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    // Constants
    private const TOPICABLE_TYPES = ['App\\Models\\Quiz', 'Harishdurga\\LaravelQuiz\\Models\\Quiz'];

    /**
     * Show topics analytics page.
     */
    public function topics(Request $request): View
    {
        $topics = Topic::with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->paginate(25);

        $topicsCount = Topic::whereNull('parent_id')->count();
        $quizzesCount = Quiz::count();
        $usersCountNonAdmin = $this->countNonAdminUsers();

        // Build quiz stats per topic (small dataset expected)
        $topicQuizStats = [];
        foreach ($topics as $topic) {
            $topicableIds = DB::table('topicables')
                ->where('topic_id', $topic->id)
                ->pluck('topicable_id')
                ->all();

            $relatedQuizzes = Quiz::whereIn('id', $topicableIds)->get();
            $quizStats = [];

            foreach ($relatedQuizzes as $quiz) {
                $totalMarks = DB::table('quiz_questions')
                    ->where('quiz_id', $quiz->id)
                    ->sum('marks');

                $passMarks = $quiz->pass_marks ?? (int) round($totalMarks / 3);

                $quizStats[] = [
                    'id' => $quiz->id,
                    'title' => $quiz->title ?? $quiz->name,
                    'totalMarks' => $totalMarks,
                    'passMarks' => $passMarks,
                ];
            }

            $topicQuizStats[$topic->id] = $quizStats;
        }

        return view('admin.analytics.topics', compact(
            'topics', 'topicsCount', 'quizzesCount', 'usersCountNonAdmin', 'topicQuizStats'
        ));
    }

    /**
     * Show quizzes analytics page.
     */
    public function quizzes(Request $request): View
    {
        $quizzes = Quiz::withCount('questions')->orderBy('name')->paginate(25);
        $topicsCount = Topic::whereNull('parent_id')->count();
        $quizzesCount = Quiz::count();
        $usersCountNonAdmin = $this->countNonAdminUsers();

        $quizStats = [];
        $quizUserData = [];
        foreach ($quizzes as $quiz) {
            $totalQuestions = $quiz->questions_count ?? 0;
            $mandatory = $quiz->mandatory_questions_count ?? ($totalQuestions > 0 ? $totalQuestions - 1 : 0);
            $optional = $quiz->optional_questions_count ?? ($totalQuestions > 0 ? 1 : 0);

            $totalMarks = DB::table('quiz_questions')
                ->where('quiz_id', $quiz->id)
                ->sum('marks');

            $passMarks = (int) round($totalMarks / 3);
            $maxAttempts = $quiz->max_attempts ?? ($quiz->attempts_count > 0 ? $quiz->attempts_count : 1);

            $statusLabel = ($quiz->published ?? false)
                ? '<span class="text-green-600">Published</span>'
                : '<span class="text-gray-600">Draft</span>';

            $storedAttempts = isset($quiz->attempts_count) ? (int) $quiz->attempts_count : null;
            $sumScores = DB::table('quiz_attempts')->where('quiz_id', $quiz->id)->sum('score');
            $attemptsCount = DB::table('quiz_attempts')->where('quiz_id', $quiz->id)->count();
            $average_score = $attemptsCount > 0 ? round($sumScores / $attemptsCount, 2) : null;
            $totalAttempts = ($storedAttempts > 0) ? $storedAttempts : $attemptsCount;
            $usersAttemptedFromTable = DB::table('quiz_attempts')->where('quiz_id', $quiz->id)->distinct()->count('user_id');

            $usersAttempted = $quiz->users_attempted_count
                ?? $quiz->unique_attempts_count
                ?? $quiz->attempts_users_count
                ?? $usersAttemptedFromTable
                ?? null;

            $quizStats[$quiz->id] = [
                'totalQuestions' => $totalQuestions,
                'mandatory' => $mandatory,
                'optional' => $optional,
                'totalMarks' => $totalMarks,
                'passMarks' => $passMarks,
                'maxAttempts' => $maxAttempts,
                'statusLabel' => $statusLabel,
                'usersAttempted' => $usersAttempted,
                'totalAttempts' => $totalAttempts,
                'average_score' => $average_score,
            ];
        }

        // Precompute per-quiz user attempt data and pass to view to avoid running queries in Blade
        $quizUserData = $this->buildQuizUserData($quizzes);

        return view('admin.analytics.quizzes', compact(
            'quizzes', 'quizzesCount', 'topicsCount', 'usersCountNonAdmin', 'quizStats', 'quizUserData'
        ));
    }

    /**
     * Build per-quiz user names and attempt aggregates used by the quizzes view.
     *
     * @param \Illuminate\Support\Collection $quizzes
     * @return array<int, array{userNames:\Illuminate\Support\Collection, attemptCounts:\Illuminate\Support\Collection, attemptAverages:\Illuminate\Support\Collection}>
     */
    private function buildQuizUserData($quizzes): array
    {
        $quizUserData = [];
        foreach ($quizzes as $quiz) {
            $attemptsForQuiz = Attempt::where('quiz_id', $quiz->id)
                ->whereNotNull('user_id')
                ->whereNull('deleted_at')
                ->get();

            $userIds = $attemptsForQuiz->pluck('user_id')->unique()->filter()->values()->all();
            $userNames = User::whereIn('id', $userIds)->pluck('name', 'id');

            $attemptCounts = $attemptsForQuiz->groupBy('user_id')->map->count();
            $attemptAverages = $attemptsForQuiz->groupBy('user_id')->map(function ($group) {
                $avg = $group->avg('score');
                return is_null($avg) ? null : round($avg, 2);
            });

            $quizUserData[$quiz->id] = [
                'userNames' => $userNames,
                'attemptCounts' => $attemptCounts,
                'attemptAverages' => $attemptAverages,
            ];
        }
        return $quizUserData;
    }

    /**
     * Show users analytics page.
     */
    public function users(Request $request): View
    {
        $users = User::orderBy('name')->paginate(25);
        $topicsCount = Topic::whereNull('parent_id')->count();
        $quizzesCount = Quiz::count();
        $usersCountNonAdmin = $this->countNonAdminUsers();

        $usersCollection = method_exists($users, 'getCollection') ? $users->getCollection() : $users;
        [$authorsList, $nonAdminAuthors] = !empty($usersCollection)
            ? $this->splitUsersByRole($usersCollection)
            : [collect(), collect()];

        $userQuizAttempts = [];
        foreach ($users as $user) {
            $userQuizAttempts[$user->id] = $this->buildQuizAttempts($user);
        }

        return view('admin.analytics.users', compact(
            'users', 'usersCountNonAdmin', 'topicsCount', 'quizzesCount',
            'userQuizAttempts', 'authorsList', 'nonAdminAuthors'
        ));
    }

    /** Show basic platform analytics for admins. */
    public function index(Request $request)
    {
        $quizzesCount = Quiz::count();
        $usersCount = User::count();
        $topicsCount = Topic::whereNull('parent_id')->count();
        $subtopicsCount = Topic::whereNotNull('parent_id')->count();
        $topicQuizCount = DB::table('topicables')
            ->whereIn('topicable_type', self::TOPICABLE_TYPES)
            ->distinct()
            ->count('topicable_id');
        try {
            $usersCountNonAdmin = User::whereDoesntHave('roles', fn($q) => $q->where('role','admin'))->count();
        } catch (\Throwable $e) {
            $usersCountNonAdmin = $usersCount;
        }
        return view('admin.analytics', compact(
            'quizzesCount', 'usersCount', 'topicsCount', 'subtopicsCount', 'topicQuizCount', 'usersCountNonAdmin'
        ));
    }


    // Helpers
    
    private function getRoles($user): array
    {
        return method_exists($user, 'roles')
            ? $user->roles->pluck('role')->map(fn($r) => strtolower($r))->all()
            : [];
    }

    private function splitUsersByRole($usersCollection): array
    {
        $authors = collect();
        $nonAdmins = collect();
        foreach ($usersCollection as $u) {
            $roles = $this->getRoles($u);
            if (in_array('author', $roles)) {
                $authors->push($u);
            } elseif (!in_array('admin', $roles)) {
                $nonAdmins->push($u);
            }
        }
        return [$authors, $nonAdmins];
    }

    private function buildQuizAttempts($user): array
    {
        $quizAttempts = [];
        $attemptsCollection = method_exists($user, 'attempts') ? $user->attempts()->with(['quiz.topics'])->get() : collect();
        foreach ($attemptsCollection->groupBy('quiz_id') as $quizId => $attemptsForQuiz) {
            $quiz = $attemptsForQuiz->first()->quiz ?? null;
            $quizTitle = data_get($quiz, 'title') ?? data_get($quiz, 'name') ?? 'Quiz #' . $quizId;
            $topicName = data_get($quiz, 'topics.0.name') ?? 'Unknown';
            $totalAttempts = $attemptsForQuiz->count();
            $avgScore = round($attemptsForQuiz->avg('score') ?? 0, 2);
            $totalMarks = $quiz ? (int) DB::table('quiz_questions')->where('quiz_id', $quiz->id)->sum('marks') : 0;
            $quizAttempts[] = [
                'quizId' => $quizId,
                'quizTitle' => $quizTitle,
                'topicName' => $topicName,
                'totalAttempts' => $totalAttempts,
                'avgScore' => $avgScore,
                'totalMarks' => $totalMarks,
            ];
        }
        return $quizAttempts;
    }

    /**
     * Count non-admin users with a safe fallback if roles table is missing.
     */
    private function countNonAdminUsers(): int
    {
        try {
            return User::whereDoesntHave('roles', fn($q) => $q->where('role', 'admin'))->count();
        } catch (\Throwable $e) {
            return User::count();
        }
    }

}
