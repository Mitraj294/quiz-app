<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Quiz;

class TopicController extends Controller
{
    private const QUIZ_MODEL_TYPES = ['App\\Models\\Quiz', 'Harishdurga\\LaravelQuiz\\Models\\Quiz'];

    public function index(): View
    {
        $topics = Topic::with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $user = Auth::user();

        // For each top-level topic compute both total quizzes (all) and published quizzes counts
        foreach ($topics as $topic) {
            // gather quiz ids attached to this topic
            $quizIds = DB::table('topicables')
                ->where('topic_id', $topic->id)
                ->whereIn('topicable_type', self::QUIZ_MODEL_TYPES)
                ->pluck('topicable_id');

            // fetch all quizzes for counting
            $allQuizzes = Quiz::whereIn('id', $quizIds)->get();
            $topic->all_quizzes_count = $allQuizzes->count();
            $topic->published_quizzes_count = $allQuizzes->where('is_published', 1)->count();

            // compute visible quizzes for current user (used by some parts of view if needed)
            if ($user instanceof User && $user->isAdmin()) {
                $visibleCount = $allQuizzes->count();
            } else {
                $visibleCount = $allQuizzes->where('is_published', 1)->count();
            }
            $topic->quizzes_count = $visibleCount;

            // For each child, compute counts similarly
            foreach ($topic->children as $child) {
                $childQuizIds = DB::table('topicables')
                    ->where('topic_id', $child->id)
                    ->whereIn('topicable_type', self::QUIZ_MODEL_TYPES)
                    ->pluck('topicable_id');

                $childAllQuizzes = Quiz::whereIn('id', $childQuizIds)->get();
                $child->all_quizzes_count = $childAllQuizzes->count();
                $child->published_quizzes_count = $childAllQuizzes->where('is_published', 1)->count();

                if ($user instanceof User && $user->isAdmin()) {
                    $childVisible = $childAllQuizzes->count();
                } else {
                    $childVisible = $childAllQuizzes->where('is_published', 1)->count();
                }
                $child->quizzes_count = $childVisible;
            }

            // totals across topic + its immediate children
            $topic->total_quizzes = $topic->all_quizzes_count + $topic->children->sum('all_quizzes_count');
            $topic->published_total_quizzes = $topic->published_quizzes_count + $topic->children->sum('published_quizzes_count');
        }

        return view('topics.index', compact('topics'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:topics,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $topic = Topic::create($data);

        if (! $topic) {
            return back()->withInput()->withErrors(['error' => 'Failed to create topic. Please try again.']);
        }

        $parentId = $data['parent_id'] ?? null;
        if ($parentId && $parent = Topic::find($parentId)) {
            return redirect()->route('topics.show', $parent->id)->with('success', 'Sub-topic created successfully!');
        }

        return redirect()->route('topics.index')->with('success', 'Topic created successfully!');
    }

    /**
     * Show a topic and its quizzes
    *
    * @param Topic $topic
    * @return View
    */
    public function show(Topic $topic): View
    {
        $topic->load('questions', 'children');

        $quizIds = DB::table('topicables')
            ->where('topic_id', $topic->id)
            ->whereIn('topicable_type', self::QUIZ_MODEL_TYPES)
            ->pluck('topicable_id');

        $user = Auth::user();

        if ($user instanceof User && $user->isAdmin()) {
            $quizzes = Quiz::whereIn('id', $quizIds)->with('questions')->get();
        } else {
            $quizzes = Quiz::whereIn('id', $quizIds)->where('is_published', 1)->with('questions')->get();
        }
        $topic->setRelation('quizzes', $quizzes);

        // compute counts for the main topic's quizzes
        $topic->quizzes_count = $topic->quizzes->count();
        $topic->published_quizzes_count = $topic->quizzes->where('is_published', 1)->count();

        // Load quizzes for each child topic
        foreach ($topic->children as $child) {
            $childQuizIds = DB::table('topicables')
                ->where('topic_id', $child->id)
                ->whereIn('topicable_type', self::QUIZ_MODEL_TYPES)
                ->pluck('topicable_id');

            if ($user instanceof User && $user->isAdmin()) {
                $childQuizzes = Quiz::whereIn('id', $childQuizIds)->get();
            } else {
                $childQuizzes = Quiz::whereIn('id', $childQuizIds)->where('is_published', 1)->get();
            }
            $child->setRelation('quizzes', $childQuizzes);

            // attach counts used by the view to avoid inline logic in blade
            $child->quizzes_count = $childQuizzes->count();
            $child->published_quizzes_count = $childQuizzes->where('is_published', 1)->count();
        }

        return view('topics.show', [
            'topic' => $topic,
        ]);
    }

    /**
     * Show edit form for the topic (admin only)
     *
     * @param Topic $topic
     * @return View
     */
    public function edit(Topic $topic): View
    {
        return view('topics.edit', [
            'topic' => $topic,
        ]);
    }

    /**
     * Update the topic
    *
    * @param Request $request
    * @param Topic $topic
    * @return RedirectResponse
    */
    public function update(Request $request, Topic $topic): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($data['name']) && $data['name'] !== $topic->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        $topic->update($data);

        return redirect()->route('topics.show', $topic->id)->with('success', 'Topic updated successfully.');
    }

    /**
     * Soft-delete the topic (admin only)
    *
    * @param Topic $topic
    * @return RedirectResponse
    */
    public function destroy(Topic $topic): RedirectResponse
    {
        $user = Auth::user();
        if (! ($user instanceof User) || ! $user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $parentId = $topic->parent_id;
        $topic->delete();

        if ($parentId) {
            return redirect()->route('topics.show', $parentId)->with('success', 'Sub-topic deleted.');
        }

        return redirect()->route('topics.index')->with('success', 'Topic deleted.');
    }
}
