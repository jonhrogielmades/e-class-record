<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.index', [
            'user' => $request->user(),
            'teachers' => User::query()->where('role', User::ROLE_TEACHER)->orderBy('name')->get(),
            'students' => User::query()->where('role', User::ROLE_STUDENT)->with('studentProfile.section')->orderBy('name')->get(),
            'sections' => Section::query()->withCount('students')->with('teacher')->orderBy('name')->get(),
        ]);
    }

    public function storeTeacher(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'department' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_TEACHER,
            'department' => $validated['department'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return back()->with('success', 'Teacher account created successfully.');
    }

    public function destroyUser(Request $request, User $managedUser): RedirectResponse
    {
        abort_if($managedUser->id === $request->user()->id, 403);
        $managedUser->delete();

        return back()->with('success', 'User account deleted successfully.');
    }

    public function destroySection(Section $section): RedirectResponse
    {
        if ($section->students()->exists()) {
            return back()->with('error', 'Remove all learners before deleting this section.');
        }

        $section->delete();

        return back()->with('success', 'Section deleted successfully.');
    }
}
