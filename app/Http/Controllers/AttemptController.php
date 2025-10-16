<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttemptController extends Controller
{
    /**
     * Show the quiz-taking page for users
     */
    public function start(Quiz $quiz)
    {
        // Eager load questions with options, question_type, and pivot data
        $quiz->load(['questions.question.options', 'questions.question.question_type']);

        // Check if quiz has questions
        if ($quiz->questions->count() === 0) {
            return redirect()->route('quizzes.show', $quiz->id)
                ->with('error', 'This quiz has no questions yet.');
        }

        // Check if user has reached max attempts
        if ($quiz->max_attempts > 0) {
            $attemptCount = Attempt::where('quiz_id', $quiz->id)
                ->where('user_id', Auth::id())
                ->count();

            if ($attemptCount >= $quiz->max_attempts) {
                return redirect()->route('quizzes.index')
                    ->with('error', ' You have already attempted this quiz. You can only attempt this quiz ' . $quiz->max_attempts . ' time(s). Please try other quizzes.');
            }
        }

        // Prefer `quizzes.take` view if present; otherwise fallback to `quizzes.attempt`.
        $viewName = view()->exists('quizzes.take') ? 'quizzes.take' : 'quizzes.attempt';

        return view($viewName, compact('quiz'));
    }

    /**
     * Submit quiz attempt and calculate score
     */
    public function submit(Request $request, Quiz $quiz)
    {
        $request->validate([
            'answers' => 'nullable|array',
            'answers.*' => 'nullable',
        ]);

        $user = Auth::user();
        $answers = $request->input('answers', []);

        DB::beginTransaction();
        try {
            // Load questions with relationships
            $quiz->load(['questions.question.options', 'questions.question.question_type']);

            Log::info('Submitting quiz attempt', [
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'request_answers_count' => is_array($answers) ? count($answers) : 0,
            ]);

            // Create attempt record (no scoring for now)
            // Ensure DB non-null constraints are respected by providing defaults
            $attempt = Attempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score' => 0.00,
                'passed' => 0,
                'completed_at' => now(),
            ]);

            Log::info('Created attempt record', ['attempt_id' => $attempt->id]);

            // Save each provided answer without scoring
            foreach ($quiz->questions as $quizQuestion) {
                $question = $quizQuestion->question;
                $questionId = $question->id;
                $userAnswer = $answers[$questionId] ?? null;

                if ($question->question_type->name === 'fill_the_blank') {
                    AttemptAnswer::create([
                        'quiz_attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                        'option_id' => null,
                        'answer_text' => $userAnswer,
                    ]);

                    Log::info('Saved fill-in answer', ['attempt_id' => $attempt->id, 'question_id' => $questionId, 'answer_text' => $userAnswer]);
                } else {
                    if (is_array($userAnswer)) {
                        foreach ($userAnswer as $optionId) {
                            AttemptAnswer::create([
                                'quiz_attempt_id' => $attempt->id,
                                'question_id' => $questionId,
                                'option_id' => $optionId,
                                'answer_text' => null,
                            ]);

                            Log::info('Saved MCQ answer (multiple)', ['attempt_id' => $attempt->id, 'question_id' => $questionId, 'option_id' => $optionId]);
                        }
                    } elseif ($userAnswer) {
                        AttemptAnswer::create([
                            'quiz_attempt_id' => $attempt->id,
                            'question_id' => $questionId,
                            'option_id' => $userAnswer,
                            'answer_text' => null,
                        ]);

                        Log::info('Saved MCQ answer (single)', ['attempt_id' => $attempt->id, 'question_id' => $questionId, 'option_id' => $userAnswer]);
                    } else {
                        Log::info('No answer provided for question', ['attempt_id' => $attempt->id, 'question_id' => $questionId]);
                    }
                }
            }

            DB::commit();

            Log::info('Quiz attempt submitted (no scoring)', [
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'attempt_id' => $attempt->id,
            ]);

            return redirect()->route('quizzes.index')
                ->with('success', 'Thank you! Your quiz has been submitted successfully. (Attempt ID: ' . $attempt->id . ')');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quiz submission failed', [
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('quizzes.show', $quiz->id)
                ->with('error', 'An error occurred while submitting your quiz. Please try again.');
        }
    }

   
}
