<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Models\Section;
use App\Services\EClassRecordService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly EClassRecordService $service)
    {
    }

    public function gradesCsv(Request $request): StreamedResponse
    {
        $grades = $this->gradeRows($request);

        return $this->csvDownload('grade-report.csv', [
            ['Date', 'Section', 'Student No.', 'Student', 'Assessment', 'Category', 'Score', 'Max Score', 'Percent', 'Remarks'],
            ...$grades->map(fn (array $row) => [
                $row['date'],
                $row['section'],
                $row['student_number'],
                $row['student'],
                $row['assessment'],
                $row['category'],
                $row['score'],
                $row['max_score'],
                $row['percent'],
                $row['remarks'],
            ])->all(),
        ]);
    }

    public function attendanceCsv(Request $request): StreamedResponse
    {
        $records = $this->attendanceRows($request);

        return $this->csvDownload('attendance-report.csv', [
            ['Date', 'Section', 'Student No.', 'Student', 'Topic', 'Status', 'Remarks'],
            ...$records->map(fn (array $row) => [
                $row['date'],
                $row['section'],
                $row['student_number'],
                $row['student'],
                $row['topic'],
                $row['status'],
                $row['remarks'],
            ])->all(),
        ]);
    }

    public function print(Request $request): View
    {
        return view('reports.print', [
            'user' => $request->user(),
            'grades' => $this->gradeRows($request),
            'attendanceRecords' => $this->attendanceRows($request),
            'sectionName' => $this->reportSection($request)?->name ?? 'All assigned records',
        ]);
    }

    private function gradeRows(Request $request): Collection
    {
        $query = Grade::query()
            ->with(['student', 'section'])
            ->orderByDesc('recorded_at');

        $this->scopeReportQuery($request, $query);

        return $query->get()->map(function (Grade $grade): array {
            $percent = $this->service->gradePercentage($grade);

            return [
                'date' => optional($grade->recorded_at)->format('Y-m-d'),
                'section' => $grade->section?->name ?? 'N/A',
                'student_number' => $grade->student?->student_number ?? 'N/A',
                'student' => $grade->student?->name ?? 'Unknown',
                'assessment' => $grade->title,
                'category' => $grade->category,
                'score' => $grade->score,
                'max_score' => $grade->max_score,
                'percent' => $percent,
                'remarks' => $grade->remarks,
            ];
        });
    }

    private function attendanceRows(Request $request): Collection
    {
        $query = AttendanceRecord::query()
            ->with(['student', 'section'])
            ->orderByDesc('date');

        $this->scopeReportQuery($request, $query);

        return $query->get()->map(fn (AttendanceRecord $record): array => [
            'date' => optional($record->date)->format('Y-m-d'),
            'section' => $record->section?->name ?? 'N/A',
            'student_number' => $record->student?->student_number ?? 'N/A',
            'student' => $record->student?->name ?? 'Unknown',
            'topic' => $record->topic,
            'status' => ucfirst($record->status),
            'remarks' => $record->remarks,
        ]);
    }

    private function scopeReportQuery(Request $request, $query): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            if ($request->filled('section')) {
                $query->where('section_id', $request->integer('section'));
            }

            return;
        }

        if ($user->isTeacher()) {
            $sectionIds = $user->sections()->pluck('id');
            $query->whereIn('section_id', $sectionIds);

            if ($request->filled('section')) {
                $query->where('section_id', $request->integer('section'));
            }

            return;
        }

        $studentId = $user->studentProfile?->id;
        $query->where('student_profile_id', $studentId ?: 0);
    }

    private function reportSection(Request $request): ?Section
    {
        if (! $request->filled('section')) {
            return null;
        }

        return Section::query()->find($request->integer('section'));
    }

    private function csvDownload(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
