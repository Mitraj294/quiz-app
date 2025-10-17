<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Quiz;
use App\Models\Topic;
use Illuminate\Support\Str;

/**
 * QuizController
 *
 * Note: this application stores `duration` and `time_between_attempts` in minutes.
 * The UI expects and displays values in minutes and the controller persists them as minutes.
 */
class QuizController extends Controller
{
    // Reusable validation rule fragments to avoid duplicated literals
    private const RULE_NULLABLE_STRING = 'nullable|string';
    private const RULE_NULLABLE_INT_MIN0 = 'nullable|integer|min:0';
    private const RULE_NULLABLE_ARRAY = 'nullable|array';
    private const RULE_NULLABLE_NUM_MIN0 = 'nullable|numeric|min:0';
    private const RULE_NULLABLE_BOOLEAN = 'nullable|boolean';
    private const RULE_REQUIRED_STRING_MAX255 = 'required|string|max:255';
    private const RULE_NULLABLE_DATE = 'nullable|date';
    private const TEXT_SHORT_ANSWER = 'fill_the_blank';

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

    /**
     * Store a new quiz.
     */
    public function store(Request $request)
    {
        // Validation rules depend on topic_option
        $rules = [
            'name' => self::RULE_REQUIRED_STRING_MAX255,
            'description' => self::RULE_NULLABLE_STRING,
            'total_marks' => self::RULE_NULLABLE_NUM_MIN0,
            'pass_marks' => self::RULE_NULLABLE_NUM_MIN0,
            // negative_marking_settings: expects a JSON object, e.g. {"type":"fixed","value":1}, to configure negative marking per quiz
            'negative_marking_settings' => 'nullable|json',
            'max_attempts' => self::RULE_NULLABLE_INT_MIN0,
            'is_published' => self::RULE_NULLABLE_BOOLEAN,
            'media_url' => self::RULE_NULLABLE_STRING,
            'media_type' => self::RULE_NULLABLE_STRING,
            'duration' => self::RULE_NULLABLE_INT_MIN0,
            'valid_from' => self::RULE_NULLABLE_DATE,
            'valid_upto' => self::RULE_NULLABLE_DATE,
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

        // Create quiz (store duration and time_between_attempts as minutes)
        $quiz = Quiz::create([
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['name']),
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

    /**
     * Show the quiz edit form to admins
     */
    public function edit(Quiz $quiz)
    {
        $topics = Topic::orderBy('name')->get();
        return view('quizzes.edit', compact('quiz', 'topics'));
    }

    /**
     * Update quiz fields
     */
    public function update(Request $request, Quiz $quiz)
    {
        $rules = [
            'name' => self::RULE_REQUIRED_STRING_MAX255,
            'description' => self::RULE_NULLABLE_STRING,
            'total_marks' => self::RULE_NULLABLE_NUM_MIN0,
            'pass_marks' => self::RULE_NULLABLE_NUM_MIN0,
            'max_attempts' => self::RULE_NULLABLE_INT_MIN0,
            'is_published' => self::RULE_NULLABLE_BOOLEAN,
            'duration' => self::RULE_NULLABLE_INT_MIN0,
            'valid_from' => self::RULE_NULLABLE_DATE,
            'valid_upto' => self::RULE_NULLABLE_DATE,
            'time_between_attempts' => self::RULE_NULLABLE_INT_MIN0,
            'topic_id' => 'nullable|exists:topics,id',
        ];

        $data = $request->validate($rules);

        $quiz->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'total_marks' => $data['total_marks'] ?? $quiz->total_marks,
            'pass_marks' => $data['pass_marks'] ?? $quiz->pass_marks,
            'max_attempts' => $data['max_attempts'] ?? $quiz->max_attempts,
            'is_published' => isset($data['is_published']) ? (int)$data['is_published'] : $quiz->is_published,
            'duration' => $data['duration'] ?? $quiz->duration,
            'valid_from' => $data['valid_from'] ?? $quiz->valid_from,
            'valid_upto' => $data['valid_upto'] ?? $quiz->valid_upto,
            'time_between_attempts' => $data['time_between_attempts'] ?? $quiz->time_between_attempts,
        ]);

        // Attach or sync topic if provided
        if (! empty($data['topic_id'])) {
            $quiz->topics()->syncWithoutDetaching([$data['topic_id']]);
        }

        return redirect()->route('quizzes.show', $quiz->id)->with('success', 'Quiz updated successfully');
    }

    /**
     * Generate a unique slug for the quiz name.
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $i = 1;
        while (Quiz::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $i;
            $i++;
        }
        return $slug;
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
            3 => self::TEXT_SHORT_ANSWER,
        ];

        return view('quizzes.create_question', compact('quiz', 'questionTypes'));
    }

    /**
     * Show the edit form for a question within the quiz context (allows editing both question and quiz-specific settings)
     */
    public function editQuestion(Quiz $quiz, $questionId)
    {
        $question = \Harishdurga\LaravelQuiz\Models\Question::with('options', 'question_type')->findOrFail($questionId);

        // Load the quiz-specific pivot data if it exists
        $quizQuestion = \App\Models\QuizQuestion::where('quiz_id', $quiz->id)->where('question_id', $questionId)->first();

        $questionTypes = [
            1 => 'multiple_choice_single_answer',
            2 => 'multiple_choice_multiple_answer',
            3 => self::TEXT_SHORT_ANSWER,
        ];

        // Map the vendor question_type name to numeric key
        $typeMap = [
            'multiple_choice_single_answer' => 1,
            'multiple_choice_multiple_answer' => 2,
            'fill_the_blank' => 3,
        ];

        $currentType = 1;
        if ($question->relationLoaded('question_type') && $question->question_type) {
            $currentType = $typeMap[$question->question_type->name] ?? 1;
        }

        return view('quizzes.edit_question', compact('quiz', 'question', 'quizQuestion', 'questionTypes', 'currentType'));
    }

    /**
     * Update a question and its quiz-specific settings
     */
    public function updateQuestion(Request $request, Quiz $quiz, $questionId)
    {
        $rules = [
            'question_type' => 'required|in:1,2,3',
            'question_text' => 'required|string',
            'options' => 'array',
            'options.*' => self::RULE_NULLABLE_STRING,
            'correct' => 'array',
            'correct.*' => 'nullable|integer',
            'text_answer' => self::RULE_NULLABLE_STRING,
            'marks' => self::RULE_NULLABLE_NUM_MIN0,
            'negative_marks' => self::RULE_NULLABLE_NUM_MIN0,
            'is_optional' => self::RULE_NULLABLE_BOOLEAN,
            'media_url' => self::RULE_NULLABLE_STRING,
            'media_type' => self::RULE_NULLABLE_STRING,
        ];

        $data = $request->validate($rules);

        $questionTypeModel = \Harishdurga\LaravelQuiz\Models\QuestionType::firstOrCreate(['name' => $this->questionTypeName($data['question_type'])]);

        DB::transaction(function () use ($data, $questionId, $questionTypeModel, $quiz) {
            $question = \Harishdurga\LaravelQuiz\Models\Question::findOrFail($questionId);

            // Update question fields
            $question->update([
                'name' => $data['question_text'],
                'question_type_id' => $questionTypeModel->id,
                'media_url' => $data['media_url'] ?? null,
                'media_type' => $data['media_type'] ?? null,
            ]);

            // Persist options
            $this->persistQuestionOptions($questionId, $data['question_type'], $data['options'] ?? [], $data['correct'] ?? [], $data['text_answer'] ?? null);

            // Update or create pivot record in quiz_questions
            \App\Models\QuizQuestion::updateOrCreate([
                'quiz_id' => $quiz->id,
                'question_id' => $questionId,
            ], [
                'marks' => $data['marks'] ?? 1,
                'negative_marks' => $data['negative_marks'] ?? 0,
                'is_optional' => $data['is_optional'] ?? 0,
                'order' => 0,
            ]);
        });

        Log::info('Quiz-scoped question updated', ['quiz_id' => $quiz->id, 'question_id' => $questionId, 'request' => $data]);

        return redirect()->route('quizzes.show', $quiz->id)
            ->with('success', 'Question and quiz settings updated successfully');
    }

    /**
     * Resolve numeric question_type to the vendor string name
     */
    private function questionTypeName(int $type): string
    {
        return [
            1 => 'multiple_choice_single_answer',
            2 => 'multiple_choice_multiple_answer',
            3 => self::TEXT_SHORT_ANSWER,
        ][$type] ?? 'Unknown';
    }

    /**
     * Persist question options depending on type (MCQ or text answer)
     */
    private function persistQuestionOptions(int $questionId, int $type, array $options = [], array $correct = [], ?string $textAnswer = null): void
    {
        // Delete all existing options first
        \Harishdurga\LaravelQuiz\Models\QuestionOption::where('question_id', $questionId)->delete();

        if (in_array($type, [1, 2])) {
            foreach ($options as $idx => $opt) {
                if (! empty($opt)) {
                    \Harishdurga\LaravelQuiz\Models\QuestionOption::create([
                        'question_id' => $questionId,
                        'name' => $opt,
                        'is_correct' => in_array($idx, $correct),
                    ]);
                }
            }
        } elseif ($type == 3 && ! empty($textAnswer)) {
            \Harishdurga\LaravelQuiz\Models\QuestionOption::create([
                'question_id' => $questionId,
                'name' => $textAnswer,
                'is_correct' => true,
            ]);
        }
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
            'is_optional' => self::RULE_NULLABLE_BOOLEAN,
            'media_url' => self::RULE_NULLABLE_STRING,
            'media_type' => self::RULE_NULLABLE_STRING,
        ]);

        // create the question using vendor model
        $questionTypeModel = \Harishdurga\LaravelQuiz\Models\QuestionType::firstOrCreate([
            'name' => [1 => 'multiple_choice_single_answer', 2 => 'multiple_choice_multiple_answer', 3 => self::TEXT_SHORT_ANSWER][$data['question_type']] ?? 'Unknown'
        ]);

        $question = \Harishdurga\LaravelQuiz\Models\Question::create([
            'name' => $data['question_text'],
            'question_type_id' => $questionTypeModel->id,
            'media_url' => $data['media_url'] ?? null,
            'media_type' => $data['media_type'] ?? null,
        ]);

        // Attach to the first topic of the quiz if available
        $quiz->loadMissing('topics');
        $topicId = optional($quiz->topics->first())->id;
        if ($topicId) {
            $topic = \App\Models\Topic::find($topicId);
            if ($topic) {
                $topic->questions()->attach($question->id);
            }
        }

        // Store options
        if (in_array($data['question_type'], [1, 2])) {
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

        // Attach to quiz with settings (avoid duplicate entries)
        \App\Models\QuizQuestion::updateOrCreate(
            [
                'quiz_id' => $quiz->id,
                'question_id' => $question->id,
            ],
            [
                'marks' => $data['marks'] ?? 1,
                'negative_marks' => $data['negative_marks'] ?? 0,
                'is_optional' => $data['is_optional'] ?? 0,
                'order' => 0,
            ]
        );

        return redirect()->route('quizzes.show', $quiz->id)->with('success', 'Question created and attached to quiz');
    }

    /**
     * Attach selected existing questions to the quiz with their settings.
     * Updates existing questions or creates new ones.
     */
    public function attachQuestions(Request $request, Quiz $quiz)
    {
        Log::info('=== ATTACH QUESTIONS CALLED ===');
        Log::info('Quiz ID: ' . $quiz->id);
        Log::info('Request Data:', $request->all());

        try {
            $data = $request->validate([
                'question_ids' => 'required|array',
                'question_ids.*' => 'integer|exists:questions,id',
                'marks' => self::RULE_NULLABLE_ARRAY,
                'marks.*' => self::RULE_NULLABLE_NUM_MIN0,
                'negative_marks' => self::RULE_NULLABLE_ARRAY,
                'negative_marks.*' => self::RULE_NULLABLE_NUM_MIN0,
                'is_optional' => self::RULE_NULLABLE_ARRAY,
                'is_optional.*' => self::RULE_NULLABLE_BOOLEAN,
            ]);

            Log::info('Validation passed. Validated data:', $data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', $e->errors());
            throw $e;
        }

        Log::info('Processing ' . count($data['question_ids']) . ' questions');

        // Update or create quiz_question records for each selected question
        foreach ($data['question_ids'] as $questionId) {
            $questionData = [
                'marks' => $data['marks'][$questionId] ?? 1,
                'negative_marks' => $data['negative_marks'][$questionId] ?? 0,
                'is_optional' => $data['is_optional'][$questionId] ?? 0,
                'order' => 0,
            ];

            Log::info("Attaching Question ID: $questionId", $questionData);

            \App\Models\QuizQuestion::updateOrCreate(
                [
                    'quiz_id' => $quiz->id,
                    'question_id' => $questionId,
                ],
                $questionData
            );

            Log::info("Successfully attached Question ID: $questionId");
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
        $topicId = optional($quiz->topics->first())->id;
        $quiz->delete();

        if ($topicId) {
            return redirect()->route('topics.show', $topicId)
                ->with('success', 'Quiz deleted successfully!');
        }

        return redirect()->route('topics.index')
            ->with('success', 'Quiz deleted successfully!');
    }

    /**
     * Toggle publish state for a quiz (admin only).
     */
    public function publish(Request $request, Quiz $quiz)
    {
        // Toggle the boolean state
        $quiz->is_published = !$quiz->is_published;
        // If we're publishing, recalculate marks from quiz_questions
        if ($quiz->is_published) {
            $total = \App\Models\QuizQuestion::where('quiz_id', $quiz->id)->sum('marks');
            // Business rule: pass marks are set as one-third of total marks
            $pass = (int) round($total / 3);

            // Update model fields
            $quiz->total_marks = $total;
            $quiz->pass_marks = $pass;
        }

        // Persist changes to the quiz model
        $quiz->save();

        $action = $quiz->is_published ? 'published' : 'unpublished';
        $message = $quiz->is_published ? 'Quiz published successfully' : 'Quiz unpublished successfully';

        Log::info('Quiz publish toggled', [
            'quiz_id' => $quiz->id,
            'action' => $action,
            'is_published' => $quiz->is_published,
            'total_marks' => $quiz->total_marks ?? null,
            'pass_marks' => $quiz->pass_marks ?? null
        ]);

        return redirect()->route('quizzes.show', $quiz->id)->with('success', $message);
    }

    /**
     * Show a paginated list of attempts for the current user for this quiz.
     */
    public function resultIndex(Quiz $quiz)
    {
        $userId = Auth::id();
        if (! $userId) {
            return redirect()->route('quizzes.show', $quiz->id)->with('error', 'Please login to view results');
        }

        $attempts = \App\Models\Attempt::where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->orderByDesc('completed_at')
            ->paginate(15);

        return view('quizzes.result_index', compact('quiz', 'attempts'));
    }
}

