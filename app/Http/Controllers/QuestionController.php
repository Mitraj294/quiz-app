<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Topic;
use Harishdurga\LaravelQuiz\Models\Question as VendorQuestion;
use Harishdurga\LaravelQuiz\Models\QuestionOption as VendorOption;
use Harishdurga\LaravelQuiz\Models\QuestionType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * QuestionController
 * 
 * Handles CRUD operations for questions within topics.
 */
class QuestionController extends Controller
{
    private const RULE_NULLABLE_STRING = 'nullable|string';

    /**
     * Show create form for a new question under a topic
     *
     * @param Topic $topic
     * @return View
     */
    public function create(Topic $topic): View
    {
        $questionTypes = [
            1 => 'multiple_choice_single_answer',
            2 => 'multiple_choice_multiple_answer',
            3 => 'fill_the_blank',
        ];

        return view('questions.create', compact('topic', 'questionTypes'));
    }

    /**
     * Store a new question and attach to topic
     *
     * @param Request $request
     * @param Topic $topic
     * @return RedirectResponse
     */
    public function store(Request $request, Topic $topic): RedirectResponse
    {
        $data = $this->validateQuestionData($request);
        $questionTypeModel = $this->getQuestionType($data['question_type']);

        DB::transaction(function () use ($data, $topic, $questionTypeModel) {
            $question = $this->createQuestion($data, $questionTypeModel);
            $topic->questions()->attach($question->id);
            $this->storeQuestionOptions($question->id, $data);
        });

        return redirect()->route('topics.show', $topic->id)->with('success', 'Question added successfully');
    }


    /**
     * Show the form for editing a question
     *
     * @param int $questionId
     * @return View
     */
    public function edit($questionId): View
    {
        $question = VendorQuestion::with(['options', 'question_type'])->findOrFail($questionId);

        $typeMap = [
            'multiple_choice_single_answer' => 1,
            'multiple_choice_multiple_answer' => 2,
            'fill_the_blank' => 3,
        ];

        $questionTypes = [
            1 => 'multiple_choice_single_answer',
            2 => 'multiple_choice_multiple_answer',
            3 => 'fill_the_blank',
        ];

        $currentType = 1;
        if ($question->relationLoaded('question_type') && $question->question_type) {
            $currentType = $typeMap[$question->question_type->name] ?? 1;
        }

        return view('questions.edit', compact('question', 'questionTypes', 'currentType'));
    }

    /**
     * Update a question
     *
     * @param Request $request
     * @param int $questionId
     * @return RedirectResponse
     */
    public function update(Request $request, $questionId): RedirectResponse
    {
        $data = $this->validateQuestionData($request);
        $questionTypeModel = $this->getQuestionType($data['question_type']);

        DB::transaction(function () use ($data, $questionId, $questionTypeModel) {
            $question = VendorQuestion::findOrFail($questionId);

            $question->update([
                'name' => $data['question_text'],
                'question_type_id' => $questionTypeModel->id,
                'media_url' => $data['media_url'] ?? null,
                'media_type' => $data['media_type'] ?? null,
            ]);

            VendorOption::where('question_id', $questionId)->delete();
            $this->storeQuestionOptions($questionId, $data);
        });

        $quizId = DB::table('quiz_questions')
            ->where('question_id', $questionId)
            ->value('quiz_id');

        if ($quizId) {
            Log::info('Question updated and linked to quiz, redirecting to select page', ['question_id' => $questionId, 'quiz_id' => $quizId]);
            return redirect()->route('quizzes.questions.select', $quizId)->with('success', 'Question updated successfully');
        }

        Log::info('Question updated (no related quiz found)', ['question_id' => $questionId]);
        return redirect()->back()->with('success', 'Question updated successfully');
    }

    /**
     * Delete a question
     *
     * @param int $questionId
     * @return RedirectResponse
     */
    public function destroy($questionId): RedirectResponse
    {
        DB::transaction(function () use ($questionId) {
            $question = VendorQuestion::findOrFail($questionId);

            VendorOption::where('question_id', $questionId)->delete();

            DB::table('topicables')
                ->where('topicable_id', $questionId)
                ->whereIn('topicable_type', ['Harishdurga\\LaravelQuiz\\Models\\Question', 'App\\Models\\Question'])
                ->delete();

            DB::table('quiz_questions')
                ->where('question_id', $questionId)
                ->delete();

            $question->delete();
        });

        return redirect()->back()->with('success', 'Question deleted successfully');
    }

    /**
     * Validate question data from request
     *
     * @param Request $request
     * @return array
     */
    private function validateQuestionData(Request $request): array
    {
        return $request->validate([
            'question_type' => 'required|in:1,2,3',
            'question_text' => 'required|string',
            'options' => 'array',
            'options.*' => self::RULE_NULLABLE_STRING,
            'correct' => 'array',
            'correct.*' => 'nullable|integer',
            'text_answer' => self::RULE_NULLABLE_STRING,
            'media_url' => self::RULE_NULLABLE_STRING,
            'media_type' => self::RULE_NULLABLE_STRING,
        ]);
    }

    /**
     * Get or create question type model
     *
     * @param int $type
     * @return QuestionType
     */
    private function getQuestionType(int $type): QuestionType
    {
        $typeMap = [
            1 => 'multiple_choice_single_answer',
            2 => 'multiple_choice_multiple_answer',
            3 => 'fill_the_blank',
        ];

        $typeName = $typeMap[$type] ?? 'Unknown';

        return QuestionType::firstOrCreate(['name' => $typeName]);
    }

    /**
     * Create a question record
     *
     * @param array $data
     * @param QuestionType $questionTypeModel
     * @return VendorQuestion
     */
    private function createQuestion(array $data, QuestionType $questionTypeModel): VendorQuestion
    {
        return VendorQuestion::create([
            'name' => $data['question_text'],
            'question_type_id' => $questionTypeModel->id,
            'media_url' => $data['media_url'] ?? null,
            'media_type' => $data['media_type'] ?? null,
        ]);
    }

    /**
     * Store question options based on question type
     *
     * @param int $questionId
     * @param array $data
     * @return void
     */
    private function storeQuestionOptions(int $questionId, array $data): void
    {
        if (in_array($data['question_type'], [1, 2])) {
            $this->storeMcqOptions($questionId, $data['options'] ?? [], $data['correct'] ?? []);
        }

        if ($data['question_type'] == 3 && !empty($data['text_answer'])) {
            $this->storeTextAnswerOption($questionId, $data['text_answer']);
        }
    }

    /**
     * Store MCQ options for a given question id.
     *
     * @param int $questionId
     * @param array $options
     * @param array $correct
     * @return void
     */
    private function storeMcqOptions(int $questionId, array $options, array $correct): void
    {
        foreach ($options as $idx => $opt) {
            if (!empty($opt)) {
                VendorOption::create([
                    'question_id' => $questionId,
                    'name' => $opt,
                    'is_correct' => in_array($idx, $correct),
                ]);
            }
        }
    }

    /**
     * Store a text/short-answer option as the correct option for the question.
     *
     * @param int $questionId
     * @param string $text
     * @return void
     */
    private function storeTextAnswerOption(int $questionId, string $text): void
    {
        VendorOption::create([
            'question_id' => $questionId,
            'name' => $text,
            'is_correct' => true,
        ]);
    }
}
