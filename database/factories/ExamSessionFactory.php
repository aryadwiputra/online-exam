<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamSession>
 */
class ExamSessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'title' => 'Sesi '.fake()->unique()->numerify('##'),
            'start_time' => now()->subHour(),
            'end_time' => now()->addHours(3),
        ];
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHours(2),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => now()->subDays(2),
            'end_time' => now()->subDay(),
        ]);
    }
}
