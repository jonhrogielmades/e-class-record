<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Services\EClassRecordService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(private readonly EClassRecordService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isTeacher()) {
            $sectionSummaries = $this->service->sectionSummaries($user->sections()->orderBy('name')->get());
            $sectionIds = $sectionSummaries->pluck('section.id');
            $grades = Grade::query()->whereIn('section_id', $sectionIds)->get();
            $attendance = AttendanceRecord::query()->whereIn('section_id', $sectionIds)->get();

            return view('analytics.index', [
                'user' => $user,
                'sectionSummaries' => $sectionSummaries,
                'gradeTrend' => $this->gradeTrend($grades),
                'attendanceMix' => $this->attendanceMix($attendance),
                'categoryAverages' => $this->service->categoryAverages($grades),
            ]);
        }

        $studentSnapshot = $this->service->studentSnapshot($user);

        return view('analytics.index', [
            'user' => $user,
            'studentSnapshot' => $studentSnapshot,
            'gradeTrend' => $studentSnapshot ? $this->gradeTrend($studentSnapshot['grades']) : collect(),
            'attendanceMix' => $studentSnapshot ? $this->attendanceMix($studentSnapshot['attendance']) : collect(),
            'categoryAverages' => $studentSnapshot['categoryAverages'] ?? collect(),
        ]);
    }

    private function gradeTrend(Collection $grades): Collection
    {
        return $grades
            ->sortBy('recorded_at')
            ->take(-8)
            ->values()
            ->map(fn (Grade $grade, int $index) => [
                'label' => $grade->recorded_at?->format('M j') ?? 'G'.($index + 1),
                'value' => $this->service->gradePercentage($grade),
            ]);
    }

    private function attendanceMix(Collection $records): Collection
    {
        return collect(['present', 'late', 'absent'])->map(fn (string $status) => [
            'label' => ucfirst($status),
            'value' => $records->where('status', $status)->count(),
        ]);
    }
}
