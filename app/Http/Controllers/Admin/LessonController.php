<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonRequest;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $lessons = Lesson::query()
            ->withCount('exams')
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/lessons/Index', [
            'lessons' => $lessons,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/lessons/Create');
    }

    public function store(StoreLessonRequest $request): RedirectResponse
    {
        Lesson::create($request->validated());

        return redirect()->route('admin.lessons.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Lesson $lesson): Response
    {
        return Inertia::render('admin/lessons/Edit', [
            'lesson' => $lesson,
        ]);
    }

    public function update(StoreLessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $lesson->update($request->validated());

        return redirect()->route('admin.lessons.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $lesson->delete();

        return redirect()->route('admin.lessons.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
