<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    private const DEMO_PASSWORD = 'password123';

    public function landing(): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.index' : 'dashboard');
        }

        return view('landing', [
            'databaseReady' => $this->hasTables(['users', 'sections', 'student_profiles']),
            'demoAccounts' => $this->demoAccounts(),
            'demoPassword' => self::DEMO_PASSWORD,
        ]);
    }

    public function showLogin(): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.index' : 'dashboard');
        }

        return view('auth.login', [
            'databaseReady' => $this->hasTable('users'),
            'demoAccounts' => $this->demoAccounts(),
            'demoPassword' => self::DEMO_PASSWORD,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        if (! $this->hasTable('users')) {
            return back()
                ->with('error', $this->databaseSetupMessage())
                ->onlyInput('email');
        }

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

        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.index')->with('success', 'Login successful. Admin tools are ready.');
        }

        return redirect()->route('dashboard')->with('success', 'Login successful. Your e-class dashboard is ready.');
    }

    public function showRegister(): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.index' : 'dashboard');
        }

        $hasUsersTable = $this->hasTable('users');
        $hasSectionsTable = $this->hasTable('sections');

        return view('auth.register', [
            'databaseReady' => $hasUsersTable && $hasSectionsTable,
            'sections' => $hasSectionsTable
                ? Section::query()->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        if (! $this->hasTable('users') || ! $this->hasTable('sections')) {
            return back()
                ->with('error', $this->databaseSetupMessage())
                ->withInput($request->except(['password', 'password_confirmation']));
        }

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
                'title' => null,
                'department' => null,
            ]);

            if ($user->isStudent()) {
                $section = Section::query()->findOrFail($validated['section_id']);

                StudentProfile::create([
                    'user_id' => $user->id,
                    'section_id' => $section->id,
                    'student_number' => StudentProfile::nextStudentNumber($section),
                    'name' => $user->name,
                    'email' => $user->email,
                    'guardian' => $validated['guardian'] ?? null,
                    'contact' => $validated['phone'] ?? null,
                    'address' => null,
                    'focus' => null,
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

    private function hasTables(array $tables): bool
    {
        foreach ($tables as $table) {
            if (! $this->hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    private function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    private function demoAccounts(): array
    {
        if (! $this->hasTables(['users', 'student_profiles', 'sections'])) {
            return [];
        }

        return User::query()
            ->with('studentProfile.section')
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_TEACHER, User::ROLE_STUDENT])
            ->orderByRaw('CASE WHEN role = ? THEN 0 WHEN role = ? THEN 1 ELSE 2 END', [User::ROLE_ADMIN, User::ROLE_TEACHER])
            ->orderBy('name')
            ->get()
            ->map(function (User $user): array {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'section' => $user->studentProfile?->section?->name,
                ];
            })
            ->values()
            ->all();
    }

    private function databaseSetupMessage(): string
    {
        return 'Database is not initialized yet. Run "php artisan migrate --seed" and reload the page.';
    }
}
