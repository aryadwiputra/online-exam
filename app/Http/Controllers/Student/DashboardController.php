<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var Student $student */
        $student = $request->user('student');

        $sessions = $student->examSessions()
            ->with('exam:id,title,description,duration,lesson_id,classroom_id', 'exam.lesson:id,title')
            ->orderBy('start_time')
            ->get()
            ->map(function ($session) use ($student) {
                $attempt = Attempt::query()
                    ->where('exam_session_id', $session->id)
                    ->where('student_id', $student->id)
                    ->first();

                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'exam_title' => $session->exam->title,
                    'lesson' => $session->exam->lesson?->title,
                    'description' => $session->exam->description,
                    'duration' => $session->exam->duration,
                    'start_time' => $session->start_time->toDateTimeString(),
                    'end_time' => $session->end_time->toDateTimeString(),
                    'status' => match (true) {
                        $session->hasEnded() => 'ended',
                        $session->hasStarted() => 'open',
                        default => 'upcoming',
                    },
                    'attempt' => $attempt === null ? null : [
                        'id' => $attempt->id,
                        'submitted' => $attempt->isSubmitted(),
                        'score' => $attempt->score,
                    ],
                ];
            });

        return Inertia::render('student/Dashboard', [
            'student' => $student->only(['id', 'name', 'nisn']),
            'classroom' => $student->classroom?->only(['id', 'title']),
            'sessions' => $sessions,
        ]);
    }
}
