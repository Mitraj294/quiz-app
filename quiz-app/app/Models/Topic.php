<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Topic extends Model
{
    protected $fillable = ['name', 'slug', 'parent_id', 'is_active'];

    public function questions(): MorphToMany
    {
        return $this->morphedByMany(Question::class, 'topicable');
    }

    public function quizzes(): MorphToMany
    {
        return $this->morphedByMany(Quiz::class, 'topicable');
    }
}
