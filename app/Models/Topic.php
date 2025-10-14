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
     * Resolve route binding so both slug and numeric id work in URLs.
     *
     * Examples:
     *  - /topics/my-topic-slug  (slug)
     *  - /topics/9              (numeric id)
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?? $this->getRouteKeyName();

        // First try the configured route key (usually 'slug')
        $model = static::where($field, $value)->first();

        // If not found and value looks numeric, try by id as a fallback
        if (! $model && is_numeric($value)) {
            $model = static::find($value);
        }

        return $model;
    }
}
