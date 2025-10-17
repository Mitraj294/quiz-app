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

    /**
     * One-to-many relation to the QuizAuthor model which stores pivot-like meta
     * information about authors attached to a quiz.
     */
    public function quiz_authors()
    {
        return $this->hasMany(\App\Models\QuizAuthor::class, 'quiz_id');
    }

    /**
     * Convenience many-to-many-like accessor for author User models.
     * Uses the `quiz_authors` table as the pivot to access users.
     */
    public function authors()
    {
        return $this->belongsToMany(\App\Models\User::class, 'quiz_authors', 'quiz_id', 'author_id')
            ->withPivot(['author_type', 'author_role', 'is_active', 'created_at', 'updated_at', 'deleted_at']);
    }
}
