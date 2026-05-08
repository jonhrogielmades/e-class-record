<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Grade;
use App\Models\StudentProfile;
use App\Services\EClassRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function __construct(private readonly EClassRecordService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isStudent()) {
            return view('grades.index', [
                'user' => $user,
                'studentSnapshot' => $this->service->studentSnapshot($user),
            ]);
        }

        $sectionSummaries = $this->service->sectionSummaries($user->sections()->orderBy('name')->get());
        $activeSummary = $sectionSummaries->first(fn (array $summary) => $summary['section']->id === (int) $request->query('section'))
            ?? $sectionSummaries->first();

        $gradeRecords = collect();
        $selectedGrade = null;

        if ($activeSummary) {
            $section = $activeSummary['section'];
            $gradeQuery = $section->grades()->with(['student', 'section'])->orderByDesc('recorded_at');

            if ($request->filled('category')) {
                $gradeQuery->where('category', $request->query('category'));
            }

            if ($request->filled('search')) {
                $search = trim((string) $request->query('search'));
                $gradeQuery->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($studentQuery) use ($search) {
                            $studentQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('student_number', 'like', "%{$search}%");
                        });
                });
            }

            $gradeRecords = $this->service->decorateGrades($gradeQuery->get());
            $selectedGrade = $section->grades()->with(['student', 'section'])->whereKey($request->query('grade'))->first();
        }

        return view('grades.index', [
            'user' => $user,
            'sectionSummaries' => $sectionSummaries,
            'activeSummary' => $activeSummary,
            'gradeRecords' => $gradeRecords,
            'selectedGrade' => $selectedGrade,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'student_profile_id' => ['required', 'exists:student_profiles,id'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['Quiz', 'Exam', 'Project', 'Performance Task'])],
            'score' => ['required', 'integer', 'min:0'],
            'max_score' => ['required', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string'],
        ]);

        if ((int) $validated['score'] > (int) $validated['max_score']) {
            return back()->withInput()->with('error', 'Enter a valid score from 0 up to the max score.');
        }

        $section = $request->user()->sections()->findOrFail($validated['section_id']);
        $student = StudentProfile::query()->whereKey($validated['student_profile_id'])->where('section_id', $section->id)->firstOrFail();

        $grade = Grade::create([
            'student_profile_id' => $student->id,
            'section_id' => $section->id,
            'recorded_by' => $request->user()->id,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'score' => $validated['score'],
            'max_score' => $validated['max_score'],
            'remarks' => $validated['remarks'],
            'recorded_at' => now(),
        ]);

        $this->notifyStudent($student, 'New grade recorded', "{$grade->title}: {$grade->score}/{$grade->max_score}", 'grade', [
            'grade_id' => $grade->id,
            'section_id' => $section->id,
        ]);

        return redirect()->route('grades.index', ['section' => $section->id])->with('success', 'Grade record created successfully.');
    }

    public function update(Request $request, Grade $grade): RedirectResponse
    {
        $this->ensureTeacherOwnsGrade($request, $grade);

        $validated = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'student_profile_id' => ['required', 'exists:student_profiles,id'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['Quiz', 'Exam', 'Project', 'Performance Task'])],
            'score' => ['required', 'integer', 'min:0'],
            'max_score' => ['required', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string'],
        ]);

        if ((int) $validated['score'] > (int) $validated['max_score']) {
            return back()->withInput()->with('error', 'Enter a valid score from 0 up to the max score.');
        }

        $section = $request->user()->sections()->findOrFail($validated['section_id']);
        $student = StudentProfile::query()->whereKey($validated['student_profile_id'])->where('section_id', $section->id)->firstOrFail();

        $grade->update([
            'student_profile_id' => $student->id,
            'section_id' => $section->id,
            'recorded_by' => $request->user()->id,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'score' => $validated['score'],
            'max_score' => $validated['max_score'],
            'remarks' => $validated['remarks'],
        ]);

        $this->notifyStudent($student, 'Grade updated', "{$grade->title}: {$grade->score}/{$grade->max_score}", 'grade', [
            'grade_id' => $grade->id,
            'section_id' => $section->id,
        ]);

        return redirect()->route('grades.index', ['section' => $section->id, 'grade' => $grade->id])->with('success', 'Grade record updated successfully.');
    }

    public function destroy(Request $request, Grade $grade): RedirectResponse
    {
        $this->ensureTeacherOwnsGrade($request, $grade);

        $sectionId = $grade->section_id;
        $grade->delete();

        return redirect()->route('grades.index', ['section' => $sectionId])->with('success', 'Grade record deleted successfully.');
    }

    private function ensureTeacherOwnsGrade(Request $request, Grade $grade): void
    {
        abort_unless($grade->section && $grade->section->teacher_id === $request->user()->id, 403);
    }

    private function notifyStudent(StudentProfile $student, string $title, string $message, string $type, array $data): void
    {
        if (! $student->user) {
            return;
        }

        AppNotification::create([
            'user_id' => $student->user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
        ]);
    }
}
