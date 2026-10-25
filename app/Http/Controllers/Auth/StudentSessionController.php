<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StudentSessionController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }

        return Inertia::render('auth/StudentLogin');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'nisn' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('student')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'nisn' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('student.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('student')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
