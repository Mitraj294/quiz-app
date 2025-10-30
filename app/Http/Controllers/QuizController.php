<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Quiz;
use App\Models\Topic;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

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
    private const QUESTION_TYPES = [
        1 => 'multiple_choice_single_answer',
        2 => 'multiple_choice_multiple_answer',
        3 => 'fill_the_blank',
    ];

    public function index(): View
    {
        $user = Auth::user();
        // Safely determine admin status. Use method_exists() to satisfy static analysis tools
        $isAdmin = Auth::check() && $user instanceof \App\Models\User && method_exists($user, 'isAdmin') && $user->isAdmin();

        if ($isAdmin) {
            $quizzes = Quiz::with('topics')->latest()->get();
        } else {
            // non-admins only see published quizzes
            $quizzes = Quiz::with('topics')->where('is_published', 1)->latest()->get();
        }

        return view('quizzes.index', compact('quizzes', 'isAdmin'));
    }

    public function create(): View
    {
        $topics = Topic::orderBy('name')->get();
        return view('quizzes.create', compact('topics'));
    }

    public function store(Request $request): RedirectResponse
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
        // Convert incoming local datetimes (from user's browser) to UTC before saving.
        $tz = $request->input('timezone') ?? config('app.timezone');

        $validFrom = null;
        if (! empty($validated['valid_from'])) {
            try {
                $validFrom = \Carbon\Carbon::parse($validated['valid_from'], $tz)->setTimezone('UTC')->toDateTimeString();
            } catch (\Exception $e) {
                $validFrom = $validated['valid_from'];
            }
        }

        $validUpto = null;
        if (! empty($validated['valid_upto'])) {
            try {
                $validUpto = \Carbon\Carbon::parse($validated['valid_upto'], $tz)->setTimezone('UTC')->toDateTimeString();
            } catch (\Exception $e) {
                $validUpto = $validated['valid_upto'];
            }
        }

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
            // if valid_from not provided, default to now() in UTC
            'valid_from' => $validFrom ?? now()->setTimezone('UTC')->toDateTimeString(),
            'valid_upto' => $validUpto ?? null,
            'time_between_attempts' => $validated['time_between_attempts'] ?? 0,
        ]);

        // Attach topic (avoid duplicate pivot entries)
        $quiz->topics()->syncWithoutDetaching([$topicId]);

        return redirect()->route('topics.show', $topicId)
            ->with('success', 'Quiz created successfully!');
    }

    public function edit(Quiz $quiz): View
    {
        $topics = Topic::orderBy('name')->get();
        $users = \App\Models\User::orderBy('name')->get();
        return view('quizzes.edit', compact('quiz', 'topics', 'users'));
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
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

        // If the browser provided a timezone, use it to convert incoming local datetimes to UTC
        $tz = $request->input('timezone') ?? config('app.timezone');

        $validFrom = null;
        if (array_key_exists('valid_from', $data) && ! empty($data['valid_from'])) {
            try {
                $validFrom = \Carbon\Carbon::parse($data['valid_from'], $tz)->setTimezone('UTC')->toDateTimeString();
            } catch (\Exception $e) {
                $validFrom = $data['valid_from'];
            }
        }

        $validUpto = null;
        if (array_key_exists('valid_upto', $data) && ! empty($data['valid_upto'])) {
            try {
                $validUpto = \Carbon\Carbon::parse($data['valid_upto'], $tz)->setTimezone('UTC')->toDateTimeString();
            } catch (\Exception $e) {
                $validUpto = $data['valid_upto'];
            }
        }

        $quiz->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'total_marks' => $data['total_marks'] ?? $quiz->total_marks,
            'pass_marks' => $data['pass_marks'] ?? $quiz->pass_marks,
            'max_attempts' => $data['max_attempts'] ?? $quiz->max_attempts,
            'is_published' => isset($data['is_published']) ? (int)$data['is_published'] : $quiz->is_published,
            'duration' => $data['duration'] ?? $quiz->duration,
            'valid_from' => $validFrom ?? ($data['valid_from'] ?? $quiz->valid_from),
            'valid_upto' => $validUpto ?? ($data['valid_upto'] ?? $quiz->valid_upto),
            'time_between_attempts' => $data['time_between_attempts'] ?? $quiz->time_between_attempts,
        ]);

        // Attach or sync topic if provided
        if (! empty($data['topic_id'])) {
            // Replace previous topic associations with the newly selected topic
            // (the UI select is single-choice). Use sync() so old topics are removed.
            $quiz->topics()->sync([$data['topic_id']]);
        }

        return redirect()->route('quizzes.show', $quiz->id)->with('success', 'Quiz updated successfully');
    }

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



    public function show(Quiz $quiz): View
    {
        // Fetch topics for this quiz (polymorphic pivot stores multiple model namespaces)
        $topicIds = DB::table('topicables')
            ->where('topicable_id', $quiz->id)
            ->whereIn('topicable_type', ['App\Models\Quiz', 'Harishdurga\LaravelQuiz\Models\Quiz'])
            ->pluck('topic_id');

        $topics = \App\Models\Topic::whereIn('id', $topicIds)->get();
        $quiz->setRelation('topics', $topics);

        // Load quiz_questions with their related question data and options
        $quiz->load(['questions.question.options']);

        // Prepare computed values that were previously in the Blade view
        $now = \Carbon\Carbon::now('UTC');

        // Questions counts
        $totalQuestions = $quiz->questions->count();
        $mandatoryCount = $quiz->questions->where('is_optional', false)->count();
        $optionalCount = $quiz->questions->where('is_optional', true)->count();

        // User attempts/retake info (computed in helper to reduce complexity)
        [$userAttempts, $attempts, $remainingSeconds, $canRetake] = $this->getAttemptStats($quiz, $now);

        // Computed marks and pass marks
        $computedTotalMarks = $quiz->questions->sum('marks');
        $computedPassMarks = (int) round($computedTotalMarks / 3);

        // Expiry
        $isExpired = false;
        if (! empty($quiz->valid_upto)) {
            try {
                $validUpto = \Carbon\Carbon::parse($quiz->valid_upto, 'UTC');
                if ($now->gt($validUpto)) {
                    $isExpired = true;
                }
            } catch (\Exception $e) {
                // ignore parse errors and treat as not expired
            }
        }

        if ($isExpired) {
            $canRetake = false;
        }

        $validUptoUtc = $quiz->valid_upto ? \Carbon\Carbon::parse($quiz->valid_upto)->setTimezone('UTC')->toIso8601String() : '';

        return view('quizzes.show', compact(
            'quiz',
            'totalQuestions',
            'mandatoryCount',
            'optionalCount',
            'userAttempts',
            'computedTotalMarks',
            'computedPassMarks',
            'isExpired',
            'validUptoUtc',
            'remainingSeconds',
            'canRetake',
            'attempts'
        ));
    }


    public function selectQuestions(Quiz $quiz): View
    {
        // Fetch topic IDs for this quiz
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

    public function createQuestion(Quiz $quiz): View
    {
        return view('quizzes.create_question', [
            'quiz' => $quiz,
            'questionTypes' => self::QUESTION_TYPES,
        ]);
    }

    public function editQuestion(Quiz $quiz, int $questionId): View
    {
        $question = \Harishdurga\LaravelQuiz\Models\Question::with('options', 'question_type')->findOrFail($questionId);
        $quizQuestion = \App\Models\QuizQuestion::where('quiz_id', $quiz->id)->where('question_id', $questionId)->first();

        $currentType = 1;
        if ($question->question_type && $question->question_type->name) {
            $currentType = array_search($question->question_type->name, self::QUESTION_TYPES, true) ?: 1;
        }

        return view('quizzes.edit_question', [
            'quiz' => $quiz,
            'question' => $question,
            'quizQuestion' => $quizQuestion,
            'questionTypes' => self::QUESTION_TYPES,
            'currentType' => $currentType,
        ]);
    }

    public function updateQuestion(Request $request, Quiz $quiz, int $questionId): RedirectResponse
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

    private function questionTypeName(int $type): string
    {
        return self::QUESTION_TYPES[$type] ?? 'Unknown';
    }

    private function persistQuestionOptions(int $questionId, int $type, array $options = [], array $correct = [], ?string $textAnswer = null): void
    {
        \Harishdurga\LaravelQuiz\Models\QuestionOption::where('question_id', $questionId)->delete();

        if (in_array($type, [1, 2], true)) {
            foreach ($options as $idx => $opt) {
                if ($opt === '' || $opt === null) {
                    continue;
                }

                \Harishdurga\LaravelQuiz\Models\QuestionOption::create([
                    'question_id' => $questionId,
                    'name' => $opt,
                    'is_correct' => in_array($idx, $correct, true),
                ]);
            }

            return;
        }

        if ($type === 3 && ! empty($textAnswer)) {
            \Harishdurga\LaravelQuiz\Models\QuestionOption::create([
                'question_id' => $questionId,
                'name' => $textAnswer,
                'is_correct' => true,
            ]);
        }
    }

    public function storeQuestion(Request $request, Quiz $quiz): RedirectResponse
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

        $questionTypeModel = \Harishdurga\LaravelQuiz\Models\QuestionType::firstOrCreate([
            'name' => self::QUESTION_TYPES[$data['question_type']] ?? 'Unknown'
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

        if (in_array($data['question_type'], [1, 2], true)) {
            $correct = $data['correct'] ?? [];
            foreach ($data['options'] ?? [] as $idx => $opt) {
                if ($opt === '' || $opt === null) {
                    continue;
                }

                \Harishdurga\LaravelQuiz\Models\QuestionOption::create([
                    'question_id' => $question->id,
                    'name' => $opt,
                    'is_correct' => in_array($idx, $correct, true),
                ]);
            }
        }

        if ($data['question_type'] === 3 && ! empty($data['text_answer'])) {
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

    public function attachQuestions(Request $request, Quiz $quiz): RedirectResponse
    {
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
            // validated
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed in attachQuestions', $e->errors());
            throw $e;
        }
        foreach ($data['question_ids'] as $questionId) {
            $questionData = [
                'marks' => $data['marks'][$questionId] ?? 1,
                'negative_marks' => $data['negative_marks'][$questionId] ?? 0,
                'is_optional' => $data['is_optional'][$questionId] ?? 0,
                'order' => 0,
            ];

            \App\Models\QuizQuestion::updateOrCreate(
                [
                    'quiz_id' => $quiz->id,
                    'question_id' => $questionId,
                ],
                $questionData
            );
        }

        return redirect()->route('quizzes.show', $quiz->id)
            ->with('success', 'Questions attached/updated to quiz successfully');
    }

    public function detachQuestion(Quiz $quiz, int $questionId): RedirectResponse
    {
        \App\Models\QuizQuestion::where('quiz_id', $quiz->id)
            ->where('question_id', $questionId)
            ->delete();

        return redirect()->back()->with('success', 'Question removed from quiz successfully');
    }

    public function destroy(Quiz $quiz): RedirectResponse
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

    public function publish(Request $request, Quiz $quiz): RedirectResponse
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

    public function resultIndex(Quiz $quiz): View|RedirectResponse
    {

        $userId = Auth::id();
        // If admin and user_id is provided, use that
        $authUser = Auth::user();
        // Use direct DB check for admin role to avoid static analysis/model issues
        $isAdmin = false;
        if ($authUser) {
            $isAdmin = DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('role_user.user_id', $authUser->id)
                ->where('roles.role', 'admin') // Change 'role' to your actual column name if different
                ->exists();
        }
        if ($isAdmin && request()->has('user_id')) {
            $userId = request()->input('user_id');
        }

        if (! $userId) {
            return redirect()->route('quizzes.show', $quiz->id)->with('error', 'Please login to view results');
        }

        $attempts = \App\Models\Attempt::where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->orderByDesc('completed_at')
            ->paginate(15);

        return view('quizzes.result_index', compact('quiz', 'attempts'));
    }

    /**
     * Compute attempt-related stats for the current authenticated user.
     * Returns array: [userAttempts, attempts, remainingSeconds, canRetake]
     */
    private function getAttemptStats(Quiz $quiz, \Carbon\Carbon $now): array
    {
        $userAttempts = 0;
        $attempts = 0;
        $remainingSeconds = 0;
        $canRetake = true;

        if (Auth::check()) {
            $userAttempts = \App\Models\Attempt::where('quiz_id', $quiz->id)
                ->where('user_id', Auth::id())
                ->whereNotNull('completed_at')
                ->count();

            $attempts = $userAttempts;

            $lastAttempt = \App\Models\Attempt::where('quiz_id', $quiz->id)
                ->where('user_id', Auth::id())
                ->whereNotNull('completed_at')
                ->orderByDesc('completed_at')
                ->first();

            if ($quiz->time_between_attempts && $lastAttempt) {
                try {
                    $lockUntil = \Carbon\Carbon::parse($lastAttempt->completed_at, 'UTC')->addMinutes($quiz->time_between_attempts);
                    if ($now->lt($lockUntil)) {
                        $canRetake = false;
                        $remainingSeconds = $now->diffInSeconds($lockUntil);
                    }
                } catch (\Exception $e) {
                    // ignore parse errors and allow retake
                }
            }
        }

        return [$userAttempts, $attempts, $remainingSeconds, $canRetake];
    }
}
