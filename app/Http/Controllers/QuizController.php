<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Harishdurga\LaravelQuiz\Models\Quiz;
use App\Models\Topic;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::with('topics')->latest()->get();
        return view('quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        $topics = Topic::orderBy('name')->get();
        return view('quizzes.create', compact('topics'));
    }

    public function store(Request $request)
    {
        // Validation rules depend on topic_option
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_marks' => 'nullable|numeric',
            'pass_marks' => 'nullable|numeric',
            'is_published' => 'nullable|boolean',
            'topic_option' => 'required|in:existing,new',
        ];

        // Add conditional validation based on topic_option
        if ($request->input('topic_option') === 'existing') {
            $rules['topic_id'] = 'required|exists:topics,id';
        } else {
            $rules['new_topic_name'] = 'required|string|max:255';
            $rules['new_topic_description'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        // Handle topic creation or selection
        if ($validated['topic_option'] === 'new') {
            // Create new topic
            $topic = Topic::create([
                'name' => $validated['new_topic_name'],
                'description' => $validated['new_topic_description'] ?? null,
            ]);
            $topicId = $topic->id;
        } else {
            // Use existing topic
            $topicId = $validated['topic_id'];
        }

        // Create quiz
        $quiz = Quiz::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'total_marks' => $validated['total_marks'] ?? 0,
            'pass_marks' => $validated['pass_marks'] ?? 0,
            'is_published' => $request->has('is_published'),
        ]);

        // Attach topic
        $quiz->topics()->attach($topicId);

        return redirect()->route('topics.show', $topicId)
            ->with('success', 'Quiz created successfully!');
    }

    public function show(Quiz $quiz)
    {
        $quiz->load('topics', 'questions');
        return view('quizzes.show', compact('quiz'));
    }

    public function destroy(Quiz $quiz)
    {
        $topicId = $quiz->topics()->first()->id ?? null;
        $quiz->delete();

        if ($topicId) {
            return redirect()->route('topics.show', $topicId)
                ->with('success', 'Quiz deleted successfully!');
        }

        return redirect()->route('topics.index')
            ->with('success', 'Quiz deleted successfully!');
    }
}
