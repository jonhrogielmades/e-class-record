<?php

namespace App\Http\Controllers;

use App\Services\EClassRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly EClassRecordService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('settings.index', [
            'user' => $user,
            'studentSnapshot' => $user->isStudent() ? $this->service->studentSnapshot($user) : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isTeacher()) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'department' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:255'],
            ]);

            $user->update([
                'name' => $validated['name'],
                'department' => $validated['department'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ]);

            return back()->with('success', 'Settings updated successfully.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'guardian' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
            ]);

            if ($user->studentProfile) {
                $user->studentProfile->update([
                    'name' => $validated['name'],
                    'guardian' => $validated['guardian'] ?? null,
                    'contact' => $validated['phone'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Settings updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        DB::transaction(function () use ($user) {
            $user->delete();
        });

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Account deleted successfully.');
    }
}
