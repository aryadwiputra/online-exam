<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamAttemptService
{
    public function start(ExamSession $session, Student $student): Attempt
    {
        $session->loadMissing('exam');

        $existing = Attempt::query()
            ->where('exam_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing !== null) {
            $this->submitIfExpired($existing);

            return $existing;
        }

        if (! $session->isOpen()) {
            throw ValidationException::withMessages([
                'session' => 'Sesi ujian belum dimulai atau sudah berakhir.',
            ]);
        }

        $isEnrolled = $student->enrollments()
            ->where('exam_session_id', $session->id)
            ->exists();

        if (! $isEnrolled) {
            throw ValidationException::withMessages([
                'session' => 'Anda tidak terdaftar pada sesi ujian ini.',
            ]);
        }

        $questions = $session->exam->questions()->get(['id', 'options', 'answer_index']);

        if ($questions->isEmpty()) {
            throw ValidationException::withMessages([
                'session' => 'Ujian ini belum memiliki soal.',
            ]);
        }

        return DB::transaction(function () use ($session, $student, $questions): Attempt {
            $startedAt = now();
            $expiresAt = $startedAt->addMinutes($session->exam->duration);

            if ($expiresAt->greaterThan($session->end_time)) {
                $expiresAt = $session->end_time;
            }

            $attempt = Attempt::create([
                'exam_session_id' => $session->id,
                'student_id' => $student->id,
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
            ]);

            $ordered = $session->exam->random_question
                ? $questions->shuffle()->values()
                : $questions->sortBy('id')->values();

            foreach ($ordered as $position => $question) {
                $attempt->answers()->create([
                    'question_id' => $question->id,
                    'position' => $position + 1,
                    'option_order' => $this->optionOrder($question, $session->exam->random_answer),
                ]);
            }

            return $attempt;
        });
    }

    public function recordAnswer(Attempt $attempt, AttemptAnswer $answer, int $optionIndex): Attempt
    {
        $this->ensureInProgress($attempt);

        if (! in_array($optionIndex, $answer->option_order, true)) {
            throw ValidationException::withMessages([
                'chosen_index' => 'Pilihan jawaban tidak valid.',
            ]);
        }

        $answer->update([
            'chosen_index' => $optionIndex,
            'is_correct' => $optionIndex === $answer->question->answer_index,
        ]);

        return $attempt;
    }

    public function submit(Attempt $attempt): Attempt
    {
        if ($attempt->isSubmitted()) {
            return $attempt;
        }

        return DB::transaction(function () use ($attempt): Attempt {
            $answers = $attempt->answers()->get(['is_correct']);
            $total = $answers->count();
            $correct = $answers->where('is_correct', true)->count();

            $attempt->forceFill([
                'submitted_at' => now(),
                'total_correct' => $correct,
                'score' => $total > 0 ? round($correct / $total * 100, 2) : 0,
            ])->save();

            return $attempt;
        });
    }

    public function submitIfExpired(Attempt $attempt): Attempt
    {
        if (! $attempt->isSubmitted() && $attempt->isExpired()) {
            return $this->submit($attempt);
        }

        return $attempt;
    }

    public function ensureInProgress(Attempt $attempt): void
    {
        if ($attempt->isSubmitted()) {
            throw ValidationException::withMessages([
                'attempt' => 'Ujian sudah dikumpulkan.',
            ]);
        }

        if ($attempt->isExpired()) {
            $this->submit($attempt);

            throw ValidationException::withMessages([
                'attempt' => 'Waktu ujian sudah habis.',
            ]);
        }
    }

    /**
     * @return array<int, int>
     */
    private function optionOrder(Question $question, bool $shuffle): array
    {
        $indexes = array_keys($question->options);

        return $shuffle ? array_values($this->shuffle($indexes)) : $indexes;
    }

    /**
     * @param  array<int, int>  $indexes
     * @return array<int, int>
     */
    private function shuffle(array $indexes): array
    {
        shuffle($indexes);

        return $indexes;
    }
}
