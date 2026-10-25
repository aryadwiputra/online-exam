<?php

use App\Models\Attempt;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;

it('provides daily started and submitted attempt trends for the last seven days', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-25 12:00:00'));

    $admin = User::factory()->create();
    $student = Student::factory()->create();
    $session = ExamSession::factory()->create();

    Attempt::factory()->create([
        'exam_session_id' => $session->id,
        'student_id' => $student->id,
        'started_at' => Carbon::parse('2026-10-24 09:00:00'),
        'expires_at' => Carbon::parse('2026-10-24 10:00:00'),
    ]);

    Attempt::factory()->submitted()->create([
        'exam_session_id' => ExamSession::factory()->create()->id,
        'student_id' => Student::factory()->create()->id,
        'started_at' => Carbon::parse('2026-10-24 08:00:00'),
        'submitted_at' => Carbon::parse('2026-10-24 09:30:00'),
        'score' => 80,
    ]);

    Attempt::factory()->submitted()->create([
        'exam_session_id' => ExamSession::factory()->create()->id,
        'student_id' => Student::factory()->create()->id,
        'started_at' => Carbon::parse('2026-10-22 08:00:00'),
        'submitted_at' => Carbon::parse('2026-10-22 09:30:00'),
        'score' => 60,
    ]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('admin/Dashboard')
        ->has('dailyActivity', 7)
        ->where('dailyActivity.0.date', '2026-10-19')
        ->where('dailyActivity.0.started', 0)
        ->where('dailyActivity.0.submitted', 0)
        ->where('dailyActivity.3.date', '2026-10-22')
        ->where('dailyActivity.3.started', 1)
        ->where('dailyActivity.3.submitted', 1)
        ->where('dailyActivity.3.averageScore', 60)
        ->where('dailyActivity.5.date', '2026-10-24')
        ->where('dailyActivity.5.started', 2)
        ->where('dailyActivity.5.submitted', 1)
        ->where('dailyActivity.5.averageScore', 80)
        ->where('dailyActivity.6.date', '2026-10-25')
        ->where('dailyActivity.6.averageScore', null));
});

afterEach(function () {
    Carbon::setTestNow();
});
