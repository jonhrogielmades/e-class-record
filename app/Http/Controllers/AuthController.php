<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function landing(): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('landing');
    }

    public function showLogin(): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'No account was found for that email and password combination.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Login successful. Your e-class dashboard is ready.');
    }

    public function showRegister(): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register', [
            'sections' => Section::query()->orderBy('name')->get(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in([User::ROLE_TEACHER, User::ROLE_STUDENT])],
            'section_id' => [
                Rule::requiredIf(fn () => $request->input('role') === User::ROLE_STUDENT),
                'nullable',
                'exists:sections,id',
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'guardian' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'password' => $validated['password'],
                'role' => $validated['role'],
                'phone' => $validated['phone'] ?? null,
                'title' => $validated['role'] === User::ROLE_TEACHER ? 'Class Adviser' : null,
                'department' => $validated['role'] === User::ROLE_TEACHER ? 'BSIT Program' : null,
            ]);

            if ($user->isStudent()) {
                $section = Section::query()->findOrFail($validated['section_id']);

                StudentProfile::create([
                    'user_id' => $user->id,
                    'section_id' => $section->id,
                    'student_number' => StudentProfile::nextStudentNumber($section),
                    'name' => $user->name,
                    'email' => $user->email,
                    'guardian' => $validated['guardian'] ?: 'Pending guardian details',
                    'contact' => $validated['phone'] ?: 'Not provided',
                    'address' => 'Address not yet provided',
                    'focus' => 'General Studies',
                    'status' => 'Regular',
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Registration successful. Your e-class record dashboard is ready.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}