<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class QuestionController extends Controller
{
    public function create(Exam $exam): Response
    {
        return Inertia::render('admin/questions/Create', [
            'exam' => $exam->only(['id', 'title']),
        ]);
    }

    public function store(StoreQuestionRequest $request, Exam $exam): RedirectResponse
    {
        $exam->questions()->create($request->validated());

        return redirect()->route('admin.exams.show', $exam)
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    public function edit(Question $question): Response
    {
        $question->load('exam:id,title');

        return Inertia::render('admin/questions/Edit', [
            'question' => $question->only([
                'id',
                'exam_id',
                'question',
                'options',
                'answer_index',
            ]),
            'exam' => $question->exam->only(['id', 'title']),
        ]);
    }

    public function update(StoreQuestionRequest $request, Question $question): RedirectResponse
    {
        $question->update($request->validated());

        $examId = $question->exam_id;

        return redirect()->route('admin.exams.show', $examId)
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $examId = $question->exam_id;

        $question->delete();

        return redirect()->route('admin.exams.show', $examId)
            ->with('success', 'Soal berhasil dihapus.');
    }
}
