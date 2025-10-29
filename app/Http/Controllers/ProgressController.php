<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    /** Show progress index page. */
    public function index()
    {
        return view('progress.index');
    }

    /** Show user's quiz progress and stats. */
    public function quiz()
    {
        $user = Auth::user();
        $quizIds = \App\Models\Attempt::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('quiz_id')->unique();
        $quizzes = \App\Models\Quiz::whereIn('id', $quizIds)->get();
        $remainingQuizzes = \App\Models\Quiz::whereNotIn('id', $quizIds)->get();

        $userQuizStats = [];
        foreach ($quizzes as $quiz) {
            $attempts = \App\Models\Attempt::where('user_id', $user->id)
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
                        $validTimes[] = abs(\Carbon\Carbon::parse($end)->diffInSeconds(\Carbon\Carbon::parse($start)));
                    }
                }
                if ($validTimes) {
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

        return view('progress.quiz', compact(
            'quizzes', 'remainingQuizzes', 'userQuizStats'
        ));
    }

    /** Show user's topic progress. */
    public function topic()
    {
        $user = Auth::user();
        $quizIds = \App\Models\Attempt::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('quiz_id')->unique();
        $topicIds = DB::table('topicables')
            ->whereIn('topicable_id', $quizIds)
            ->where('topicable_type', 'App\\Models\\Quiz')
            ->pluck('topic_id')->unique();
        $topics = \App\Models\Topic::whereIn('id', $topicIds)->get();
        $remainingTopics = \App\Models\Topic::whereNotIn('id', $topicIds)->get();
        return view('progress.topic', compact(
            'topics', 'remainingTopics'
        ));
    }
}
