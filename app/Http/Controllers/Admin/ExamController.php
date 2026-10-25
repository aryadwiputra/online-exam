<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExamRequest;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExamController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $classroomId = $request->integer('classroom');
        $lessonId = $request->integer('lesson');

        $exams = Exam::query()
            ->with(['lesson:id,title', 'classroom:id,title'])
            ->withCount(['questions', 'sessions'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($classroomId > 0, fn ($query) => $query->where('classroom_id', $classroomId))
            ->when($lessonId > 0, fn ($query) => $query->where('lesson_id', $lessonId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/exams/Index', [
            'exams' => $exams,
            'classrooms' => Classroom::orderBy('title')->get(['id', 'title']),
            'lessons' => Lesson::orderBy('title')->get(['id', 'title']),
            'filters' => [
                'search' => $search,
                'classroom' => $classroomId ?: null,
                'lesson' => $lessonId ?: null,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/exams/Create', $this->formOptions());
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $exam = Exam::create($request->validated());

        return redirect()->route('admin.exams.show', $exam)
            ->with('success', 'Ujian berhasil dibuat. Silakan tambahkan soal.');
    }

    public function show(Exam $exam): Response
    {
        $exam->load(['lesson:id,title', 'classroom:id,title'])
            ->loadCount(['questions', 'sessions']);

        return Inertia::render('admin/exams/Show', [
            'exam' => $exam,
            'questions' => $exam->questions()
                ->orderBy('id')
                ->paginate(10, ['id', 'exam_id', 'question', 'options', 'answer_index'])
                ->withQueryString(),
        ]);
    }

    public function edit(Exam $exam): Response
    {
        return Inertia::render('admin/exams/Edit', [
            'exam' => $exam->only([
                'id',
                'lesson_id',
                'classroom_id',
                'title',
                'duration',
                'description',
                'random_question',
                'random_answer',
                'show_answer',
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function update(StoreExamRequest $request, Exam $exam): RedirectResponse
    {
        $exam->update($request->validated());

        return redirect()->route('admin.exams.index')
            ->with('success', 'Ujian berhasil diperbarui.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return redirect()->route('admin.exams.index')
            ->with('success', 'Ujian berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'classrooms' => Classroom::orderBy('title')->get(['id', 'title']),
            'lessons' => Lesson::orderBy('title')->get(['id', 'title']),
        ];
    }
}
