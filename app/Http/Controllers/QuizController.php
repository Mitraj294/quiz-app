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
            'negative_marking_settings' => 'nullable|json',
            'max_attempts' => 'nullable|integer|min:0',
            'is_published' => 'nullable|in:0,1',
            'media_url' => 'nullable|string',
            'media_type' => 'nullable|string',
            'duration' => 'nullable|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_upto' => 'nullable|date',
            'time_between_attempts' => 'nullable|integer|min:0',
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
            'negative_marking_settings' => $validated['negative_marking_settings'] ?? null,
            'max_attempts' => $validated['max_attempts'] ?? 0,
            'is_published' => isset($validated['is_published']) ? (int)$validated['is_published'] : 0,
            'media_url' => $validated['media_url'] ?? null,
            'media_type' => $validated['media_type'] ?? null,
            'duration' => $validated['duration'] ?? 0,
            'valid_from' => $validated['valid_from'] ?? now(),
            'valid_upto' => $validated['valid_upto'] ?? null,
            'time_between_attempts' => $validated['time_between_attempts'] ?? 0,
        ]);

    // Attach topic (avoid duplicate pivot entries)
    $quiz->topics()->syncWithoutDetaching([$topicId]);

        return redirect()->route('topics.show', $topicId)
            ->with('success', 'Quiz created successfully!');
    }

    public function show(Quiz $quiz)
    {
        $quiz->load('topics', 'questions');
        return view('quizzes.show', compact('quiz'));
    }

    /**
     * Show a list of existing questions (from topics attached to this quiz)
     * so an admin can select and attach them to the quiz.
     */
    public function selectQuestions(Quiz $quiz)
    {
        // Load topics and their questions
        $quiz->load('topics');

        // Collect questions from quiz topics
        $topicIds = $quiz->topics->pluck('id')->toArray();
        $questions = \Harishdurga\LaravelQuiz\Models\Question::whereHas('topics', function ($q) use ($topicIds) {
            $q->whereIn('topics.id', $topicIds);
        })->with('options')->get();

        return view('quizzes.select_questions', compact('quiz', 'questions'));
    }

    /**
     * Attach selected existing questions to the quiz.
     */
    public function attachQuestions(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'integer|exists:questions,id',
        ]);

        $quiz->questions()->attach($data['question_ids']);

        return redirect()->route('quizzes.show', $quiz)->with('success', 'Questions attached to quiz successfully');
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
