<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Topic;
use Harishdurga\LaravelQuiz\Models\Question as VendorQuestion;
use Harishdurga\LaravelQuiz\Models\QuestionOption as VendorOption;
use Illuminate\Support\Str;

class QuestionController extends Controller
{
    private const TEXT_SHORT_ANSWER = 'fill_the_blank';
    private const RULE_NULLABLE_STRING = 'nullable|string';

    public function create(Topic $topic)
    {
        // Provide the topic and available question types
        $questionTypes = [
            1 => 'multiple_choice_single_answer',
            2 => 'multiple_choice_multiple_answer',
            3 => self::TEXT_SHORT_ANSWER
        ];

        return view('questions.create', compact('topic', 'questionTypes'));
    }

    public function store(Request $request, Topic $topic)
    {
        $data = $request->validate([
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

        // Map numeric type to human-friendly name and ensure the question_type exists
        $typeMap = [
            1 => 'multiple_choice_single_answer',
            2 => 'multiple_choice_multiple_answer',
            3 => self::TEXT_SHORT_ANSWER,
        ];

        $typeName = $typeMap[$data['question_type']] ?? 'Unknown';

        // The package maps the attribute `question_type` to DB column `name` via an accessor,
        // so we must create/lookup by the actual column name `name`.
        $questionTypeModel = \Harishdurga\LaravelQuiz\Models\QuestionType::firstOrCreate([
            'name' => $typeName,
        ]);

        // Wrap in transaction to ensure atomicity
        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $topic, $questionTypeModel) {
            // Create question using package model (takes advantage of attribute mapping)
            $question = VendorQuestion::create([
                'name' => $data['question_text'],
                'question_type_id' => $questionTypeModel->id,
                'media_url' => $data['media_url'] ?? null,
                'media_type' => $data['media_type'] ?? null,
            ]);

            // Attach to topic via topicable morph
            $topic->questions()->attach($question->id);

            // If MCQ types, store options and mark correct ones
            if (in_array($data['question_type'], [1, 2])) {
                $this->storeMcqOptions($question->id, $data['options'] ?? [], $data['correct'] ?? []);
            }

            // If text/short answer, store the answer as a correct option (package has no explicit text-answer column)
            if ($data['question_type'] == 3 && ! empty($data['text_answer'])) {
                $this->storeTextAnswerOption($question->id, $data['text_answer']);
            }
        });

        return redirect()->route('topics.show', $topic->id)->with('success', 'Question added successfully');
    }

    /**
     * Store MCQ options for a given question id.
     */
    private function storeMcqOptions(int $questionId, array $options, array $correct): void
    {
        foreach ($options as $idx => $opt) {
            if (! empty($opt)) {
                VendorOption::create([
                    'question_id' => $questionId,
                    'name' => $opt,
                    'is_correct' => in_array((int) $idx, array_map('intval', $correct)),
                ]);
            }
        }
    }

    /**
     * Store a text/short-answer option as the correct option for the question.
     */
    private function storeTextAnswerOption(int $questionId, string $text): void
    {
        VendorOption::create([
            'question_id' => $questionId,
            'name' => $text,
            'is_correct' => true,
        ]);
    }

    /**
     * Show the form for editing a question
     */
    public function edit($questionId)
    {
        // The vendor model defines the relationship as `question_type()` (snake_case)
        // so eager-load that relationship. Guard access in case the relation is missing.
        $question = VendorQuestion::with(['options', 'question_type'])->findOrFail($questionId);

        // Map question type to the numeric format expected by the form
        $typeMap = [
            'multiple_choice_single_answer' => 1,
            'multiple_choice_multiple_answer' => 2,
            'fill_the_blank' => 3,
        ];

        $questionTypes = [
            1 => 'multiple_choice_single_answer',
            2 => 'multiple_choice_multiple_answer',
            3 => self::TEXT_SHORT_ANSWER
        ];

        $currentType = 1;
        if ($question->relationLoaded('question_type') && $question->question_type) {
            $currentType = $typeMap[$question->question_type->name] ?? 1;
        }

        return view('questions.edit', compact('question', 'questionTypes', 'currentType'));
    }

    /**
     * Update a question
     */
    public function update(Request $request, $questionId)
    {
        $data = $request->validate([
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

        $typeMap = [
            1 => 'multiple_choice_single_answer',
            2 => 'multiple_choice_multiple_answer',
            3 => self::TEXT_SHORT_ANSWER,
        ];

        $typeName = $typeMap[$data['question_type']] ?? 'Unknown';
        $questionTypeModel = \Harishdurga\LaravelQuiz\Models\QuestionType::firstOrCreate([
            'name' => $typeName,
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $questionId, $questionTypeModel) {
            $question = VendorQuestion::findOrFail($questionId);

            // Update question
            $question->update([
                'name' => $data['question_text'],
                'question_type_id' => $questionTypeModel->id,
                'media_url' => $data['media_url'] ?? null,
                'media_type' => $data['media_type'] ?? null,
            ]);

            // Delete existing options
            VendorOption::where('question_id', $questionId)->delete();

            // Re-create options
            if (in_array($data['question_type'], [1, 2])) {
                $this->storeMcqOptions($questionId, $data['options'] ?? [], $data['correct'] ?? []);
            }

            if ($data['question_type'] == 3 && ! empty($data['text_answer'])) {
                $this->storeTextAnswerOption($questionId, $data['text_answer']);
            }
        });

        // Find if this question is attached to any quiz via quiz_questions pivot
        $quizId = \Illuminate\Support\Facades\DB::table('quiz_questions')
            ->where('question_id', $questionId)
            ->value('quiz_id');

        if ($quizId) {
            // Log and redirect to the quiz's select questions page so admin can continue managing quiz questions
            \Illuminate\Support\Facades\Log::info('Question updated and linked to quiz, redirecting to select page', ['question_id' => $questionId, 'quiz_id' => $quizId]);
            return redirect()->route('quizzes.questions.select', $quizId)
                ->with('success', 'Question updated successfully. Returning to quiz question selection.');
        }

        \Illuminate\Support\Facades\Log::info('Question updated (no related quiz found)', ['question_id' => $questionId]);
        return redirect()->back()->with('success', 'Question updated successfully');
    }

    /**
     * Delete a question
     */
    public function destroy($questionId)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($questionId) {
            $question = VendorQuestion::findOrFail($questionId);

            // Delete options first
            VendorOption::where('question_id', $questionId)->delete();

            // Delete topicable relationships
            \Illuminate\Support\Facades\DB::table('topicables')
                ->where('topicable_type', 'LIKE', '%Question%')
                ->where('topicable_id', $questionId)
                ->delete();

            // Delete quiz_questions relationships
            \Illuminate\Support\Facades\DB::table('quiz_questions')
                ->where('question_id', $questionId)
                ->delete();

            // Delete the question
            $question->delete();
        });

        return redirect()->back()->with('success', 'Question deleted successfully');
    }
}
