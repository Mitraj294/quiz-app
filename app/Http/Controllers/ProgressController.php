<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        [$userTopicCount, $userQuizCount] = $this->computeUserProgressCounts($user);

        return view('progress.index', compact('userTopicCount', 'userQuizCount'));
    }

    public function quiz(): View
    {
        $user = Auth::user();

        $quizIds = Attempt::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('quiz_id')
            ->unique();

        $quizzes = Quiz::whereIn('id', $quizIds)->get();
        $remainingQuizzes = Quiz::whereNotIn('id', $quizIds)->get();

        $userQuizStats = [];

        foreach ($quizzes as $quiz) {
            $attempts = Attempt::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->whereNotNull('completed_at')
                ->get();

            $attemptCount = $attempts->count();
            $avgTimeTaken = null;
            $avgScore = null;

            if ($attemptCount > 0) {
                $validTimes = [];

                foreach ($attempts as $a) {
                    $start = $a->started_at ?? $a->created_at;
                    $end = $a->completed_at;

                    if ($start && $end) {
                        $validTimes[] = abs(Carbon::parse($end)->diffInSeconds(Carbon::parse($start)));
                    }
                }

                if (!empty($validTimes)) {
                    $avgTimeTaken = round(array_sum($validTimes) / count($validTimes) / 60, 2);
                }

                $avgScore = round($attempts->avg('score'), 2);
            }

            $userQuizStats[$quiz->id] = [
                'attemptCount' => $attemptCount,
                'avgTimeTaken' => $avgTimeTaken,
                'avgScore' => $avgScore,
            ];
        }

        [$userTopicCount, $userQuizCount] = $this->computeUserProgressCounts($user);

        return view('progress.quiz', compact('quizzes', 'remainingQuizzes', 'userQuizStats', 'userTopicCount', 'userQuizCount'));
    }

    public function topic(): View
    {
        $user = Auth::user();

        // gather quizzes the user has completed
        $quizIds = Attempt::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('quiz_id')
            ->unique()
            ->values()
            ->all();

        // topics the user has participated in (via quizzes)
        $topicIds = DB::table('topicables')
            ->whereIn('topicable_id', $quizIds)
            ->where('topicable_type', Quiz::class)
            ->pluck('topic_id')
            ->unique()
            ->values()
            ->all();

        $topics = Topic::with('children')->whereIn('id', $topicIds)->get();
        $remainingTopics = Topic::with('children')->whereNotIn('id', $topicIds)->get();

        $this->prepareParticipatedTopics($topics, $quizIds, $user);
        $this->prepareRemainingTopics($remainingTopics, $user);

        [$userTopicCount, $userQuizCount] = $this->computeUserProgressCounts($user);

        return view('progress.topic', compact('topics', 'remainingTopics', 'userTopicCount', 'userQuizCount'));
    }

    /**
     * Compute user progress counts: distinct topics and quizzes the user has completed.
     * Returns an array: [topicCount, quizCount]
     */
    protected function computeUserProgressCounts($user): array
    {
        $userTopicCount = 0;
        $userQuizCount = 0;

        if ($user) {
            // Count distinct topic IDs for quizzes the user has completed
            $userTopicCount = \App\Models\Attempt::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->join('topicables', function ($join) {
                    $join->on('quizzes.id', '=', 'topicables.topicable_id')
                        ->where('topicables.topicable_type', 'App\\Models\\Quiz');
                })
                ->distinct('topicables.topic_id')
                ->count('topicables.topic_id');

            // Count distinct quizzes the user has completed
            $userQuizCount = \App\Models\Attempt::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->distinct('quiz_id')
                ->count('quiz_id');
        }

        return [$userTopicCount, $userQuizCount];
    }

    /**
     * Prepare participated topics: attach related quizzes (only those user completed),
     * attach last attempt info for quizzes and compute subtopic stats.
     */
    protected function prepareParticipatedTopics($topics, array $quizIds, $user): void
    {
        foreach ($topics as $topic) {
            $topicableIds = DB::table('topicables')
                ->where('topic_id', $topic->id)
                ->where('topicable_type', Quiz::class)
                ->pluck('topicable_id')
                ->all();

            $related = Quiz::whereIn('id', $topicableIds)
                ->whereIn('id', $quizIds)
                ->get();

            // attach last attempt info for related quizzes
            foreach ($related as $quiz) {
                $attempt = Attempt::where('user_id', $user->id)
                    ->where('quiz_id', $quiz->id)
                    ->whereNotNull('completed_at')
                    ->orderByDesc('completed_at')
                    ->first();

                $score = $attempt ? ($attempt->score ?? $attempt->marks ?? $attempt->marks_obtained ?? null) : null;
                $passed = (!is_null($score) && !is_null($quiz->pass_marks)) ? ($score >= $quiz->pass_marks) : null;

                $quiz->last_attempt = $attempt;
                $quiz->last_score = $score;
                $quiz->last_passed = $passed;
            }

            $topic->related_quizzes = $related;

            // compute subtopic stats for children
            foreach ($topic->children as $sub) {
                $subQuizIds = DB::table('topicables')
                    ->where('topic_id', $sub->id)
                    ->where('topicable_type', Quiz::class)
                    ->pluck('topicable_id')
                    ->all();

                $subTotal = count($subQuizIds);
                $subAttempted = Attempt::where('user_id', $user->id)
                    ->whereNotNull('completed_at')
                    ->whereIn('quiz_id', $subQuizIds)
                    ->distinct()
                    ->count('quiz_id');

                $sub->subTotal = $subTotal;
                $sub->subAttempted = $subAttempted;
            }
        }
    }

    /**
     * Prepare remaining topics: attach all related quizzes and per-quiz last attempt info, and compute subtopic stats.
     */
    protected function prepareRemainingTopics($remainingTopics, $user): void
    {
        foreach ($remainingTopics as $topic) {
            $topicableIds = DB::table('topicables')
                ->where('topic_id', $topic->id)
                ->where('topicable_type', Quiz::class)
                ->pluck('topicable_id')
                ->all();

            $related = Quiz::whereIn('id', $topicableIds)->get();

            // for each related quiz attach last attempt info for the current user
            foreach ($related as $quiz) {
                $attempt = Attempt::where('user_id', $user->id)
                    ->where('quiz_id', $quiz->id)
                    ->whereNotNull('completed_at')
                    ->orderByDesc('completed_at')
                    ->first();

                $score = $attempt ? ($attempt->score ?? $attempt->marks ?? $attempt->marks_obtained ?? null) : null;
                $passed = (!is_null($score) && !is_null($quiz->pass_marks)) ? ($score >= $quiz->pass_marks) : null;

                $quiz->last_attempt = $attempt;
                $quiz->last_score = $score;
                $quiz->last_passed = $passed;
            }

            $topic->related_quizzes = $related;

            // compute subtopic stats for children
            foreach ($topic->children as $sub) {
                $subQuizIds = DB::table('topicables')
                    ->where('topic_id', $sub->id)
                    ->where('topicable_type', Quiz::class)
                    ->pluck('topicable_id')
                    ->all();

                $subTotal = count($subQuizIds);
                $subAttempted = Attempt::where('user_id', $user->id)
                    ->whereNotNull('completed_at')
                    ->whereIn('quiz_id', $subQuizIds)
                    ->distinct()
                    ->count('quiz_id');

                $sub->subTotal = $subTotal;
                $sub->subAttempted = $subAttempted;
            }
        }
    }
}
