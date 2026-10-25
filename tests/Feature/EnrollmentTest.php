<?php

use App\Models\Attempt;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create();
});

it('enrolls students of the exam classroom into a session', function () {
    $classroom = Classroom::factory()->create();
    $exam = Exam::factory()->create(['classroom_id' => $classroom->id]);
    $session = ExamSession::factory()->create(['exam_id' => $exam->id]);

    $eligible = Student::factory()->count(2)->create(['classroom_id' => $classroom->id]);
    $outsider = Student::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.exam-sessions.enrollments.store', $session), [
            'student_ids' => [...$eligible->pluck('id'), $outsider->id],
        ])
        ->assertRedirect(route('admin.exam-sessions.enrollments.index', $session));

    expect($session->enrollments()->count())->toBe(2)
        ->and($session->enrollments()->where('student_id', $outsider->id)->exists())->toBeFalse();
});

it('does not duplicate enrollments', function () {
    $classroom = Classroom::factory()->create();
    $exam = Exam::factory()->create(['classroom_id' => $classroom->id]);
    $session = ExamSession::factory()->create(['exam_id' => $exam->id]);
    $student = Student::factory()->create(['classroom_id' => $classroom->id]);

    foreach (range(1, 2) as $attempt) {
        $this->actingAs($this->admin)
            ->post(route('admin.exam-sessions.enrollments.store', $session), [
                'student_ids' => [$student->id],
            ]);
    }

    expect($session->enrollments()->count())->toBe(1);
});

it('prevents removing a student that already started the exam', function () {
    $classroom = Classroom::factory()->create();
    $exam = Exam::factory()->create(['classroom_id' => $classroom->id]);
    $session = ExamSession::factory()->create(['exam_id' => $exam->id]);
    $student = Student::factory()->create(['classroom_id' => $classroom->id]);
    $session->enrollments()->create(['student_id' => $student->id]);

    Attempt::factory()->create([
        'exam_session_id' => $session->id,
        'student_id' => $student->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.exam-sessions.enrollments.destroy', [
            'examSession' => $session->id,
            'student' => $student->id,
        ]))
        ->assertSessionHasErrors('student');

    expect($session->enrollments()->count())->toBe(1);
});

it('removes a student that has not started the exam', function () {
    $classroom = Classroom::factory()->create();
    $exam = Exam::factory()->create(['classroom_id' => $classroom->id]);
    $session = ExamSession::factory()->create(['exam_id' => $exam->id]);
    $student = Student::factory()->create(['classroom_id' => $classroom->id]);
    $session->enrollments()->create(['student_id' => $student->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.exam-sessions.enrollments.destroy', [
            'examSession' => $session->id,
            'student' => $student->id,
        ]))
        ->assertRedirect();

    expect($session->enrollments()->count())->toBe(0);
});
