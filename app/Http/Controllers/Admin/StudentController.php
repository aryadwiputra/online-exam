<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $classroomId = $request->integer('classroom');

        $students = Student::query()
            ->with('classroom:id,title')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->when($classroomId > 0, fn ($query) => $query->where('classroom_id', $classroomId))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/students/Index', [
            'students' => $students,
            'classrooms' => Classroom::orderBy('title')->get(['id', 'title']),
            'filters' => [
                'search' => $search,
                'classroom' => $classroomId ?: null,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/students/Create', [
            'classrooms' => Classroom::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        Student::create($request->validated());

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Student $student): Response
    {
        return Inertia::render('admin/students/Edit', [
            'student' => $student->only(['id', 'classroom_id', 'nisn', 'name', 'gender']),
            'classrooms' => Classroom::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(StoreStudentRequest $request, Student $student): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $student->update($data);

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }
}
