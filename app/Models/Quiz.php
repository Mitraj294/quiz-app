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
}
