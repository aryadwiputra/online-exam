<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $today = Carbon::today();
        $startDate = $today->copy()->subDays(6);
        $startedCounts = Attempt::query()
            ->whereBetween('started_at', [$startDate->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->selectRaw('DATE(started_at) as activity_date, COUNT(*) as total')
            ->groupBy('activity_date')
            ->pluck('total', 'activity_date');
        $submittedCounts = Attempt::query()
            ->whereNotNull('submitted_at')
            ->whereBetween('submitted_at', [$startDate->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->selectRaw('DATE(submitted_at) as activity_date, COUNT(*) as total')
            ->groupBy('activity_date')
            ->pluck('total', 'activity_date');
        $averageScores = Attempt::query()
            ->whereNotNull('submitted_at')
            ->whereNotNull('score')
            ->whereBetween('submitted_at', [$startDate->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->selectRaw('DATE(submitted_at) as activity_date, AVG(score) as average_score')
            ->groupBy('activity_date')
            ->pluck('average_score', 'activity_date');
        $dailyActivity = collect(range(0, 6))
            ->map(function (int $offset) use ($startDate, $startedCounts, $submittedCounts, $averageScores): array {
                $date = $startDate->copy()->addDays($offset);
                $key = $date->toDateString();

                return [
                    'date' => $key,
                    'label' => $date->translatedFormat('D'),
                    'started' => (int) ($startedCounts[$key] ?? 0),
                    'submitted' => (int) ($submittedCounts[$key] ?? 0),
                    'averageScore' => isset($averageScores[$key]) ? round((float) $averageScores[$key], 2) : null,
                ];
            });

        return Inertia::render('admin/Dashboard', [
            'dailyActivity' => $dailyActivity,
            'stats' => [
                'students' => Student::count(),
                'classrooms' => Classroom::count(),
                'exams' => Exam::count(),
                'sessions' => ExamSession::count(),
                'activeSessions' => ExamSession::query()
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>=', now())
                    ->count(),
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
