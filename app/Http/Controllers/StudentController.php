<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use App\Services\EClassRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(private readonly EClassRecordService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isStudent()) {
            return view('students.index', [
                'user' => $user,
                'studentSnapshot' => $this->service->studentSnapshot($user),
            ]);
        }

        $sectionSummaries = $this->service->sectionSummaries($user->sections()->orderBy('name')->get());
        $activeSummary = $sectionSummaries->first(fn (array $summary) => $summary['section']->id === (int) $request->query('section'))
            ?? $sectionSummaries->first();

        $selectedStudent = null;
        $selectedAttendance = null;
        $attendanceRecords = collect();

        if ($activeSummary) {
            $section = $activeSummary['section'];
            $selectedStudent = $section->students()->whereKey($request->query('student'))->first();
            $attendanceRecords = $this->service->decorateAttendances(
                $section->attendanceRecords()->with(['student', 'section'])->orderByDesc('date')->get()
            );
            $selectedAttendance = $section->attendanceRecords()->with(['student', 'section'])->whereKey($request->query('attendance'))->first();
        }

        return view('students.index', [
            'user' => $user,
            'sectionSummaries' => $sectionSummaries,
            'activeSummary' => $activeSummary,
            'selectedStudent' => $selectedStudent,
            'attendanceRecords' => $attendanceRecords,
            'selectedAttendance' => $selectedAttendance,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'student_number' => ['nullable', 'string', 'max:255', 'unique:student_profiles,student_number'],
            'guardian' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'focus' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
        ]);

        $section = $request->user()->sections()->findOrFail($validated['section_id']);

        StudentProfile::create([
            'section_id' => $section->id,
            'student_number' => $validated['student_number'] ?: StudentProfile::nextStudentNumber($section),
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'guardian' => $validated['guardian'] ?: 'Pending guardian details',
            'contact' => $validated['contact'] ?: 'Not provided',
            'address' => $validated['address'] ?: 'Address not yet provided',
            'focus' => $validated['focus'] ?: 'General Studies',
            'status' => $validated['status'] ?: 'Regular',
        ]);

        return redirect()->route('students.index', ['section' => $section->id])->with('success', 'Student profile created successfully.');
    }

    public function update(Request $request, StudentProfile $student): RedirectResponse
    {
        $this->ensureTeacherOwnsStudent($request, $student);

        $validated = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'student_number' => ['required', 'string', 'max:255', Rule::unique('student_profiles', 'student_number')->ignore($student->id)],
            'guardian' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'focus' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
        ]);

        $section = $request->user()->sections()->findOrFail($validated['section_id']);

        $student->update([
            'section_id' => $section->id,
            'student_number' => $validated['student_number'],
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'guardian' => $validated['guardian'] ?: 'Pending guardian details',
            'contact' => $validated['contact'] ?: 'Not provided',
            'address' => $validated['address'] ?: 'Address not yet provided',
            'focus' => $validated['focus'] ?: 'General Studies',
            'status' => $validated['status'] ?: 'Regular',
        ]);

        if ($student->user) {
            $student->user->update([
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $student->contact,
            ]);
        }

        return redirect()->route('students.index', ['section' => $section->id, 'student' => $student->id])->with('success', 'Student profile updated successfully.');
    }

    public function destroy(Request $request, StudentProfile $student): RedirectResponse
    {
        $this->ensureTeacherOwnsStudent($request, $student);

        $sectionId = $student->section_id;

        DB::transaction(function () use ($student) {
            if ($student->user) {
                $student->user->delete();
                return;
            }

            $student->delete();
        });

        return redirect()->route('students.index', ['section' => $sectionId])->with('success', 'Student profile deleted successfully.');
    }

    private function ensureTeacherOwnsStudent(Request $request, StudentProfile $student): void
    {
        abort_unless($student->section && $student->section->teacher_id === $request->user()->id, 403);
    }
}