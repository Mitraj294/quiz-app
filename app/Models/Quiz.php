<?php

namespace App\Models;

use Harishdurga\LaravelQuiz\Models\Quiz as BaseQuiz;

/**
 * App Quiz model that extends the package Quiz model to ensure compatibility
 * with the package migrations and attribute accessors.
 */
class Quiz extends BaseQuiz
{
    // Inherit behavior from vendor model

    /**
     * Force use of numeric id for route model binding in the application.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Return number of attempts for a given user id.
     * Uses the application Attempt model directly to avoid any vendor soft-delete scopes.
     *
     * @param int|null $userId
     * @return int
     */
    public function attemptsCountForUser(?int $userId): int
    {
        if (! $userId) {
            return 0;
        }

        return \App\Models\Attempt::where('quiz_id', $this->id)
            ->where('user_id', $userId)
            ->count();
    }
}
