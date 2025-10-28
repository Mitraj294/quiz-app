<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Topic;
use Harishdurga\LaravelQuiz\Models\Question as VendorQuestion;
use Harishdurga\LaravelQuiz\Models\QuestionOption as VendorOption;
use Illuminate\Support\Str;

class QuestionController extends Controller
{
    private const RULE_NULLABLE_STRING = 'nullable|string';

    /**
     * Show create form for a new question under a topic
     */
    public function create(Topic $topic)
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
     */
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

        $typeMap = [
            1 => 'multiple_choice_single_answer',
            2 => 'multiple_choice_multiple_answer',
            3 => 'fill_the_blank',
        ];

        $typeName = $typeMap[$data['question_type']] ?? 'Unknown';

        $questionTypeModel = \Harishdurga\LaravelQuiz\Models\QuestionType::firstOrCreate([
            'name' => $typeName,
        ]);

        // Wrap in transaction to ensure atomicity
        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $topic, $questionTypeModel) {
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
                $correct = $data['correct'] ?? [];
                if (! empty($data['options'])) {
                    foreach ($data['options'] as $idx => $opt) {
                        if (! empty($opt)) {
                            VendorOption::create([
                                'question_id' => $question->id,
                                'name' => $opt,
                                'is_correct' => in_array($idx, $correct),
                            ]);
                        }
                    }
                }
            }

            // If text/short answer, store the answer as a correct option
            if ($data['question_type'] == 3 && ! empty($data['text_answer'])) {
                VendorOption::create([
                    'question_id' => $question->id,
                    'name' => $data['text_answer'],
                    'is_correct' => true,
                ]);
            }
        });

        return redirect()->route('topics.show', $topic->id)->with('success', 'Question added successfully');
    }


    /**
     * Show the form for editing a question
     */
    public function edit($questionId)
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
            3 => 'fill_the_blank',
        ];

        $typeName = $typeMap[$data['question_type']] ?? 'Unknown';
        $questionTypeModel = \Harishdurga\LaravelQuiz\Models\QuestionType::firstOrCreate([
            'name' => $typeName,
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $questionId, $questionTypeModel) {
            $question = VendorQuestion::findOrFail($questionId);

            $question->update([
                'name' => $data['question_text'],
                'question_type_id' => $questionTypeModel->id,
                'media_url' => $data['media_url'] ?? null,
                'media_type' => $data['media_type'] ?? null,
            ]);

            VendorOption::where('question_id', $questionId)->delete();

            if (in_array($data['question_type'], [1, 2])) {
                $correct = $data['correct'] ?? [];
                if (! empty($data['options'])) {
                    foreach ($data['options'] as $idx => $opt) {
                        if (! empty($opt)) {
                            VendorOption::create([
                                'question_id' => $questionId,
                                'name' => $opt,
                                'is_correct' => in_array($idx, $correct),
                            ]);
                        }
                    }
                }
            }

            if ($data['question_type'] == 3 && ! empty($data['text_answer'])) {
                VendorOption::create([
                    'question_id' => $questionId,
                    'name' => $data['text_answer'],
                    'is_correct' => true,
                ]);
            }
        });

        $quizId = \Illuminate\Support\Facades\DB::table('quiz_questions')
            ->where('question_id', $questionId)
            ->value('quiz_id');

        if ($quizId) {
            \Illuminate\Support\Facades\Log::info('Question updated and linked to quiz, redirecting to select page', ['question_id' => $questionId, 'quiz_id' => $quizId]);
            return redirect()->route('quizzes.questions.select', $quizId)->with('success', 'Question updated successfully');
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

            VendorOption::where('question_id', $questionId)->delete();

            \Illuminate\Support\Facades\DB::table('topicables')
                ->where('topicable_id', $questionId)
                ->whereIn('topicable_type', ['Harishdurga\\\\LaravelQuiz\\\\Models\\\\Question', 'App\\\\Models\\\\Question'])
                ->delete();

            \Illuminate\Support\Facades\DB::table('quiz_questions')
                ->where('question_id', $questionId)
                ->delete();

            $question->delete();
        });

        return redirect()->back()->with('success', 'Question deleted successfully');
    }
}
