<?php

namespace App\Models;

use Harishdurga\LaravelQuiz\Models\Question as BaseQuestion;

/**
 * App Question model that extends the package Question model.
 */
class Question extends BaseQuestion
{
    // Inherit behavior from vendor model

    /**
     * Force use of numeric id for route model binding in the application.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }
}
