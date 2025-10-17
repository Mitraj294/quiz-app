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
    private const TOPICABLE_TYPES = ['App\\Models\\Quiz', 'Harishdurga\\LaravelQuiz\\Models\\Quiz'];
    // (no private fallback prefix required for page-only controller)

    /**
     * Show basic platform analytics for admins.
     */
    public function index(Request $request)
    {
        $quizzesCount = Quiz::count();
        $usersCount = User::count();
        // Only count top-level topics (exclude subtopics)
        $topicsCount = Topic::whereNull('parent_id')->count();

        // Total subtopics (children of top-level topics)
        $subtopicsCount = Topic::whereNotNull('parent_id')->count();

        // Total distinct quizzes attached to any topic
        $topicQuizCount = DB::table('topicables')
            ->whereIn('topicable_type', self::TOPICABLE_TYPES)
            ->distinct()
            ->count('topicable_id');

        return view('admin.analytics', compact('quizzesCount', 'usersCount', 'topicsCount', 'subtopicsCount', 'topicQuizCount'));
    }

    /**
     * Render a paginated page listing topics with counts (admin view)
     */
    // topicsPage removed: topics are now served only via topicsFragment for inline rendering within analytics

    /**
     * Return a server-rendered topics table partial for inline insertion (supports pagination via ?page=...)
     */
    public function topicsFragment(Request $request)
    {
    // only top-level topics (no parent)
    $topics = Topic::with('children')->whereNull('parent_id')->orderBy('name')->paginate(25);

        // fetch quiz counts for the topics on this page
        $topicIds = $topics->pluck('id')->all();
        $quizCounts = DB::table('topicables')
            ->whereIn('topic_id', $topicIds)
            ->whereIn('topicable_type', self::TOPICABLE_TYPES)
            ->select('topic_id', DB::raw('count(distinct topicable_id) as quizzes'))
            ->groupBy('topic_id')
            ->pluck('quizzes', 'topic_id');

        return view('admin.partials._topics_table', compact('topics', 'quizCounts'));
    }

    /**
     * Return a server-rendered quizzes table partial for inline insertion
     */
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

    /**
     * Return a server-rendered users table partial for inline insertion
     */
    public function usersFragment(Request $request)
    {
        // We'll always compute $authors (collection) and $users (LengthAwarePaginator or Collection)
        // while being defensive about missing schema (roles table) and missing relations on models.

        $authors = collect();
        $users = collect();

        try {
            if (Schema::hasTable('roles')) {
                // Authors: users who have role 'author' (case-insensitive) OR who have authored quizzes.
                $authors = User::where(function($q) {
                        $q->whereHas('roles', function($r){ $r->whereRaw('LOWER(role) = ?', ['author']); })
                          ->orWhereHas('authoredQuizzes');
                    })
                    ->whereDoesntHave('roles', function($q){ $q->whereRaw('LOWER(role) = ?', ['admin']); })
                    ->withCount('attempts')
                    ->orderBy('name')
                    ->get();

                // Other non-admin, non-author users - paginated. Exclude users who are authors by role or authoredQuizzes.
                $users = User::whereDoesntHave('roles', function($q){ $q->whereRaw("LOWER(role) IN ('admin', 'author')"); })
                    ->whereDoesntHave('authoredQuizzes')
                    ->withCount('attempts')
                    ->orderBy('name')
                    ->paginate(25);
            } else {
                // No roles table: derive authors from quiz_authors pivot and exclude them from paginated users.
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
            // As a last-resort fallback, return all users paginated and no authors to avoid 500.
            // This prevents template rendering from failing if schema or relations are unexpected.
            $authors = collect();
            $users = User::withCount('attempts')->orderBy('name')->paginate(25);
        }

        // Ensure $authors is a Collection and $users is a paginator or collection as before
        return view('admin.partials._users_table', compact('authors','users'));
    }

    /**
     * Return a single topic detail partial for inline insertion (used when clicking a subtopic)
     */
    public function topicFragment(Topic $topic)
    {
        // load children and related quizzes for this topic
        $topic->load('children');

        $topicableIds = DB::table('topicables')->where('topic_id', $topic->id)->pluck('topicable_id')->all();
        $relatedQuizzes = \App\Models\Quiz::whereIn('id', $topicableIds)->get();

        // Return a small HTML fragment directly to avoid depending on a separate partial file
        $html = '<div data-fragment="topic-detail" class="p-6 text-gray-900 bg-white shadow-sm sm:rounded-lg">';
        $html .= '<h3 class="text-2xl font-bold mb-4">'.e($topic->name).'</h3>';
        $html .= '<div class="mb-6"><p class="text-gray-800">'.e($topic->description ?? $topic->name).'</p></div>';
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


}
