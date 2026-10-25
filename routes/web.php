<?php

use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamSessionController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuestionImportController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentImportController;
use App\Http\Controllers\Auth\AdminSessionController;
use App\Http\Controllers\Auth\StudentSessionController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StudentSessionController::class, 'create'])->name('student.login');
Route::post('/student/login', [StudentSessionController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('student.login.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AdminSessionController::class, 'create'])->name('login');
    Route::post('/login', [AdminSessionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AdminSessionController::class, 'destroy'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::resource('classrooms', ClassroomController::class)->except('show');
        Route::resource('lessons', LessonController::class)->except('show');
        Route::resource('students', StudentController::class)->except('show');
        Route::get('students/import', [StudentImportController::class, 'create'])->name('students.import.create');
        Route::get('students/import/template', [StudentImportController::class, 'template'])->name('students.import.template');
        Route::post('students/import', [StudentImportController::class, 'store'])->name('students.import.store');

        Route::resource('exams', ExamController::class);
        Route::resource('exams.questions', QuestionController::class)
            ->shallow()
            ->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::get('exams/{exam}/questions/import', [QuestionImportController::class, 'create'])
            ->name('exams.questions.import.create');
        Route::get('exams/{exam}/questions/import/template', [QuestionImportController::class, 'template'])
            ->name('exams.questions.import.template');
        Route::post('exams/{exam}/questions/import', [QuestionImportController::class, 'store'])
            ->name('exams.questions.import.store');

        Route::resource('exam-sessions', ExamSessionController::class)->except('show');
        Route::get('exam-sessions/{examSession}/enrollments', [EnrollmentController::class, 'index'])
            ->name('exam-sessions.enrollments.index');
        Route::post('exam-sessions/{examSession}/enrollments', [EnrollmentController::class, 'store'])
            ->name('exam-sessions.enrollments.store');
        Route::delete('exam-sessions/{examSession}/enrollments/{student}', [EnrollmentController::class, 'destroy'])
            ->name('exam-sessions.enrollments.destroy');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
});

Route::prefix('student')->name('student.')->middleware('auth:student')->group(function (): void {
    Route::post('/logout', [StudentSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
    Route::get('/exams/{session}', [StudentExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{session}/start', [StudentExamController::class, 'start'])->name('exams.start');
    Route::get('/attempts/{attempt}', [StudentExamController::class, 'attempt'])->name('attempts.show');
    Route::post('/attempts/{attempt}/answers', [StudentExamController::class, 'answer'])->name('attempts.answer');
    Route::post('/attempts/{attempt}/submit', [StudentExamController::class, 'submit'])->name('attempts.submit');
    Route::get('/attempts/{attempt}/result', [StudentExamController::class, 'result'])->name('attempts.result');
});
