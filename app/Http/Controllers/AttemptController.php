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

        // Find an existing in-progress attempt (no completed_at) for this user and quiz
        $attempt = Attempt::where('quiz_id', $quiz->id)
            ->where('user_id', Auth::id())
            ->whereNull('completed_at')
            ->first();

        if (! $attempt) {
            $attempt = Attempt::create([
                'user_id' => Auth::id(),
                'quiz_id' => $quiz->id,
                'score' => 0.00,
                'passed' => 0,
                'completed_at' => null,
            ]);
        }

        return view($viewName, compact('quiz', 'attempt'));
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


            // Use provided attempt if available (created when user clicked Start)
            $attemptId = $request->input('attempt_id');
            if ($attemptId) {
                $attempt = Attempt::where('id', $attemptId)
                    ->where('quiz_id', $quiz->id)
                    ->where('user_id', $user->id)
                    ->first();
            }

            // If an attempt was not provided or not found, create a new one
            if (empty($attempt)) {
                $attempt = Attempt::create([
                    'user_id' => $user->id,
                    'quiz_id' => $quiz->id,
                    'score' => 0.00,
                    'passed' => 0,
                    // completed_at will be set below when submission finishes
                    'completed_at' => null,
                ]);
            }

            // Mark attempt as completed now (submission time)
            $attempt->completed_at = now();
            $attempt->save();

            Log::info('Created attempt record', ['attempt_id' => $attempt->id]);

            // Process answers and calculate total score in helper (helper persists attempt)
            $this->processAttemptAndCalculateScore($quiz, $attempt, $answers);

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

    /**
     * Show a detailed result for a specific attempt.
     */
    public function show(Quiz $quiz, \App\Models\Attempt $attempt)
    {
        // Ensure attempt belongs to quiz
        if ($attempt->quiz_id !== $quiz->id) {
            abort(404);
        }

        // Eager load attempt answers and related question/option data
        $attempt->load(['answers.option', 'answers.question.options', 'answers.question.question_type']);

        // Ensure quiz questions and their question relations are loaded (includes marks/negative/is_optional)
        $quiz->load(['questions.question.options', 'questions.question.question_type']);

        // Group answers by question_id for easy lookup in the view
        $answersByQuestion = $attempt->answers->groupBy('question_id');

        return view('quizzes.result_show', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'answersByQuestion' => $answersByQuestion,
        ]);
    }
   
      /**
     * Persist answers for an attempt and calculate total score.
     * Returns the total earned score (float).
     */
    private function processAttemptAndCalculateScore(Quiz $quiz, Attempt $attempt, array $answers): float
    {
        $totalScore = 0.0;

        $negSettings = $quiz->negative_marking_settings ?? [];
        $negEnabled = $negSettings['enable_negative_marks'] ?? true;

        foreach ($quiz->questions as $quizQuestion) {
            $question = $quizQuestion->question;
            $questionId = $question->id;
            $userAnswer = $answers[$questionId] ?? null;

            if ($question->question_type->name === 'fill_the_blank') {
                $earned = $this->processFillBlankQuestion($quizQuestion, $attempt, $userAnswer, $negEnabled);
            } else {
                $earned = $this->processMcqQuestion($quizQuestion, $attempt, $userAnswer, $negEnabled);
            }

            $totalScore += $earned;
        }

        // Persist total and passed flag
        $attempt->score = round($totalScore, 2);
        $passMarks = floatval($quiz->pass_marks ?? $quiz->pass_mark ?? 0);
        $attempt->passed = ($attempt->score >= $passMarks) ? 1 : 0;
        $attempt->save();

        return $attempt->score;
    }

    /**
     * Handle a fill-in-the-blank question: persist answer and return earned marks (float).
     */
    private function processFillBlankQuestion($quizQuestion, Attempt $attempt, $userAnswer, bool $negEnabled): float
    {
        $question = $quizQuestion->question;
        $questionId = $question->id;
        $marks = floatval($quizQuestion->marks ?? 0);
        $neg = floatval($quizQuestion->negative_marks ?? 0);

        AttemptAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $questionId,
            'option_id' => null,
            'answer_text' => $userAnswer,
        ]);

        $correctAnswers = $question->options->where('is_correct', 1)->pluck('option')->map(fn($v) => trim((string)$v))->filter()->values()->all();
        $submitted = trim((string)($userAnswer ?? ''));
        $isCorrect = ($submitted !== '') && collect($correctAnswers)->map(fn($v) => strtolower($v))->contains(strtolower($submitted));

        if ($isCorrect) {
            $earned = $marks;
        } elseif ($negEnabled && $neg > 0) {
            $earned = -1.0 * $neg;
        } else {
            $earned = 0.0;
        }

        Log::info('Saved fill-in answer', ['attempt_id' => $attempt->id, 'question_id' => $questionId, 'answer_text' => $userAnswer, 'earned' => $earned]);

        return $earned;
    }

    /**
     * Handle MCQ (single/multiple) question: persist answers and return earned marks (float).
     */
    private function processMcqQuestion($quizQuestion, Attempt $attempt, $userAnswer, bool $negEnabled): float
    {
        $question = $quizQuestion->question;
        $questionId = $question->id;
        $marks = floatval($quizQuestion->marks ?? 0);
        $neg = floatval($quizQuestion->negative_marks ?? 0);

        if (is_array($userAnswer)) {
            $selected = array_map('intval', $userAnswer);
        } elseif ($userAnswer) {
            $selected = [intval($userAnswer)];
        } else {
            $selected = [];
        }

        if (count($selected) > 0) {
            foreach ($selected as $optionId) {
                $opt = $question->options->firstWhere('id', $optionId);

                AttemptAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'question_id' => $questionId,
                    'option_id' => $optionId,
                    'answer_text' => $opt ? trim((string)$opt->option) : '',
                ]);
            }
        } else {
            Log::info('No answer provided for question', ['attempt_id' => $attempt->id, 'question_id' => $questionId]);
        }

        $correctOptionIds = $question->options->where('is_correct', 1)->pluck('id')->map(fn($v) => intval($v))->all();
        $correctCount = count($correctOptionIds);
        $selectedCorrect = count(array_intersect($correctOptionIds, $selected));
        $selectedIncorrect = max(0, count($selected) - $selectedCorrect);

        if ($correctCount === 0) {
            $earned = 0.0;
        } else {
            $proportion = $selectedCorrect / $correctCount;
            $earned = $proportion * $marks;

            if ($negEnabled && $selectedIncorrect > 0 && $neg > 0) {
                $earned -= ($neg * $selectedIncorrect);
            }
        }

        Log::info('Saved MCQ answer', ['attempt_id' => $attempt->id, 'question_id' => $questionId, 'selected' => $selected, 'earned' => $earned]);

        return $earned;
    }

}
