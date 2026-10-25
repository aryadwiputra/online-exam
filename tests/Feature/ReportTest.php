<?php

use App\Models\Attempt;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create();

    $classroom = Classroom::factory()->create(['title' => 'Kelas 10A']);
    $exam = Exam::factory()->create(['classroom_id' => $classroom->id]);
    $this->session = ExamSession::factory()->create(['exam_id' => $exam->id]);
    $this->student = Student::factory()->create([
        'classroom_id' => $classroom->id,
        'name' => 'Budi Santoso',
    ]);

    $this->attempt = Attempt::factory()->submitted()->create([
        'exam_session_id' => $this->session->id,
        'student_id' => $this->student->id,
        'total_correct' => 2,
        'score' => 66.67,
    ]);
});

it('lists attempts with summary statistics', function () {
    $this->actingAs($this->admin)
        ->get('/admin/reports')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/Index')
            ->has('attempts.data', 1)
            ->where('summary.total', 1)
            ->where('summary.average', 66.67)
            ->where('summary.highest', 66.67));
});

it('filters reports by exam session', function () {
    $otherSession = ExamSession::factory()->create([
        'exam_id' => $this->session->exam_id,
    ]);

    $this->actingAs($this->admin)
        ->get('/admin/reports?session='.$otherSession->id)
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('attempts.data', 0));

    $this->actingAs($this->admin)
        ->get('/admin/reports?session='.$this->session->id)
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('attempts.data', 1));
});

it('filters reports by student name or nisn', function () {
    $this->actingAs($this->admin)
        ->get('/admin/reports?search=Budi')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('attempts.data', 1));

    $this->actingAs($this->admin)
        ->get('/admin/reports?search=tidak-ada')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('attempts.data', 0));
});

it('exports the filtered report as csv', function () {
    $response = $this->actingAs($this->admin)
        ->get('/admin/reports/export?session='.$this->session->id);

    $response->assertOk();

    $content = $response->streamedContent();

    expect($content)->toContain('NISN')
        ->and($content)->toContain('Budi Santoso')
        ->and($content)->toContain($this->student->nisn);
});

it('protects reports from unauthenticated access', function () {
    $this->get('/admin/reports')->assertRedirect(route('login'));
    $this->get('/admin/reports/export')->assertRedirect(route('login'));
});
