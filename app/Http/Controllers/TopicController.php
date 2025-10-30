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
// Quiz model types used in topicables
const QUIZ_MODEL_TYPES = ['App\\Models\\Quiz', 'Harishdurga\\LaravelQuiz\\Models\\Quiz'];
class TopicController extends Controller
{
    /**
     * List top-level topics
     *
     * @return View
     */
    public function index(): View
    {
        // Eager load quizzes count for topics
        $topics = Topic::withCount('quizzes')
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        // Manually set quizzes_count for each child topic
        foreach ($topics as $topic) {
            foreach ($topic->children as $child) {
                $quizCount = DB::table('topicables')
                    ->where('topic_id', $child->id)
                    ->whereIn('topicable_type', QUIZ_MODEL_TYPES)
                    ->count();
                $child->quizzes_count = $quizCount;
            }
        }

        // Calculate total quizzes (direct + in subtopics)
        $totalQuizzes = $topics->sum('quizzes_count') + $topics->flatMap(function($topic){
            return $topic->children;
        })->sum('quizzes_count');

        return view('topics.index', [
            'topics' => $topics,
            'totalQuizzes' => $totalQuizzes,
        ]);
    }

    /**
     * Store a new topic or sub-topic
    *
    * @param Request $request
    * @return RedirectResponse
    */
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

        // If creating a sub-topic, redirect back to parent topic
        $parentId = $data['parent_id'] ?? null;
        if ($parentId) {
            $parent = Topic::find($parentId);
            if ($parent) {
                return redirect()->route('topics.show', $parent->id)
                    ->with('success', 'Sub-topic created successfully!');
            }
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

        // Manually fetch quizzes for main topic
        $quizIds = DB::table('topicables')
            ->where('topic_id', $topic->id)
            ->whereIn('topicable_type', QUIZ_MODEL_TYPES)
            ->pluck('topicable_id');

        /** @var User|null $user */
        $user = Auth::user();

        if ($user instanceof User && $user->isAdmin()) {
            $quizzes = \App\Models\Quiz::whereIn('id', $quizIds)
                ->with('questions')
                ->get();
        } else {
            $quizzes = \App\Models\Quiz::whereIn('id', $quizIds)
                ->where('is_published', 1)
                ->with('questions')
                ->get();
        }
        $topic->setRelation('quizzes', $quizzes);

        // Load quizzes for each child topic
        foreach ($topic->children as $child) {
            $childQuizIds = DB::table('topicables')
                ->where('topic_id', $child->id)
                ->whereIn('topicable_type', QUIZ_MODEL_TYPES)
                ->pluck('topicable_id');

            if ($user instanceof User && $user->isAdmin()) {
                $childQuizzes = \App\Models\Quiz::whereIn('id', $childQuizIds)->get();
            } else {
                $childQuizzes = \App\Models\Quiz::whereIn('id', $childQuizIds)
                    ->where('is_published', 1)
                    ->get();
            }
            $child->setRelation('quizzes', $childQuizzes);
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
