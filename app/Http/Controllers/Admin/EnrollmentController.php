<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Enrollment;
use App\Models\ExamSession;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EnrollmentController extends Controller
{
    public function index(ExamSession $examSession): Response
    {
        $examSession->load('exam:id,title,classroom_id', 'exam.classroom:id,title');

        $enrolled = $examSession->enrollments()
            ->with('student:id,name,nisn,classroom_id')
            ->get()
            ->map(fn (Enrollment $enrollment) => [
                'id' => $enrollment->student->id,
                'name' => $enrollment->student->name,
                'nisn' => $enrollment->student->nisn,
                'attempted' => $examSession->attempts()
                    ->where('student_id', $enrollment->student_id)
                    ->exists(),
            ]);

        $enrolledIds = $enrolled->pluck('id');

        $available = Student::query()
            ->where('classroom_id', $examSession->exam->classroom_id)
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('name')
            ->get(['id', 'name', 'nisn'])
            ->map(fn (Student $student) => [
                'id' => $student->id,
                'name' => $student->name,
                'nisn' => $student->nisn,
            ]);

        return Inertia::render('admin/exam-sessions/Enrollments', [
            'session' => [
                'id' => $examSession->id,
                'title' => $examSession->title,
                'exam_title' => $examSession->exam->title,
                'classroom_title' => $examSession->exam->classroom->title,
                'start_time' => $examSession->start_time->toDateTimeString(),
                'end_time' => $examSession->end_time->toDateTimeString(),
            ],
            'enrolled' => $enrolled,
            'available' => $available,
        ]);
    }

    public function store(Request $request, ExamSession $examSession): RedirectResponse
    {
        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $examSession->load('exam:id,classroom_id');

        $eligible = Student::query()
            ->whereIn('id', $validated['student_ids'])
            ->where('classroom_id', $examSession->exam->classroom_id)
            ->pluck('id');

        foreach ($eligible as $studentId) {
            Enrollment::firstOrCreate([
                'exam_session_id' => $examSession->id,
                'student_id' => $studentId,
            ]);
        }

        return redirect()->route('admin.exam-sessions.enrollments.index', $examSession)
            ->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function destroy(ExamSession $examSession, Student $student): RedirectResponse
    {
        $hasAttempt = Attempt::query()
            ->where('exam_session_id', $examSession->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($hasAttempt) {
            throw ValidationException::withMessages([
                'student' => 'Peserta sudah memulai ujian sehingga tidak dapat dikeluarkan.',
            ]);
        }

        $examSession->enrollments()
            ->where('student_id', $student->id)
            ->delete();

        return redirect()->route('admin.exam-sessions.enrollments.index', $examSession)
            ->with('success', 'Peserta berhasil dikeluarkan.');
    }
}
