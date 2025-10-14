<?php

namespace Database\Seeders;

use Harishdurga\LaravelQuiz\Models\Topic;
use Harishdurga\LaravelQuiz\Models\Question;
use Harishdurga\LaravelQuiz\Models\QuestionOption;
use Harishdurga\LaravelQuiz\Models\Quiz;
use Harishdurga\LaravelQuiz\Models\QuestionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure there's at least one question type
        $qt = QuestionType::first() ?? QuestionType::create(['name' => 'multiple_choice_single_answer']);

        // Create a topic
        $topic = Topic::create(['topic' => 'Demo Topic', 'slug' => 'demo-topic']);

        // Create a question
        $question = Question::create(['question' => 'What is 2 + 2?', 'question_type_id' => $qt->id]);

        // Create options
            QuestionOption::create(['question_id' => $question->id, 'option' => '3', 'is_correct' => false]);
            QuestionOption::create(['question_id' => $question->id, 'option' => '4', 'is_correct' => true]);

        // Create a quiz
        $quiz = Quiz::create([ 'title' => 'Demo Quiz', 'slug' => 'demo-quiz', 'description' => 'A simple demo quiz' ]);

        // Insert into quiz_questions pivot table
            DB::table('quiz_questions')->insert([
            'quiz_id' => $quiz->id,
            'question_id' => $question->id,
            'marks' => 1,
            'negative_marks' => 0,
            'is_optional' => 0,
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert topicable relation for quiz
            DB::table('topicables')->insert([
            'topic_id' => $topic->id,
            'topicable_id' => $quiz->id,
            'topicable_type' => Quiz::class,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
