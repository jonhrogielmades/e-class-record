<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use App\Support\EClassUi;
use Illuminate\Support\Collection;

class EClassRecordService
{
    public const SEEDED_ASSESSMENT_COUNT = 4;

    public function roundValue(float|int $value, int $precision = 1): float
    {
        return round((float) $value, $precision);
    }

    public function gradePercentage(Grade $grade): float
    {
        if ((int) $grade->max_score === 0) {
            return 0;
        }

        return $this->roundValue(($grade->score / $grade->max_score) * 100, 1);
    }

    public function attendanceSummary(Collection $records): array
    {
        $summary = $records->reduce(function (array $carry, AttendanceRecord $record) {
            $status = strtolower($record->status);
            $carry[$status] = ($carry[$status] ?? 0) + 1;
            $carry['total']++;

            return $carry;
        }, [
            'present' => 0,
            'late' => 0,
            'absent' => 0,
            'total' => 0,
        ]);

        return [
            'present' => $summary['present'],
            'late' => $summary['late'],
            'absent' => $summary['absent'],
            'total' => $summary['total'],
            'rate' => $summary['total'] > 0
                ? $this->roundValue((($summary['present'] + $summary['late']) / $summary['total']) * 100, 1)
                : 0,
        ];
    }

    public function categoryAverages(Collection $grades): Collection
    {
        return $grades
            ->groupBy('category')
            ->map(function (Collection $items, string $category) {
                return [
                    'category' => $category,
                    'average' => $this->roundValue(
                        $items->avg(fn (Grade $grade) => $this->gradePercentage($grade)) ?? 0,
                        1
                    ),
                ];
            })
            ->sortByDesc('average')
            ->values();
    }

    public function studentRecord(StudentProfile $student): array
    {
        $student->loadMissing(['section', 'grades', 'attendanceRecords']);

        $grades = $student->grades->sortByDesc('recorded_at')->values();
        $attendance = $student->attendanceRecords->sortByDesc('date')->values();

        return [
            'student' => $student,
            'section' => $student->section,
            'grades' => $grades,
            'attendance' => $attendance,
            'latestGrade' => $grades->first(),
            'gradeAverage' => $grades->isNotEmpty()
                ? $this->roundValue($grades->avg(fn (Grade $grade) => $this->gradePercentage($grade)) ?? 0, 1)
                : 0,
            'categoryAverages' => $this->categoryAverages($grades),
            'attendanceSummary' => $this->attendanceSummary($attendance),
        ];
    }

    public function sectionSummary(Section $section): array
    {
        $section->loadMissing(['teacher', 'students.section', 'students.grades', 'students.attendanceRecords']);

        $roster = $section->students
            ->map(fn (StudentProfile $student) => $this->studentRecord($student))
            ->sortBy(fn (array $record) => $record['student']->name)
            ->values();

        $studentCount = $roster->count();
        $gradeEntries = $section->grades()->count();

        return [
            'section' => $section,
            'roster' => $roster,
            'studentCount' => $studentCount,
            'averageGrade' => $studentCount > 0
                ? $this->roundValue($roster->avg('gradeAverage') ?? 0, 1)
                : 0,
            'attendanceRate' => $studentCount > 0
                ? $this->roundValue($roster->avg(fn (array $record) => $record['attendanceSummary']['rate']) ?? 0, 1)
                : 0,
            'pendingGrades' => max(0, ($studentCount * self::SEEDED_ASSESSMENT_COUNT) - $gradeEntries),
        ];
    }

    public function sectionSummaries(Collection $sections): Collection
    {
        return $sections->map(fn (Section $section) => $this->sectionSummary($section))->values();
    }

    public function teacherSnapshot(User $teacher): array
    {
        $sections = $this->sectionSummaries($teacher->sections()->orderBy('name')->get());

        $studentIds = $sections
            ->flatMap(fn (array $summary) => collect($summary['roster'])->pluck('student.id'))
            ->unique()
            ->values();

        $recentGrades = $studentIds->isEmpty()
            ? collect()
            : $this->decorateGrades(
                Grade::query()
                    ->with(['student', 'section'])
                    ->whereIn('student_profile_id', $studentIds)
                    ->orderByDesc('recorded_at')
                    ->limit(8)
                    ->get()
            );

        $recentAttendance = $studentIds->isEmpty()
            ? collect()
            : $this->decorateAttendances(
                AttendanceRecord::query()
                    ->with(['student', 'section'])
                    ->whereIn('student_profile_id', $studentIds)
                    ->orderByDesc('date')
                    ->orderByDesc('updated_at')
                    ->limit(8)
                    ->get()
            );

        return [
            'sections' => $sections,
            'totalStudents' => $studentIds->count(),
            'averageGrade' => $sections->isNotEmpty() ? $this->roundValue($sections->avg('averageGrade') ?? 0, 1) : 0,
            'averageAttendanceRate' => $sections->isNotEmpty() ? $this->roundValue($sections->avg('attendanceRate') ?? 0, 1) : 0,
            'pendingGrades' => $sections->sum('pendingGrades'),
            'recentGrades' => $recentGrades,
            'recentAttendance' => $recentAttendance,
        ];
    }

    public function studentSnapshot(User $user): ?array
    {
        $student = $user->studentProfile()->with(['section', 'grades', 'attendanceRecords'])->first();

        if (! $student || ! $student->section) {
            return null;
        }

        $record = $this->studentRecord($student);
        $sectionSummary = $this->sectionSummary($student->section);

        return array_merge($record, [
            'classmates' => $sectionSummary['roster']
                ->reject(fn (array $item) => $item['student']->id === $student->id)
                ->values(),
            'completedAssessments' => $record['grades']->count(),
        ]);
    }

    public function decorateGrade(Grade $grade): array
    {
        $grade->loadMissing(['student', 'section']);
        $percentage = $this->gradePercentage($grade);

        return [
            'model' => $grade,
            'studentName' => $grade->student?->name ?? 'Unknown learner',
            'studentNumber' => $grade->student?->student_number ?? 'N/A',
            'sectionName' => $grade->section?->name ?? 'Unknown section',
            'percentage' => $percentage,
            'performanceLabel' => EClassUi::performanceLabel($percentage),
            'performanceTone' => EClassUi::performanceTone($percentage),
        ];
    }

    public function decorateGrades(Collection $grades): Collection
    {
        return $grades->map(fn (Grade $grade) => $this->decorateGrade($grade))->values();
    }

    public function decorateAttendance(AttendanceRecord $record): array
    {
        $record->loadMissing(['student', 'section']);

        return [
            'model' => $record,
            'studentName' => $record->student?->name ?? 'Unknown learner',
            'studentNumber' => $record->student?->student_number ?? 'N/A',
            'sectionName' => $record->section?->name ?? 'Unknown section',
            'statusLabel' => EClassUi::attendanceStatusLabel($record->status),
            'attendanceTone' => EClassUi::attendanceTone($record->status),
        ];
    }

    public function decorateAttendances(Collection $records): Collection
    {
        return $records->map(fn (AttendanceRecord $record) => $this->decorateAttendance($record))->values();
    }
}