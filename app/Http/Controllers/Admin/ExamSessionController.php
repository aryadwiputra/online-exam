<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExamSessionRequest;
use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExamSessionController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $sessions = ExamSession::query()
            ->with('exam:id,title,classroom_id')
            ->withCount(['enrollments', 'attempts'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->orderByDesc('start_time')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/exam-sessions/Index', [
            'sessions' => $sessions,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/exam-sessions/Create', [
            'exams' => $this->examOptions(),
        ]);
    }

    public function store(StoreExamSessionRequest $request): RedirectResponse
    {
        ExamSession::create($request->validated());

        return redirect()->route('admin.exam-sessions.index')
            ->with('success', 'Sesi ujian berhasil dibuat.');
    }

    public function edit(ExamSession $examSession): Response
    {
        return Inertia::render('admin/exam-sessions/Edit', [
            'session' => [
                ...$examSession->only(['id', 'exam_id', 'title']),
                'start_time' => $examSession->start_time->format('Y-m-d\TH:i'),
                'end_time' => $examSession->end_time->format('Y-m-d\TH:i'),
            ],
            'exams' => $this->examOptions(),
        ]);
    }

    public function update(StoreExamSessionRequest $request, ExamSession $examSession): RedirectResponse
    {
        $examSession->update($request->validated());

        return redirect()->route('admin.exam-sessions.index')
            ->with('success', 'Sesi ujian berhasil diperbarui.');
    }

    public function destroy(ExamSession $examSession): RedirectResponse
    {
        $examSession->delete();

        return redirect()->route('admin.exam-sessions.index')
            ->with('success', 'Sesi ujian berhasil dihapus.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function examOptions(): array
    {
        return Exam::query()
            ->with('classroom:id,title')
            ->orderBy('title')
            ->get(['id', 'title', 'classroom_id'])
            ->map(fn (Exam $exam) => [
                'id' => $exam->id,
                'title' => $exam->title.' — '.$exam->classroom->title,
            ])
            ->all();
    }
}
