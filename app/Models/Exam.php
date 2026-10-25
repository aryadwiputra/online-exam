<?php

namespace App\Models;

use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $lesson_id
 * @property int $classroom_id
 * @property string $title
 * @property int $duration
 * @property string|null $description
 * @property bool $random_question
 * @property bool $random_answer
 * @property bool $show_answer
 */
#[Fillable([
    'lesson_id',
    'classroom_id',
    'title',
    'duration',
    'description',
    'random_question',
    'random_answer',
    'show_answer',
])]
class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'random_question' => 'boolean',
            'random_answer' => 'boolean',
            'show_answer' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Lesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * @return BelongsTo<Classroom, $this>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * @return HasMany<ExamSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }
}
