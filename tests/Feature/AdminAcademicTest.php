<?php

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::factory()->create();
});

it('lists classrooms with student counts', function () {
    $classroom = Classroom::factory()->create(['title' => 'Kelas 10A']);
    Student::factory()->count(2)->create(['classroom_id' => $classroom->id]);

    $this->actingAs($this->admin)
        ->get('/admin/classrooms')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/classrooms/Index')
            ->has('classrooms.data', 1)
            ->where('classrooms.data.0.students_count', 2));
});

it('creates a classroom', function () {
    $this->actingAs($this->admin)
        ->post('/admin/classrooms', ['title' => 'Kelas 11B'])
        ->assertRedirect(route('admin.classrooms.index'));

    expect(Classroom::where('title', 'Kelas 11B')->exists())->toBeTrue();
});

it('rejects duplicate classroom titles', function () {
    Classroom::factory()->create(['title' => 'Kelas 10A']);

    $this->actingAs($this->admin)
        ->post('/admin/classrooms', ['title' => 'Kelas 10A'])
        ->assertSessionHasErrors('title');
});

it('updates a classroom', function () {
    $classroom = Classroom::factory()->create(['title' => 'Kelas Lama']);

    $this->actingAs($this->admin)
        ->put("/admin/classrooms/{$classroom->id}", ['title' => 'Kelas Baru'])
        ->assertRedirect(route('admin.classrooms.index'));

    expect($classroom->fresh()->title)->toBe('Kelas Baru');
});

it('creates a lesson', function () {
    $this->actingAs($this->admin)
        ->post('/admin/lessons', ['title' => 'Fisika'])
        ->assertRedirect(route('admin.lessons.index'));

    expect(Lesson::where('title', 'Fisika')->exists())->toBeTrue();
});

it('creates a student with a hashed password', function () {
    $classroom = Classroom::factory()->create();

    $this->actingAs($this->admin)
        ->post('/admin/students', [
            'classroom_id' => $classroom->id,
            'nisn' => '0099887766',
            'name' => 'Rina',
            'gender' => 'P',
            'password' => 'rahasia123',
        ])
        ->assertRedirect(route('admin.students.index'));

    $student = Student::where('nisn', '0099887766')->firstOrFail();

    expect($student->name)->toBe('Rina')
        ->and(Hash::check('rahasia123', $student->password))->toBeTrue();
});

it('keeps the old password when updating a student without a new one', function () {
    $student = Student::factory()->create(['password' => 'old-password']);
    $oldHash = $student->password;

    $this->actingAs($this->admin)
        ->put("/admin/students/{$student->id}", [
            'classroom_id' => $student->classroom_id,
            'nisn' => $student->nisn,
            'name' => 'Nama Baru',
            'gender' => 'L',
            'password' => '',
        ])
        ->assertRedirect(route('admin.students.index'));

    $student->refresh();

    expect($student->name)->toBe('Nama Baru')
        ->and($student->password)->toBe($oldHash);
});

it('creates an exam with configuration flags', function () {
    $lesson = Lesson::factory()->create();
    $classroom = Classroom::factory()->create();

    $this->actingAs($this->admin)
        ->post('/admin/exams', [
            'title' => 'UTS Biologi',
            'lesson_id' => $lesson->id,
            'classroom_id' => $classroom->id,
            'duration' => 90,
            'description' => 'Ujian tengah semester',
            'random_question' => true,
            'random_answer' => false,
            'show_answer' => true,
        ])
        ->assertRedirect();

    $exam = Exam::where('title', 'UTS Biologi')->firstOrFail();

    expect($exam->duration)->toBe(90)
        ->and($exam->random_question)->toBeTrue()
        ->and($exam->random_answer)->toBeFalse()
        ->and($exam->show_answer)->toBeTrue();
});

it('validates exam duration boundaries', function () {
    $lesson = Lesson::factory()->create();
    $classroom = Classroom::factory()->create();

    $this->actingAs($this->admin)
        ->post('/admin/exams', [
            'title' => 'Ujian Invalid',
            'lesson_id' => $lesson->id,
            'classroom_id' => $classroom->id,
            'duration' => 0,
            'random_question' => true,
            'random_answer' => true,
            'show_answer' => false,
        ])
        ->assertSessionHasErrors('duration');
});

it('creates a multiple choice question with an option array', function () {
    $exam = Exam::factory()->create();

    $this->actingAs($this->admin)
        ->post("/admin/exams/{$exam->id}/questions", [
            'question' => 'Berapa 2 + 2?',
            'options' => ['3', '4', '5'],
            'answer_index' => 1,
        ])
        ->assertRedirect(route('admin.exams.show', $exam));

    $question = Question::where('exam_id', $exam->id)->firstOrFail();

    expect($question->options)->toBe(['3', '4', '5'])
        ->and($question->answer_index)->toBe(1);
});

it('rejects an answer index outside the provided options', function () {
    $exam = Exam::factory()->create();

    $this->actingAs($this->admin)
        ->post("/admin/exams/{$exam->id}/questions", [
            'question' => 'Pilih',
            'options' => ['A', 'B'],
            'answer_index' => 4,
        ])
        ->assertSessionHasErrors('answer_index');
});

it('rejects questions with fewer than two options', function () {
    $exam = Exam::factory()->create();

    $this->actingAs($this->admin)
        ->post("/admin/exams/{$exam->id}/questions", [
            'question' => 'Pilih',
            'options' => ['A'],
            'answer_index' => 0,
        ])
        ->assertSessionHasErrors('options');
});

it('deletes a question', function () {
    $question = Question::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/admin/questions/{$question->id}")
        ->assertRedirect();

    expect(Question::find($question->id))->toBeNull();
});
