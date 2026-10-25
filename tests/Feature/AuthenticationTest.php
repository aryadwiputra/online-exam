<?php

use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('renders the admin login page for guests', function () {
    $this->get('/login')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('auth/Login'));
});

it('renders the student login page at the root url', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('auth/StudentLogin'));
});

it('authenticates an admin with valid credentials', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects an admin login with wrong credentials', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('authenticates a student with nisn and password', function () {
    $student = Student::factory()->create([
        'classroom_id' => Classroom::factory(),
        'password' => 'secret-password',
    ]);

    $this->post('/student/login', [
        'nisn' => $student->nisn,
        'password' => 'secret-password',
    ])->assertRedirect(route('student.dashboard'));

    $this->assertAuthenticatedAs($student, 'student');
});

it('rejects a student login with wrong password', function () {
    $student = Student::factory()->create(['password' => 'secret-password']);

    $this->post('/student/login', [
        'nisn' => $student->nisn,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('nisn');

    $this->assertGuest('student');
});

it('redirects guests from the admin area to the admin login', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

it('redirects guests from the student area to the student login', function () {
    $this->get('/student/dashboard')->assertRedirect(route('student.login'));
});

it('prevents a student session from accessing the admin area', function () {
    $student = Student::factory()->create();

    $this->actingAs($student, 'student');

    Auth::shouldUse('web');

    $this->get('/admin')
        ->assertRedirect(route('login'));
});

it('prevents an admin session from accessing the student area', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get('/student/dashboard')
        ->assertRedirect(route('student.login'));
});

it('logs out the admin session', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post('/logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
