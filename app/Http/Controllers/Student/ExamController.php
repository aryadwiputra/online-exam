<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Student;
use App\Services\ExamAttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExamController extends Controller
{
    public function __construct(private readonly ExamAttemptService $attempts) {}

    public function show(Request $request, ExamSession $session): Response|RedirectResponse
    {
        $student = $this->student($request);

        $isEnrolled = $student->enrollments()
            ->where('exam_session_id', $session->id)
            ->exists();

        abort_unless($isEnrolled, 403);

        $attempt = $student->attempts()
            ->where('exam_session_id', $session->id)
            ->first();

        if ($attempt !== null) {
            return redirect()->route(
                $attempt->isSubmitted() ? 'student.attempts.result' : 'student.attempts.show',
                $attempt,
            );
        }

        $session->load('exam:id,title,description,duration,show_answer', 'exam.lesson:id,title');

        return Inertia::render('student/exams/Show', [
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'start_time' => $session->start_time->toDateTimeString(),
                'end_time' => $session->end_time->toDateTimeString(),
                'status' => match (true) {
                    $session->hasEnded() => 'ended',
                    $session->hasStarted() => 'open',
                    default => 'upcoming',
                },
            ],
            'exam' => [
                ...$session->exam->only(['id', 'title', 'description', 'duration']),
                'lesson' => $session->exam->lesson?->title,
                'questions_count' => $session->exam->questions()->count(),
            ],
        ]);
    }

    public function start(Request $request, ExamSession $session): RedirectResponse
    {
        $attempt = $this->attempts->start($session, $this->student($request));

        return redirect()->route(
            $attempt->isSubmitted() ? 'student.attempts.result' : 'student.attempts.show',
            $attempt,
        );
    }

    public function attempt(Request $request, Attempt $attempt): Response|RedirectResponse
    {
        $this->ensureOwner($request, $attempt);

        $this->attempts->submitIfExpired($attempt);

        if ($attempt->isSubmitted()) {
            return redirect()->route('student.attempts.result', $attempt);
        }

        $page = max(1, $request->integer('page', 1));

        $answers = $attempt->answers()
            ->with('question:id,question,options')
            ->orderBy('position')
            ->get();

        $total = $answers->count();

        if ($page > $total) {
            $page = $total;
        }

        /** @var AttemptAnswer|null $current */
        $current = $answers->firstWhere('position', $page);

        abort_if($current === null, 404);

        return Inertia::render('student/attempts/Show', [
            'attempt' => [
                'id' => $attempt->id,
                'remaining_seconds' => $attempt->remainingSeconds(),
                'expires_at' => $attempt->expires_at->toIso8601String(),
            ],
            'exam' => [
                'title' => $attempt->examSession->exam->title,
                'session' => $attempt->examSession->title,
            ],
            'question' => [
                'id' => $current->question->id,
                'text' => $current->question->question,
                'position' => $current->position,
                'chosen_index' => $current->chosen_index,
                'options' => collect($current->option_order)
                    ->map(fn (int $originalIndex) => [
                        'index' => $originalIndex,
                        'label' => Question::optionLabel($originalIndex),
                        'text' => $current->question->options[$originalIndex] ?? '',
                    ])
                    ->values(),
            ],
            'navigation' => $answers->map(fn (AttemptAnswer $answer) => [
                'position' => $answer->position,
                'answered' => $answer->chosen_index !== null,
            ])->values(),
            'total' => $total,
        ]);
    }

    public function answer(Request $request, Attempt $attempt): RedirectResponse
    {
        $this->ensureOwner($request, $attempt);

        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'chosen_index' => ['required', 'integer', 'min:0'],
        ]);

        $answer = $attempt->answers()
            ->where('question_id', $validated['question_id'])
            ->firstOrFail();

        $this->attempts->recordAnswer($attempt, $answer, $validated['chosen_index']);

        return redirect()->route('student.attempts.show', [
            'attempt' => $attempt,
            'page' => $answer->position,
        ]);
    }

    public function submit(Request $request, Attempt $attempt): RedirectResponse
    {
        $this->ensureOwner($request, $attempt);

        $this->attempts->submit($attempt);

        return redirect()->route('student.attempts.result', $attempt);
    }

    public function result(Request $request, Attempt $attempt): Response|RedirectResponse
    {
        $this->ensureOwner($request, $attempt);

        if (! $attempt->isSubmitted()) {
            return redirect()->route('student.attempts.show', $attempt);
        }

        $attempt->load('examSession.exam');

        $exam = $attempt->examSession->exam;

        $answers = $attempt->answers()
            ->with('question:id,question,options,answer_index')
            ->orderBy('position')
            ->get();

        return Inertia::render('student/attempts/Result', [
            'result' => [
                'score' => $attempt->score,
                'total_correct' => $attempt->total_correct,
                'total_questions' => $answers->count(),
                'started_at' => $attempt->started_at->toDateTimeString(),
                'submitted_at' => $attempt->submitted_at?->toDateTimeString(),
            ],
            'exam' => [
                'title' => $exam->title,
                'session' => $attempt->examSession->title,
                'show_answer' => $exam->show_answer,
            ],
            'answers' => $exam->show_answer
                ? $answers->map(fn (AttemptAnswer $answer) => [
                    'position' => $answer->position,
                    'question' => $answer->question->question,
                    'options' => $answer->question->options,
                    'chosen_index' => $answer->chosen_index,
                    'answer_index' => $answer->question->answer_index,
                    'is_correct' => (bool) $answer->is_correct,
                ])->values()
                : [],
        ]);
    }

    private function student(Request $request): Student
    {
        /** @var Student $student */
        $student = $request->user('student');

        return $student;
    }

    private function ensureOwner(Request $request, Attempt $attempt): void
    {
        abort_unless($attempt->student_id === $this->student($request)->id, 403);
    }
}
