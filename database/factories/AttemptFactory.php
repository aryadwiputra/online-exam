<?php

namespace Database\Factories;

use App\Models\Attempt;
use App\Models\ExamSession;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attempt>
 */
class AttemptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_session_id' => ExamSession::factory(),
            'student_id' => Student::factory(),
            'started_at' => now(),
            'expires_at' => now()->addHour(),
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => now(),
            'total_correct' => 0,
            'score' => 0,
        ]);
    }
}
