<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class QuizAuthorController extends Controller
{
    /**
     * Attach a user as an author to a quiz.
     *
     * @param Request $request
     * @param Quiz $quiz
     * @return RedirectResponse
     */
    public function attach(Request $request, Quiz $quiz): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'author_role' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $user = User::findOrFail($data['user_id']);

        $quiz->authors()->syncWithoutDetaching([
            $user->id => [
                'author_type' => null,
                'author_role' => $data['author_role'] ?? 'contributor',
                'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            ],
        ]);

        Log::info('Author attached to quiz', ['quiz_id' => $quiz->id, 'user_id' => $user->id]);

        return redirect()->back()->with('success', 'Author attached successfully');
    }

    /**
     * Detach an author (user) from a quiz.
     *
     * @param Request $request
     * @param Quiz $quiz
     * @param int $userId
     * @return RedirectResponse
     */
    public function detach(Request $request, Quiz $quiz, $userId): RedirectResponse
    {
        $quiz->authors()->detach($userId);

        Log::info('Author detached from quiz', ['quiz_id' => $quiz->id, 'user_id' => $userId]);

        return redirect()->back()->with('success', 'Author removed successfully');
    }
}
