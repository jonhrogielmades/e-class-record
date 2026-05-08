<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\AttendanceRecord;
use App\Models\StudentProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'student_profile_id' => ['required', 'exists:student_profiles,id'],
            'date' => ['required', 'date'],
            'topic' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['present', 'late', 'absent'])],
            'remarks' => ['nullable', 'string'],
        ]);

        $section = $request->user()->sections()->findOrFail($validated['section_id']);
        $student = StudentProfile::query()->whereKey($validated['student_profile_id'])->where('section_id', $section->id)->firstOrFail();

        $attendance = AttendanceRecord::updateOrCreate(
            [
                'student_profile_id' => $student->id,
                'date' => $validated['date'],
            ],
            [
                'section_id' => $section->id,
                'marked_by' => $request->user()->id,
                'topic' => $validated['topic'],
                'status' => $validated['status'],
                'remarks' => $validated['remarks'],
            ]
        );

        $this->notifyStudent($student, 'Attendance recorded', "{$attendance->topic}: ".ucfirst($attendance->status), 'attendance', [
            'attendance_id' => $attendance->id,
            'section_id' => $section->id,
        ]);

        return redirect()->route('students.index', ['section' => $section->id])->with('success', 'Attendance record saved successfully.');
    }

    public function update(Request $request, AttendanceRecord $attendance): RedirectResponse
    {
        $this->ensureTeacherOwnsAttendance($request, $attendance);

        $validated = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'student_profile_id' => ['required', 'exists:student_profiles,id'],
            'date' => ['required', 'date'],
            'topic' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['present', 'late', 'absent'])],
            'remarks' => ['nullable', 'string'],
        ]);

        $section = $request->user()->sections()->findOrFail($validated['section_id']);
        $student = StudentProfile::query()->whereKey($validated['student_profile_id'])->where('section_id', $section->id)->firstOrFail();

        $attendance->update([
            'student_profile_id' => $student->id,
            'section_id' => $section->id,
            'marked_by' => $request->user()->id,
            'date' => $validated['date'],
            'topic' => $validated['topic'],
            'status' => $validated['status'],
            'remarks' => $validated['remarks'],
        ]);

        $this->notifyStudent($student, 'Attendance updated', "{$attendance->topic}: ".ucfirst($attendance->status), 'attendance', [
            'attendance_id' => $attendance->id,
            'section_id' => $section->id,
        ]);

        return redirect()->route('students.index', ['section' => $section->id, 'attendance' => $attendance->id])->with('success', 'Attendance record updated successfully.');
    }

    public function destroy(Request $request, AttendanceRecord $attendance): RedirectResponse
    {
        $this->ensureTeacherOwnsAttendance($request, $attendance);

        $sectionId = $attendance->section_id;
        $attendance->delete();

        return redirect()->route('students.index', ['section' => $sectionId])->with('success', 'Attendance record deleted successfully.');
    }

    private function ensureTeacherOwnsAttendance(Request $request, AttendanceRecord $attendance): void
    {
        abort_unless($attendance->section && $attendance->section->teacher_id === $request->user()->id, 403);
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
