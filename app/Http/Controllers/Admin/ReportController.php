<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\ExamSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $this->filters($request);

        $attempts = $this->query($filters)
            ->paginate(15)
            ->withQueryString();

        $submitted = $this->query($filters)->whereNotNull('submitted_at');

        $summary = [
            'total' => (clone $submitted)->count(),
            'average' => $this->roundScore((clone $submitted)->avg('score')),
            'highest' => $this->roundScore((clone $submitted)->max('score')),
            'lowest' => $this->roundScore((clone $submitted)->min('score')),
        ];

        return Inertia::render('admin/reports/Index', [
            'attempts' => $attempts,
            'sessions' => ExamSession::query()
                ->with('exam:id,title')
                ->orderByDesc('start_time')
                ->get(['id', 'exam_id', 'title', 'start_time']),
            'filters' => $filters,
            'summary' => $summary,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);

        $filename = 'laporan-nilai-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $file = new \SplFileObject('php://output', 'w');

            $file->fputcsv([
                'NISN',
                'Nama Siswa',
                'Kelas',
                'Ujian',
                'Sesi',
                'Status',
                'Benar',
                'Total Soal',
                'Nilai',
                'Mulai',
                'Selesai',
            ]);

            $this->query($filters)
                ->with('student:id,name,nisn,classroom_id', 'student.classroom:id,title')
                ->chunk(200, function ($attempts) use ($file): void {
                    foreach ($attempts as $attempt) {
                        $totalQuestions = $attempt->answers()->count();

                        $file->fputcsv([
                            $attempt->student->nisn,
                            $attempt->student->name,
                            $attempt->student->classroom?->title,
                            $attempt->examSession->exam->title,
                            $attempt->examSession->title,
                            $attempt->submitted_at === null ? 'Belum selesai' : 'Selesai',
                            $attempt->total_correct,
                            $totalQuestions,
                            $attempt->score,
                            $attempt->started_at->toDateTimeString(),
                            $attempt->submitted_at?->toDateTimeString(),
                        ]);
                    }
                });
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array{session: int|null, search: string}  $filters
     * @return Builder<Attempt>
     */
    private function query(array $filters): Builder
    {
        return Attempt::query()
            ->with('student:id,name,nisn,classroom_id', 'examSession:id,title,exam_id', 'examSession.exam:id,title')
            ->when($filters['session'] !== null, fn (Builder $query) => $query->where('exam_session_id', $filters['session']))
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $query->whereHas('student', function (Builder $query) use ($filters): void {
                    $query->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('nisn', 'like', "%{$filters['search']}%");
                });
            })
            ->orderByDesc('started_at');
    }

    /**
     * @return array{session: int|null, search: string}
     */
    private function filters(Request $request): array
    {
        return [
            'session' => $request->integer('session') ?: null,
            'search' => $request->string('search')->trim()->toString(),
        ];
    }

    private function roundScore(mixed $value): ?float
    {
        return $value === null ? null : round((float) $value, 2);
    }
}
