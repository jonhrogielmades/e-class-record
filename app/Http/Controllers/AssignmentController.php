<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Assignment;
use App\Services\EClassRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function __construct(private readonly EClassRecordService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isStudent()) {
            $studentSnapshot = $this->service->studentSnapshot($user);
            $assignments = $studentSnapshot
                ? $studentSnapshot['section']->assignments()->latest('due_date')->get()
                : collect();

            return view('assignments.index', compact('user', 'studentSnapshot', 'assignments'));
        }

        $sectionSummaries = $this->service->sectionSummaries($user->sections()->orderBy('name')->get());
        $activeSummary = $sectionSummaries->first(fn (array $summary) => $summary['section']->id === (int) $request->query('section'))
            ?? $sectionSummaries->first();
        $selectedAssignment = null;
        $assignments = collect();

        if ($activeSummary) {
            $section = $activeSummary['section'];
            $assignments = $section->assignments()->latest('due_date')->get();
            $selectedAssignment = $section->assignments()->whereKey($request->query('assignment'))->first();
        }

        return view('assignments.index', compact('user', 'sectionSummaries', 'activeSummary', 'assignments', 'selectedAssignment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $section = $request->user()->sections()->findOrFail($validated['section_id']);

        $assignment = Assignment::create([
            ...$validated,
            'section_id' => $section->id,
            'teacher_id' => $request->user()->id,
        ]);

        $this->notifySectionStudents($section, 'New assignment', $assignment->title, [
            'assignment_id' => $assignment->id,
            'section_id' => $section->id,
        ]);

        return redirect()->route('assignments.index', ['section' => $section->id])->with('success', 'Assignment created successfully.');
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->ensureTeacherOwnsAssignment($request, $assignment);
        $validated = $this->validated($request);
        $section = $request->user()->sections()->findOrFail($validated['section_id']);

        $assignment->update([
            ...$validated,
            'section_id' => $section->id,
        ]);

        return redirect()->route('assignments.index', ['section' => $section->id, 'assignment' => $assignment->id])->with('success', 'Assignment updated successfully.');
    }

    public function destroy(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->ensureTeacherOwnsAssignment($request, $assignment);
        $sectionId = $assignment->section_id;
        $assignment->delete();

        return redirect()->route('assignments.index', ['section' => $sectionId])->with('success', 'Assignment deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'max_score' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['Assigned', 'In Progress', 'Closed'])],
            'instructions' => ['nullable', 'string'],
        ]);
    }

    private function ensureTeacherOwnsAssignment(Request $request, Assignment $assignment): void
    {
        abort_unless($assignment->section && $assignment->section->teacher_id === $request->user()->id, 403);
    }

    private function notifySectionStudents($section, string $title, string $message, array $data): void
    {
        $section->students()->with('user')->get()->each(function ($student) use ($title, $message, $data) {
            if (! $student->user) {
                return;
            }

            AppNotification::create([
                'user_id' => $student->user->id,
                'title' => $title,
                'message' => $message,
                'type' => 'assignment',
                'data' => $data,
            ]);
        });
    }
}
