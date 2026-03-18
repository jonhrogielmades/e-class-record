<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\EClassRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function __construct(private readonly EClassRecordService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isStudent()) {
            return view('sections.index', [
                'user' => $user,
                'studentSnapshot' => $this->service->studentSnapshot($user),
            ]);
        }

        $sectionSummaries = $this->service->sectionSummaries($user->sections()->orderBy('name')->get());
        $activeSummary = $sectionSummaries->first(fn (array $summary) => $summary['section']->id === (int) $request->query('section'))
            ?? $sectionSummaries->first();

        return view('sections.index', [
            'user' => $user,
            'sectionSummaries' => $sectionSummaries,
            'activeSummary' => $activeSummary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'strand' => ['required', 'string', 'max:255'],
            'room' => ['required', 'string', 'max:255'],
            'schedule' => ['required', 'string', 'max:255'],
            'adviser' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $section = $request->user()->sections()->create($validated);

        return redirect()->route('sections.index', ['section' => $section->id])->with('success', 'Section created successfully.');
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        $this->ensureTeacherOwnsSection($request, $section);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'strand' => ['required', 'string', 'max:255'],
            'room' => ['required', 'string', 'max:255'],
            'schedule' => ['required', 'string', 'max:255'],
            'adviser' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $section->update($validated);

        return redirect()->route('sections.index', ['section' => $section->id])->with('success', 'Section updated successfully.');
    }

    public function destroy(Request $request, Section $section): RedirectResponse
    {
        $this->ensureTeacherOwnsSection($request, $section);

        if ($section->students()->exists()) {
            return back()->with('error', 'Remove or move all learners in this section before deleting it.');
        }

        $section->delete();

        return redirect()->route('sections.index')->with('success', 'Section deleted successfully.');
    }

    private function ensureTeacherOwnsSection(Request $request, Section $section): void
    {
        abort_unless($section->teacher_id === $request->user()->id, 403);
    }
}