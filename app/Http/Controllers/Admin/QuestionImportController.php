<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Support\Csv\CsvReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionImportController extends Controller
{
    public function create(Exam $exam): Response
    {
        return Inertia::render('admin/questions/Import', [
            'exam' => $exam->only(['id', 'title']),
        ]);
    }

    public function template(Exam $exam): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $file = new \SplFileObject('php://output', 'w');
            $file->fputcsv(['question', 'option_1', 'option_2', 'option_3', 'option_4', 'option_5', 'answer']);
            $file->fputcsv(['Ibu kota Indonesia adalah?', 'Jakarta', 'Bandung', 'Surabaya', 'Medan', '', '1']);
        }, 'template-import-soal.csv', ['Content-Type' => 'text/csv']);
    }

    public function store(Request $request, Exam $exam): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $rows = CsvReader::read($request->file('file'));

        $created = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $row['__line'] ?? (string) ($index + 2);

            $options = [];

            for ($option = 1; $option <= 5; $option++) {
                $value = $row["option_{$option}"] ?? '';

                if ($value !== '') {
                    $options[] = $value;
                }
            }

            $payload = [
                'question' => $row['question'] ?? '',
                'answer' => $row['answer'] ?? '',
            ];

            $validator = Validator::make([...$payload, 'options' => $options], [
                'question' => ['required', 'string'],
                'options' => ['required', 'array', 'min:2', 'max:5'],
                'answer' => ['required', 'integer', 'min:1', 'max:5'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Baris {$line}: ".implode(' ', $validator->errors()->all());

                continue;
            }

            $answerIndex = (int) $payload['answer'] - 1;

            if ($answerIndex >= count($options)) {
                $errors[] = "Baris {$line}: jawaban menunjuk pilihan yang kosong.";

                continue;
            }

            $exam->questions()->create([
                'question' => $payload['question'],
                'options' => $options,
                'answer_index' => $answerIndex,
            ]);

            $created++;
        }

        return redirect()->route('admin.exams.show', $exam)
            ->with('success', "{$created} soal berhasil diimpor.")
            ->with('import_errors', array_slice($errors, 0, 25));
    }
}
