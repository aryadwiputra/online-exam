<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@exam.test',
        ]);

        $classroom = Classroom::create(['title' => 'Kelas 10A']);
        $lesson = Lesson::create(['title' => 'Matematika']);

        $exam = Exam::create([
            'lesson_id' => $lesson->id,
            'classroom_id' => $classroom->id,
            'title' => 'Ulangan Harian Matematika',
            'duration' => 45,
            'description' => 'Ulangan pilihan ganda bab aljabar.',
            'random_question' => true,
            'random_answer' => true,
            'show_answer' => true,
        ]);

        $exam->questions()->createMany([
            [
                'question' => 'Berapakah hasil dari 12 x 8?',
                'options' => ['86', '96', '104', '112'],
                'answer_index' => 1,
            ],
            [
                'question' => 'Nilai x pada persamaan 2x + 6 = 14 adalah?',
                'options' => ['2', '3', '4', '5'],
                'answer_index' => 2,
            ],
            [
                'question' => 'Bentuk sederhana dari 3a + 5a adalah?',
                'options' => ['8a', '15a', '8a²', '2a'],
                'answer_index' => 0,
            ],
        ]);

        $session = ExamSession::create([
            'exam_id' => $exam->id,
            'title' => 'Sesi Pagi',
            'start_time' => now()->subMinutes(30),
            'end_time' => now()->addDays(1),
        ]);

        $students = collect([
            ['nisn' => '0012345678', 'name' => 'Budi Santoso', 'gender' => 'L'],
            ['nisn' => '0012345679', 'name' => 'Siti Aminah', 'gender' => 'P'],
        ])->map(fn (array $data) => Student::create([
            ...$data,
            'classroom_id' => $classroom->id,
            'password' => 'password',
        ]));

        foreach ($students as $student) {
            $session->enrollments()->create(['student_id' => $student->id]);
        }
    }
}
