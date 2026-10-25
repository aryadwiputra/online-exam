<?php

use App\Models\Attempt;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Lesson;
use App\Models\Student;

function attemptFixture(array $examAttributes = [], int $questionCount = 3): array
{
    $classroom = Classroom::factory()->create();
    $exam = Exam::factory()->create([
        'classroom_id' => $classroom->id,
        'lesson_id' => Lesson::factory()->create()->id,
        'duration' => 60,
        ...$examAttributes,
    ]);

    $questions = collect();

    for ($index = 1; $index <= $questionCount; $index++) {
        $questions->push($exam->questions()->create([
            'question' => "Soal nomor {$index}",
            'options' => ['Benar', 'Salah', 'Mungkin'],
            'answer_index' => 0,
        ]));
    }

    $session = ExamSession::factory()->create([
        'exam_id' => $exam->id,
        'start_time' => now()->subMinutes(10),
        'end_time' => now()->addHours(2),
    ]);

    $student = Student::factory()->create(['classroom_id' => $classroom->id]);
    $session->enrollments()->create(['student_id' => $student->id]);

    return [$exam, $session, $student, $questions];
}

it('lets an enrolled student start an attempt with stable question and option ordering', function () {
    [$exam, $session, $student, $questions] = attemptFixture();

    $this->actingAs($student, 'student')
        ->post(route('student.exams.start', $session))
        ->assertRedirect();

    $attempt = Attempt::where('student_id', $student->id)->firstOrFail();

    expect($attempt->answers()->count())->toBe($questions->count())
        ->and($attempt->answers()->pluck('position')->sort()->values()->all())
        ->toBe(range(1, $questions->count()));

    $attempt->answers()->get()->each(function ($answer) {
        expect(collect($answer->option_order)->sort()->values()->all())->toBe([0, 1, 2]);
    });
});

it('reuses the existing attempt when starting twice', function () {
    [, $session, $student] = attemptFixture();

    $this->actingAs($student, 'student')->post(route('student.exams.start', $session));
    $this->actingAs($student, 'student')->post(route('student.exams.start', $session));

    expect(Attempt::where('student_id', $student->id)->count())->toBe(1);
});

it('blocks students who are not enrolled in the session', function () {
    [, $session] = attemptFixture();
    $outsider = Student::factory()->create();

    $this->actingAs($outsider, 'student')
        ->post(route('student.exams.start', $session))
        ->assertSessionHasErrors('session');

    expect(Attempt::count())->toBe(0);
});

it('blocks starting a session that has not opened yet', function () {
    [$exam, , $student] = attemptFixture();
    $session = ExamSession::factory()->upcoming()->create(['exam_id' => $exam->id]);
    $session->enrollments()->create(['student_id' => $student->id]);

    $this->actingAs($student, 'student')
        ->post(route('student.exams.start', $session))
        ->assertSessionHasErrors('session');

    expect(Attempt::count())->toBe(0);
});

it('blocks starting an exam without questions', function () {
    [, $session, $student] = attemptFixture([], 0);

    $this->actingAs($student, 'student')
        ->post(route('student.exams.start', $session))
        ->assertSessionHasErrors('session');
});

it('records an answer and marks correctness on the server', function () {
    [, $session, $student, $questions] = attemptFixture();

    $this->actingAs($student, 'student')->post(route('student.exams.start', $session));

    $attempt = Attempt::where('student_id', $student->id)->firstOrFail();
    $question = $questions->first();

    $this->actingAs($student, 'student')
        ->post(route('student.attempts.answer', $attempt), [
            'question_id' => $question->id,
            'chosen_index' => 0,
        ])
        ->assertRedirect();

    $answer = $attempt->answers()->where('question_id', $question->id)->firstOrFail();

    expect($answer->chosen_index)->toBe(0)
        ->and($answer->is_correct)->toBeTrue();
});

it('rejects option indexes outside the attempt option order', function () {
    [, $session, $student, $questions] = attemptFixture();

    $this->actingAs($student, 'student')->post(route('student.exams.start', $session));

    $attempt = Attempt::where('student_id', $student->id)->firstOrFail();

    $this->actingAs($student, 'student')
        ->post(route('student.attempts.answer', $attempt), [
            'question_id' => $questions->first()->id,
            'chosen_index' => 9,
        ])
        ->assertSessionHasErrors('chosen_index');
});

it('prevents a student from reading another student attempt', function () {
    [, $session, $student, $questions] = attemptFixture();

    $this->actingAs($student, 'student')->post(route('student.exams.start', $session));
    $attempt = Attempt::where('student_id', $student->id)->firstOrFail();

    $other = Student::factory()->create();

    $this->actingAs($other, 'student')
        ->get(route('student.attempts.show', $attempt))
        ->assertForbidden();

    $this->actingAs($other, 'student')
        ->post(route('student.attempts.answer', $attempt), [
            'question_id' => $questions->first()->id,
            'chosen_index' => 0,
        ])
        ->assertForbidden();
});

it('scores the attempt on submit and blocks further answers', function () {
    [, $session, $student, $questions] = attemptFixture();

    $this->actingAs($student, 'student')->post(route('student.exams.start', $session));
    $attempt = Attempt::where('student_id', $student->id)->firstOrFail();

    $this->actingAs($student, 'student')
        ->post(route('student.attempts.answer', $attempt), [
            'question_id' => $questions[0]->id,
            'chosen_index' => 0,
        ]);
    $this->actingAs($student, 'student')
        ->post(route('student.attempts.answer', $attempt), [
            'question_id' => $questions[1]->id,
            'chosen_index' => 1,
        ]);

    $this->actingAs($student, 'student')
        ->post(route('student.attempts.submit', $attempt))
        ->assertRedirect(route('student.attempts.result', $attempt));

    $attempt->refresh();

    expect($attempt->submitted_at)->not->toBeNull()
        ->and($attempt->total_correct)->toBe(1)
        ->and((float) $attempt->score)->toBe(33.33);

    $this->actingAs($student, 'student')
        ->post(route('student.attempts.answer', $attempt), [
            'question_id' => $questions[2]->id,
            'chosen_index' => 0,
        ])
        ->assertSessionHasErrors('attempt');
});

it('auto submits an attempt when the time has expired', function () {
    [, $session, $student] = attemptFixture();

    $this->actingAs($student, 'student')->post(route('student.exams.start', $session));
    $attempt = Attempt::where('student_id', $student->id)->firstOrFail();

    $this->travel(61)->minutes();

    $this->actingAs($student, 'student')
        ->get(route('student.attempts.show', $attempt))
        ->assertRedirect(route('student.attempts.result', $attempt));

    expect($attempt->fresh()->submitted_at)->not->toBeNull();
});

it('caps the attempt deadline at the session end time', function () {
    [$exam, , $student] = attemptFixture(['duration' => 120]);
    $session = ExamSession::factory()->create([
        'exam_id' => $exam->id,
        'start_time' => now()->subMinutes(30),
        'end_time' => now()->addMinutes(15),
    ]);
    $session->enrollments()->create(['student_id' => $student->id]);

    $this->actingAs($student, 'student')->post(route('student.exams.start', $session));

    $attempt = Attempt::where('student_id', $student->id)->firstOrFail();

    expect($attempt->expires_at->equalTo($session->end_time))->toBeTrue();
});

it('hides the answer key on the result page when the exam disables it', function () {
    [, $session, $student] = attemptFixture(['show_answer' => false]);

    $this->actingAs($student, 'student')->post(route('student.exams.start', $session));
    $attempt = Attempt::where('student_id', $student->id)->firstOrFail();

    $this->actingAs($student, 'student')->post(route('student.attempts.submit', $attempt));

    $this->actingAs($student, 'student')
        ->get(route('student.attempts.result', $attempt))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('student/attempts/Result')
            ->where('exam.show_answer', false)
            ->has('answers', 0));
});

it('shows the answer key on the result page when enabled', function () {
    [, $session, $student] = attemptFixture(['show_answer' => true]);

    $this->actingAs($student, 'student')->post(route('student.exams.start', $session));
    $attempt = Attempt::where('student_id', $student->id)->firstOrFail();

    $this->actingAs($student, 'student')->post(route('student.attempts.submit', $attempt));

    $this->actingAs($student, 'student')
        ->get(route('student.attempts.result', $attempt))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('exam.show_answer', true)
            ->has('answers', 3));
});

it('lists only enrolled sessions on the student dashboard', function () {
    [$exam, $session, $student] = attemptFixture();
    $otherSession = ExamSession::factory()->create(['exam_id' => $exam->id]);

    $this->actingAs($student, 'student')
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('student/Dashboard')
            ->has('sessions', 1)
            ->where('sessions.0.id', $session->id));
});
