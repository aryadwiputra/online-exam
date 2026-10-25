<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'classroom_id' => Classroom::factory(),
            'title' => 'Ujian '.ucfirst(implode(' ', (array) fake()->unique()->words(2))),
            'duration' => 60,
            'description' => fake()->sentence(),
            'random_question' => true,
            'random_answer' => true,
            'show_answer' => false,
        ];
    }
}
