<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends Controller
{
    // Constants
    private const TOPICABLE_TYPES = ['App\\Models\\Quiz', 'Harishdurga\\LaravelQuiz\\Models\\Quiz'];

    /** Show topics analytics page. */
    public function topics(Request $request)
    {
        $topics = Topic::with('children')->whereNull('parent_id')->orderBy('name')->paginate(25);
        $topicsCount = Topic::whereNull('parent_id')->count();
        $quizzesCount = Quiz::count();
        $usersCountNonAdmin = User::whereDoesntHave('roles', fn($q) => $q->where('role', 'admin'))->count();

        $topicQuizStats = [];
        foreach ($topics as $topic) {
            $topicableIds = DB::table('topicables')->where('topic_id', $topic->id)->pluck('topicable_id')->all();
            $relatedQuizzes = Quiz::whereIn('id', $topicableIds)->get();
            $quizStats = [];
            foreach ($relatedQuizzes as $quiz) {
                $totalMarks = DB::table('quiz_questions')->where('quiz_id', $quiz->id)->sum('marks');
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

    /** Show quizzes analytics page. */
    public function quizzes(Request $request)
    {
        $quizzes = Quiz::withCount('questions')->orderBy('name')->paginate(25);
        $topicsCount = Topic::whereNull('parent_id')->count();
        $quizzesCount = Quiz::count();
        $usersCountNonAdmin = User::whereDoesntHave('roles', fn($q) => $q->where('role', 'admin'))->count();

        $quizStats = [];
        foreach ($quizzes as $quiz) {
            $totalQuestions = $quiz->questions_count ?? 0;
            $mandatory = $quiz->mandatory_questions_count ?? ($totalQuestions > 0 ? $totalQuestions - 1 : 0);
            $optional = $quiz->optional_questions_count ?? ($totalQuestions > 0 ? 1 : 0);
            $totalMarks = DB::table('quiz_questions')->where('quiz_id', $quiz->id)->sum('marks');
            $passMarks = (int) round($totalMarks / 3);
            $maxAttempts = $quiz->max_attempts ?? ($quiz->attempts_count > 0 ? $quiz->attempts_count : 1);
            $statusLabel = ($quiz->published ?? false)
                ? '<span class="text-green-600">Published</span>'
                : '<span class="text-gray-600">Draft</span>';

            $storedAttempts = isset($quiz->attempts_count) ? (int)$quiz->attempts_count : null;
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

        return view('admin.analytics.quizzes', compact(
            'quizzes', 'quizzesCount', 'topicsCount', 'usersCountNonAdmin', 'quizStats'
        ));
    }

    /** Show users analytics page. */
    public function users(Request $request)
    {
        $users = User::orderBy('name')->paginate(25);
        $topicsCount = Topic::whereNull('parent_id')->count();
        $quizzesCount = Quiz::count();
        $usersCountNonAdmin = User::whereDoesntHave('roles', fn($q) => $q->where('role', 'admin'))->count();

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

    /** Return a server-rendered topics table partial for inline insertion (supports pagination via ?page=...) */
    public function topicsFragment(Request $request)
    {
        $topics = Topic::with('children')->whereNull('parent_id')->orderBy('name')->paginate(25);
        $topicIds = $topics->pluck('id')->all();
        $quizCounts = DB::table('topicables')
            ->whereIn('topic_id', $topicIds)
            ->whereIn('topicable_type', self::TOPICABLE_TYPES)
            ->select('topic_id', DB::raw('count(distinct topicable_id) as quizzes'))
            ->groupBy('topic_id')
            ->pluck('quizzes', 'topic_id');
        return view('admin.partials._topics_table', compact('topics', 'quizCounts'));
    }

    /** Return a server-rendered quizzes table partial for inline insertion */
    public function quizzesFragment(Request $request)
    {
        $quizzes = Quiz::withCount('questions')->orderBy('name')->paginate(25);
        $quizIds = $quizzes->pluck('id')->all();
        $attemptCounts = DB::table('quiz_attempts')
            ->whereIn('quiz_id', $quizIds)
            ->select('quiz_id', DB::raw('count(*) as attempts'))
            ->groupBy('quiz_id')
            ->pluck('attempts', 'quiz_id');
        foreach ($quizzes as $quiz) {
            $quiz->attempts_count = $attemptCounts[$quiz->id] ?? 0;
        }
        return view('admin.partials._quizzes_table', compact('quizzes'));
    }

    /** Return a server-rendered users table partial for inline insertion */
    public function usersFragment(Request $request)
    {
        $authors = collect();
        $users = collect();
        try {
            if (Schema::hasTable('roles')) {
                $authors = User::where(function ($q) {
                    $q->whereHas('roles', fn($r) => $r->whereRaw('LOWER(role) = ?', ['author']))
                        ->orWhereHas('authoredQuizzes');
                })
                    ->whereDoesntHave('roles', fn($q) => $q->whereRaw('LOWER(role) = ?', ['admin']))
                    ->withCount('attempts')
                    ->orderBy('name')
                    ->get();
                $users = User::whereDoesntHave('roles', fn($q) => $q->whereRaw("LOWER(role) IN ('admin', 'author')"))
                    ->whereDoesntHave('authoredQuizzes')
                    ->withCount('attempts')
                    ->orderBy('name')
                    ->paginate(25);
            } else {
                $authorIds = DB::table('quiz_authors')->pluck('author_id')->unique()->all();
                $authors = User::whereIn('id', $authorIds)
                    ->withCount('attempts')
                    ->orderBy('name')
                    ->get();
                $users = User::whereNotIn('id', $authorIds)
                    ->withCount('attempts')
                    ->orderBy('name')
                    ->paginate(25);
            }
        } catch (\Exception $e) {
            $authors = collect();
            $users = User::withCount('attempts')->orderBy('name')->paginate(25);
        }
        return view('admin.partials._users_table', compact('authors', 'users'));
    }

    /** Return a single topic detail partial for inline insertion (used when clicking a subtopic) */
    public function topicFragment(Topic $topic)
    {
        $topic->load('children');
        $topicableIds = DB::table('topicables')->where('topic_id', $topic->id)->pluck('topicable_id')->all();
        $relatedQuizzes = Quiz::whereIn('id', $topicableIds)->get();
        $html = '<div data-fragment="topic-detail" class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg">';
        $html .= '<h3 class="text-2xl font-bold mb-4">' . e($topic->name) . '</h3>';
        $html .= '<div class="mb-6"><p class="text-gray-800">' . e($topic->description ?? $topic->name) . '</p></div>';
        $html .= '<div class="mt-8"><h4 class="text-lg font-semibold mb-4">Related Quizzes</h4><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
        foreach ($relatedQuizzes as $quiz) {
            $title = e($quiz->title ?? $quiz->name);
            $total = e($quiz->total_marks ?? 0);
            $pass = e($quiz->pass_marks ?? 0);
            $url = route('quizzes.show', $quiz->id);
            $html .= "<div class=\"border border-gray-300 rounded-lg p-4 hover:shadow-md transition\">";
            $html .= "<div class=\"flex justify-between items-start\"><div class=\"flex-1\"><h5 class=\"font-semibold mb-2\">{$title}</h5><div class=\"flex items-center gap-4 text-xs text-gray-500 mb-2\"><span> Total: {$total} marks</span><span>Pass: {$pass} marks</span></div></div></div>";
            $html .= "<div class=\"flex gap-3 mt-3\"><a href=\"{$url}\" class=\"text-sm text-indigo-600 hover:text-indigo-900 font-medium\">View Details</a></div></div>";
        }
        $html .= '</div></div></div>';
        return response($html);
    }

    // Helpers
    private function getRoles($user)
    {
        return method_exists($user, 'roles')
            ? $user->roles->pluck('role')->map(fn($r) => strtolower($r))->all()
            : [];
    }

    private function splitUsersByRole($usersCollection)
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

    private function buildQuizAttempts($user)
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
}
