<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Student;
use App\Support\Csv\CsvReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentImportController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('admin/students/Import');
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $file = new \SplFileObject('php://output', 'w');
            $file->fputcsv(['nisn', 'name', 'classroom', 'gender', 'password']);
            $file->fputcsv(['0012345678', 'Budi Santoso', 'Kelas 10A', 'L', 'password123']);
        }, 'template-import-siswa.csv', ['Content-Type' => 'text/csv']);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $rows = CsvReader::read($request->file('file'));
        $classrooms = Classroom::pluck('id', 'title');

        $created = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $row['__line'] ?? (string) ($index + 2);

            $validator = Validator::make($row, [
                'nisn' => ['required', 'string', 'max:30', 'unique:students,nisn'],
                'name' => ['required', 'string', 'max:255'],
                'classroom' => ['required', 'string'],
                'gender' => ['nullable', 'in:L,P'],
                'password' => ['nullable', 'string', Password::min(6)],
            ]);

            if ($validator->fails()) {
                $errors[] = "Baris {$line}: ".implode(' ', $validator->errors()->all());

                continue;
            }

            $classroomId = $classrooms->get($row['classroom']);

            if ($classroomId === null) {
                $errors[] = "Baris {$line}: kelas \"{$row['classroom']}\" tidak ditemukan.";

                continue;
            }

            Student::create([
                'classroom_id' => $classroomId,
                'nisn' => $row['nisn'],
                'name' => $row['name'],
                'gender' => $row['gender'] !== '' ? $row['gender'] : 'L',
                'password' => $row['password'] !== '' ? $row['password'] : 'password',
            ]);

            $created++;
        }

        return redirect()->route('admin.students.index')
            ->with('success', "{$created} siswa berhasil diimpor.")
            ->with('import_errors', array_slice($errors, 0, 25));
    }
}
