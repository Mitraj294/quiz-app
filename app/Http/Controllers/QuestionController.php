<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Topic;
use Harishdurga\LaravelQuiz\Models\Question as VendorQuestion;
use Harishdurga\LaravelQuiz\Models\QuestionOption as VendorOption;
use Illuminate\Support\Str;

class QuestionController extends Controller
{
    public function create(Topic $topic)
    {
        // Provide the topic and available question types
        $questionTypes = [
            1 => 'Multiple Choice (Single Answer)',
            2 => 'Multiple Choice (Multiple Answers)',
            3 => 'Text / Short Answer'
        ];

        return view('questions.create', compact('topic', 'questionTypes'));
    }

    public function store(Request $request, Topic $topic)
    {
        $data = $request->validate([
            'question_type' => 'required|in:1,2,3',
            'question_text' => 'required|string',
            'options' => 'array',
            'options.*' => 'nullable|string',
            'correct' => 'array',
            'correct.*' => 'nullable|integer',
            'text_answer' => 'nullable|string',
        ]);

        // Map numeric type to human-friendly name and ensure the question_type exists
        $typeMap = [
            1 => 'Multiple Choice (Single Answer)',
            2 => 'Multiple Choice (Multiple Answers)',
            3 => 'Text / Short Answer',
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
            ]);

            // Attach to topic via topicable morph
            $topic->questions()->attach($question->id);

            // If MCQ types, store options and mark correct ones
            if (in_array($data['question_type'], [1,2])) {
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

            // If text/short answer, store the answer as a correct option (package has no explicit text-answer column)
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
}
