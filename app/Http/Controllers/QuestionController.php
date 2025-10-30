<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Harishdurga\LaravelQuiz\Models\Question as VendorQuestion;
use Harishdurga\LaravelQuiz\Models\QuestionOption as VendorOption;
use Harishdurga\LaravelQuiz\Models\QuestionType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class QuestionController extends Controller
{
    private const RULE_NULLABLE_STRING = 'nullable|string';

    private const QUESTION_TYPES = [
        1 => 'multiple_choice_single_answer',
        2 => 'multiple_choice_multiple_answer',
        3 => 'fill_the_blank',
    ];

    public function edit(int $questionId): View
    {
        $question = VendorQuestion::with(['options', 'question_type'])->findOrFail($questionId);

        $currentType = 1;
        if ($question->question_type && $question->question_type->name) {
            $currentType = array_search($question->question_type->name, self::QUESTION_TYPES, true) ?: 1;
        }

        return view('questions.edit', [
            'question' => $question,
            'questionTypes' => self::QUESTION_TYPES,
            'currentType' => $currentType,
        ]);
    }

    public function update(Request $request, int $questionId): RedirectResponse
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

    public function destroy(int $questionId): RedirectResponse
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

    private function getQuestionType(int $type): QuestionType
    {
        $typeName = self::QUESTION_TYPES[$type] ?? 'Unknown';
        return QuestionType::firstOrCreate(['name' => $typeName]);
    }

    private function storeQuestionOptions(int $questionId, array $data): void
    {
        if (in_array($data['question_type'], [1, 2], true)) {
            $this->storeMcqOptions($questionId, $data['options'] ?? [], $data['correct'] ?? []);
            return;
        }

        if ($data['question_type'] === 3 && !empty($data['text_answer'])) {
            $this->storeTextAnswerOption($questionId, $data['text_answer']);
        }
    }

    private function storeMcqOptions(int $questionId, array $options, array $correct): void
    {
        foreach ($options as $idx => $opt) {
            if ($opt === '' || $opt === null) {
                continue;
            }

            VendorOption::create([
                'question_id' => $questionId,
                'name' => $opt,
                'is_correct' => in_array($idx, $correct, true),
            ]);
        }
    }

    private function storeTextAnswerOption(int $questionId, string $text): void
    {
        VendorOption::create([
            'question_id' => $questionId,
            'name' => $text,
            'is_correct' => true,
        ]);
    }
}
