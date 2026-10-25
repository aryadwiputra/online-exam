<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'students' => Student::count(),
                'classrooms' => Classroom::count(),
                'exams' => Exam::count(),
                'sessions' => ExamSession::count(),
                'attempts' => Attempt::count(),
                'submitted' => Attempt::whereNotNull('submitted_at')->count(),
            ],
            'activeSessions' => ExamSession::query()
                ->with('exam:id,title,classroom_id', 'exam.classroom:id,title')
                ->withCount(['enrollments', 'attempts'])
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->orderBy('end_time')
                ->limit(5)
                ->get(),
            'recentAttempts' => Attempt::query()
                ->with('student:id,name,nisn', 'examSession:id,title,exam_id', 'examSession.exam:id,title')
                ->latest('updated_at')
                ->limit(8)
                ->get(),
        ]);
    }
}
