<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    protected $fillable = ['question', 'options', 'correct_index', 'explanation', 'position'];

    /** Never include the answer if a question is ever turned into JSON. */
    protected $hidden = ['correct_index'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'correct_index' => 'integer',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}