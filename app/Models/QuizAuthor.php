<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model representing an author attached to a quiz.
 * Stored in `quiz_authors` table with optional soft deletes.
 */
class QuizAuthor extends Model
{
    use SoftDeletes;

    protected $table = 'quiz_authors';

    protected $fillable = [
        'quiz_id',
        'author_id',
        'author_type',
        'author_role',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The author morph (polymorphic relationship) is kept simple here —
     * the table stores author_id and author_type, but most app code will
     * store a User id with author_type empty/null for now per instructions.
     */
    public function author()
    {
        // If author_type is set, you can implement morphTo; keep simple for now.
        return $this->belongsTo(User::class, 'author_id');
    }
}
