<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Quiz;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AttemptController extends Controller
{
    public function start(Quiz $quiz): View|RedirectResponse
    {
        $quiz->load(['questions.question.options', 'questions.question.question_type']);

        $redirect = null;
        if (!$redirect) {
            $redirect = $this->checkNoQuestions($quiz);
        }
        if (!$redirect) {
            $redirect = $this->checkMaxAttempts($quiz);
        }
        if (!$redirect) {
            $redirect = $this->checkCooldown($quiz);
        }

        if ($redirect) {
            return $redirect;
        }

        $viewName = view()->exists('quizzes.take') ? 'quizzes.take' : 'quizzes.attempt';
        // Always create a new attempt when starting/retaking
        $attempt = Attempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $quiz->id,
            'score' => 0.00,
            'passed' => 0,
            'completed_at' => null,
        ]);

        // Prepare view-level computed values (moved from Blade):
        // remainingSeconds: prefer attempt->ends_at if available (legacy), else quiz duration
        $remainingSeconds = $quiz->duration > 0 ? ($quiz->duration * 60) : 0;
        if (isset($attempt->ends_at) && ! empty($attempt->ends_at)) {
            try {
                $remainingSeconds = max(0, Carbon::parse($attempt->ends_at, 'UTC')->diffInSeconds(Carbon::now('UTC')));
            } catch (\Exception $e) {
                // ignore and keep duration-based remainingSeconds
            }
        }

        $computedTotalMarks = $quiz->questions->sum('marks');
        $computedPassMarks = (int) round($computedTotalMarks / 3);
        $totalQuestions = $quiz->questions->count();

        return view($viewName, compact('quiz', 'attempt', 'remainingSeconds', 'computedTotalMarks', 'computedPassMarks', 'totalQuestions'));
    }

    private function checkNoQuestions(Quiz $quiz): ?RedirectResponse
    {
        if ($quiz->questions->isEmpty()) {
            return redirect()->route('quizzes.show', $quiz->id)
                ->with('error', 'This quiz has no questions yet.');
        }
        return null;
    }

    private function checkMaxAttempts(Quiz $quiz): ?RedirectResponse
    {
        if ($quiz->max_attempts > 0) {
            $attemptCount = Attempt::where('quiz_id', $quiz->id)
                ->where('user_id', Auth::id())
                ->count();
            if ($attemptCount >= $quiz->max_attempts) {
                return redirect()->route('quizzes.index')
                    ->with('error', 'You have already attempted this quiz. You can only attempt this quiz ' . $quiz->max_attempts . ' time(s). Please try other quizzes.');
            }
        }
        return null;
    }

    private function checkCooldown(Quiz $quiz): ?RedirectResponse
    {
        if ($quiz->time_between_attempts && Auth::check()) {
            $lastAttempt = Attempt::where('quiz_id', $quiz->id)
                ->where('user_id', Auth::id())
                ->whereNotNull('completed_at')
                ->orderByDesc('completed_at')
                ->first();
            if ($lastAttempt) {
                try {
                    $lockUntil = Carbon::parse($lastAttempt->completed_at, 'UTC')
                        ->addMinutes($quiz->time_between_attempts);
                    if (Carbon::now('UTC')->lt($lockUntil)) {
                        $seconds = Carbon::now('UTC')->diffInSeconds($lockUntil);
                        return redirect()->route('quizzes.show', $quiz->id)
                            ->with('error', 'You must wait ' . gmdate('i:s', $seconds) . ' before attempting this quiz again.');
                    }
                } catch (\Exception $e) {
                    // ignore and allow attempt
                }
            }
        }
        return null;
    }

    public function submit(Request $request, Quiz $quiz): RedirectResponse
    {
        $request->validate([
            'answers' => 'nullable|array',
            'answers.*' => 'nullable',
        ]);

        $user = Auth::user();
        $answers = $request->input('answers', []);

        DB::beginTransaction();

        try {
            // Ensure necessary relations are loaded
            $quiz->load(['questions.question.options', 'questions.question.question_type']);

            Log::info('Submitting quiz attempt', [
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'request_answers_count' => is_array($answers) ? count($answers) : 0,
            ]);

            // Use provided attempt if available (created when user clicked Start)
            $attemptId = $request->input('attempt_id');
            $attempt = null;
            if ($attemptId) {
                $attempt = Attempt::where('id', $attemptId)
                    ->where('quiz_id', $quiz->id)
                    ->where('user_id', $user->id)
                    ->first();
            }

            // Create attempt if not found
            if (empty($attempt)) {
                $attempt = Attempt::create([
                    'user_id' => $user->id,
                    'quiz_id' => $quiz->id,
                    'score' => 0.00,
                    'passed' => 0,
                    'completed_at' => null,
                ]);
            }

            // Mark attempt completed now (submission time)
            $attempt->completed_at = Carbon::now('UTC');
            $attempt->save();

            Log::info('Created attempt record', ['attempt_id' => $attempt->id]);

            // Calculate and persist score
            $this->processAttemptAndCalculateScore($quiz, $attempt, $answers);

            DB::commit();

            Log::info('Quiz attempt submitted', [
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'attempt_id' => $attempt->id,
            ]);

            return redirect()->route('quizzes.show', $quiz->id)
                ->with('success', 'Thank you! Your quiz has been submitted successfully. (Attempt ID: ' . $attempt->id . ')');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quiz submission failed', [
                'user_id' => optional($user)->id,
                'quiz_id' => $quiz->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('quizzes.show', $quiz->id)
                ->with('error', 'An error occurred while submitting your quiz. Please try again.');
        }
    }

    public function show(Quiz $quiz, Attempt $attempt)
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

        $correctAnswers = $question->options
            ->where('is_correct', 1)
            ->pluck('option')
            ->map(fn($v) => trim((string)$v))
            ->filter()
            ->values()
            ->all();

        $submitted = trim((string)($userAnswer ?? ''));
        $isCorrect = ($submitted !== '') && collect($correctAnswers)
            ->map(fn($v) => strtolower($v))
            ->contains(strtolower($submitted));

        if ($isCorrect) {
            $earned = $marks;
        } elseif ($negEnabled && $neg > 0) {
            $earned = -1.0 * $neg;
        } else {
            $earned = 0.0;
        }

        Log::info('Saved fill-in answer', [
            'attempt_id' => $attempt->id,
            'question_id' => $questionId,
            'answer_text' => $userAnswer,
            'earned' => $earned,
        ]);

        return $earned;
    }

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

        if (!empty($selected)) {
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
            Log::info('No answer provided for question', [
                'attempt_id' => $attempt->id,
                'question_id' => $questionId
            ]);
        }

        $correctOptionIds = $question->options
            ->where('is_correct', 1)
            ->pluck('id')
            ->map(fn($v) => intval($v))
            ->all();

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

        Log::info('Saved MCQ answer', [
            'attempt_id' => $attempt->id,
            'question_id' => $questionId,
            'selected' => $selected,
            'earned' => $earned,
        ]);

        return $earned;
    }
}
