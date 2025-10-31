<?php

namespace App\Models;

use Harishdurga\LaravelQuiz\Models\Topic as BaseTopic;

/**
 * App Topic model that extends vendor's Topic model to reuse table/attribute mappings.
 */
class Topic extends BaseTopic
{
    // Keep this class intentionally small — it inherits behavior from the package model.

    /**
     * Allow description to be mass assigned from forms in the app.
     */
    protected $fillable = ['name', 'slug', 'parent_id', 'is_active', 'description'];

    /**
     * Override quizzes relationship to count both App and vendor quiz types.
     */
    public function quizzes()
    {
        return $this->morphedByMany(\App\Models\Quiz::class, 'topicable')
            ->orWherePivot('topicable_type', 'Harishdurga\\LaravelQuiz\\Models\\Quiz');
    }

    /**
     * Auto-generate a unique slug from the name if missing.
     */
    protected static function booted()
    {
        static::creating(function ($topic) {
            if (empty($topic->slug) && ! empty($topic->name)) {
                $base = \Illuminate\Support\Str::slug($topic->name);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $topic->slug = $slug;
            }
        });
    }

    /**
     * Use numeric id for route model binding by default in the app.
     * This overrides the package which uses 'slug'.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

}
