<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TopicController extends Controller
{
    public function index()
    {
        // Only top-level topics (exclude sub-topics)
        $topics = Topic::whereNull('parent_id')->orderBy('name')->get();
        return view('topics.index', [
            'topics' => $topics,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:topics,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        Topic::create($data);

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

    public function show(Topic $topic)
    {
        // Load questions
        $topic->load('questions');

        // Manually fetch quizzes due to polymorphic namespace mismatch
        $quizIds = DB::table('topicables')
            ->where('topic_id', $topic->id)
            ->whereIn('topicable_type', ['App\Models\Quiz', 'Harishdurga\LaravelQuiz\Models\Quiz'])
            ->pluck('topicable_id');

        // Load quizzes based on user role
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->isAdmin()) {
            // Admins see all quizzes (including drafts)
            $quizzes = \App\Models\Quiz::whereIn('id', $quizIds)->get();
        } else {
            // Regular users see only published quizzes
            $quizzes = \App\Models\Quiz::whereIn('id', $quizIds)
                ->where('is_published', 1)
                ->get();
        }

        // Attach quizzes collection to topic for view compatibility
        $topic->setRelation('quizzes', $quizzes);

        return view('topics.show', [
            'topic' => $topic,
        ]);
    }
}
