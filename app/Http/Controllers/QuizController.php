<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Quiz;
use App\Models\Topic;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    // Reusable validation rule fragments to avoid duplicated literals
    private const RULE_NULLABLE_STRING = 'nullable|string';
    private const RULE_NULLABLE_INT_MIN0 = 'nullable|integer|min:0';
    private const RULE_NULLABLE_ARRAY = 'nullable|array';
    private const RULE_NULLABLE_NUM_MIN0 = 'nullable|numeric|min:0';

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
            'description' => self::RULE_NULLABLE_STRING,
            'total_marks' => 'nullable|numeric',
            'pass_marks' => 'nullable|numeric',
            'negative_marking_settings' => 'nullable|json',
            'max_attempts' => self::RULE_NULLABLE_INT_MIN0,
            'is_published' => 'nullable|in:0,1',
            'media_url' => self::RULE_NULLABLE_STRING,
            'media_type' => self::RULE_NULLABLE_STRING,
            'duration' => self::RULE_NULLABLE_INT_MIN0,
            'valid_from' => 'nullable|date',
            'valid_upto' => 'nullable|date',
            'time_between_attempts' => self::RULE_NULLABLE_INT_MIN0,
            'topic_option' => 'required|in:existing,new',
        ];

        // Add conditional validation based on topic_option
        if ($request->input('topic_option') === 'existing') {
            $rules['topic_id'] = 'required|exists:topics,id';
        } else {
            $rules['new_topic_name'] = 'required|string|max:255';
            $rules['new_topic_description'] = self::RULE_NULLABLE_STRING;
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
        // Manually fetch topics for this quiz due to polymorphic namespace mismatch
        $topicIds = DB::table('topicables')
            ->where('topicable_id', $quiz->id)
            ->whereIn('topicable_type', ['App\Models\Quiz', 'Harishdurga\LaravelQuiz\Models\Quiz'])
            ->pluck('topic_id');
        
        $topics = \App\Models\Topic::whereIn('id', $topicIds)->get();
        $quiz->setRelation('topics', $topics);
        
        // Load quiz_questions with their related question data and options
        $quiz->load(['questions.question.options']);
        
        return view('quizzes.show', compact('quiz'));
    }

    /**
     * Show a list of existing questions (from topics attached to this quiz)
     * so an admin can select and attach them to the quiz.
     */
    public function selectQuestions(Quiz $quiz)
    {
        // Manually fetch topic IDs for this quiz due to polymorphic namespace mismatch
        $topicIds = DB::table('topicables')
            ->where('topicable_id', $quiz->id)
            ->whereIn('topicable_type', ['App\Models\Quiz', 'Harishdurga\LaravelQuiz\Models\Quiz'])
            ->pluck('topic_id')
            ->toArray();

        if (empty($topicIds)) {
            return view('quizzes.select_questions', [
                'quiz' => $quiz,
                'questions' => collect([])
            ]);
        }

        // Fetch question IDs from the same topics
        $questionIds = DB::table('topicables')
            ->whereIn('topic_id', $topicIds)
            ->whereIn('topicable_type', ['Harishdurga\LaravelQuiz\Models\Question', 'App\Models\Question'])
            ->pluck('topicable_id')
            ->toArray();

        // Load questions with options
        $questions = \App\Models\Question::whereIn('id', $questionIds)
            ->with('options')
            ->get();

        // Get already attached questions with their settings
        $attachedQuestions = \App\Models\QuizQuestion::where('quiz_id', $quiz->id)
            ->get()
            ->keyBy('question_id');

        return view('quizzes.select_questions', compact('quiz', 'questions', 'attachedQuestions'));
    }

    /**
     * Show form to create a new question and attach it directly to the quiz.
     */
    public function createQuestion(Quiz $quiz)
    {
        $questionTypes = [
            1 => 'multiple_choice_single_answer',
            2 => 'multiple_choice_multiple_answer',
            3 => 'Text / Short Answer',
        ];

        return view('quizzes.create_question', compact('quiz', 'questionTypes'));
    }

    /**
     * Store a new question and attach to both questions table and quiz_questions pivot.
     */
    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'question_type' => 'required|in:1,2,3',
            'question_text' => 'required|string',
            'options' => 'array',
            'options.*' => self::RULE_NULLABLE_STRING,
            'correct' => 'array',
            'correct.*' => 'nullable|integer',
            'text_answer' => self::RULE_NULLABLE_STRING,
            'marks' => self::RULE_NULLABLE_NUM_MIN0,
            'negative_marks' => self::RULE_NULLABLE_NUM_MIN0,
            'is_optional' => 'nullable|boolean',
            'media_url' => self::RULE_NULLABLE_STRING,
            'media_type' => self::RULE_NULLABLE_STRING,
        ]);

        // create the question using vendor model
        $questionTypeModel = \Harishdurga\LaravelQuiz\Models\QuestionType::firstOrCreate([
            'name' => [1 => 'multiple_choice_single_answer', 2 => 'multiple_choice_multiple_answer', 3 => 'Text / Short Answer'][$data['question_type']] ?? 'Unknown'
        ]);

        $question = \Harishdurga\LaravelQuiz\Models\Question::create([
            'name' => $data['question_text'],
            'question_type_id' => $questionTypeModel->id,
            'media_url' => $data['media_url'] ?? null,
            'media_type' => $data['media_type'] ?? null,
        ]);

        // Attach to the first topic of the quiz if available
        $topicId = $quiz->topics->first()->id ?? null;
        if ($topicId) {
            $topic = \App\Models\Topic::find($topicId);
            if ($topic) {
                $topic->questions()->attach($question->id);
            }
        }

        // Store options
        if (in_array($data['question_type'], [1,2])) {
            $correct = $data['correct'] ?? [];
            if (! empty($data['options'])) {
                foreach ($data['options'] as $idx => $opt) {
                    if (! empty($opt)) {
                        \Harishdurga\LaravelQuiz\Models\QuestionOption::create([
                            'question_id' => $question->id,
                            'name' => $opt,
                            'is_correct' => in_array($idx, $correct),
                        ]);
                    }
                }
            }
        }

        if ($data['question_type'] == 3 && ! empty($data['text_answer'])) {
            \Harishdurga\LaravelQuiz\Models\QuestionOption::create([
                'question_id' => $question->id,
                'name' => $data['text_answer'],
                'is_correct' => true,
            ]);
        }

        // Attach to quiz with settings
        \App\Models\QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_id' => $question->id,
            'marks' => $data['marks'] ?? 1,
            'negative_marks' => $data['negative_marks'] ?? 0,
            'is_optional' => $data['is_optional'] ?? 0,
            'order' => 0,
        ]);

        return redirect()->route('quizzes.show', $quiz->id)->with('success', 'Question created and attached to quiz');
    }

    /**
     * Attach selected existing questions to the quiz with their settings.
     * Updates existing questions or creates new ones.
     */
    public function attachQuestions(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'integer|exists:questions,id',
            'marks' => self::RULE_NULLABLE_ARRAY,
            'marks.*' => self::RULE_NULLABLE_NUM_MIN0,
            'negative_marks' => self::RULE_NULLABLE_ARRAY,
            'negative_marks.*' => self::RULE_NULLABLE_NUM_MIN0,
            'is_optional' => self::RULE_NULLABLE_ARRAY,
            'is_optional.*' => 'nullable|boolean',
        ]);

        // Update or create quiz_question records for each selected question
        foreach ($data['question_ids'] as $questionId) {
            \App\Models\QuizQuestion::updateOrCreate(
                [
                    'quiz_id' => $quiz->id,
                    'question_id' => $questionId,
                ],
                [
                    'marks' => $data['marks'][$questionId] ?? 1,
                    'negative_marks' => $data['negative_marks'][$questionId] ?? 0,
                    'is_optional' => $data['is_optional'][$questionId] ?? 0,
                    'order' => 0,
                ]
            );
        }

        return redirect()->route('quizzes.show', $quiz->id)
            ->with('success', 'Questions attached/updated to quiz successfully');
    }

    /**
     * Detach a question from the quiz (remove from quiz_questions pivot table only)
     */
    public function detachQuestion(Quiz $quiz, $questionId)
    {
        \App\Models\QuizQuestion::where('quiz_id', $quiz->id)
            ->where('question_id', $questionId)
            ->delete();

        return redirect()->back()->with('success', 'Question removed from quiz successfully');
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
