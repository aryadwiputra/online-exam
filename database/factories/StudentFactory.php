<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'classroom_id' => Classroom::factory(),
            'nisn' => fake()->unique()->numerify('##########'),
            'name' => fake()->name(),
            'password' => static::$password ??= Hash::make('password'),
            'gender' => fake()->randomElement(['L', 'P']),
        ];
    }
}
