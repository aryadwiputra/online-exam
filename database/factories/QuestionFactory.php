<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'question' => rtrim(fake()->sentence(), '.').'?',
            'options' => [
                fake()->words(3, true),
                fake()->words(3, true),
                fake()->words(3, true),
                fake()->words(3, true),
            ],
            'answer_index' => 0,
        ];
    }
}
