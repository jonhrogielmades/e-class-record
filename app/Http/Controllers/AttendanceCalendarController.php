<?php

namespace App\Http\Controllers;

use App\Services\EClassRecordService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceCalendarController extends Controller
{
    public function __construct(private readonly EClassRecordService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $month = Carbon::parse($request->query('month', now()->format('Y-m-01')))->startOfMonth();

        if ($user->isStudent()) {
            $studentSnapshot = $this->service->studentSnapshot($user);
            $records = $studentSnapshot
                ? $studentSnapshot['student']->attendanceRecords()->whereBetween('date', [$month, $month->copy()->endOfMonth()])->get()
                : collect();

            return view('attendance.calendar', [
                'user' => $user,
                'month' => $month,
                'studentSnapshot' => $studentSnapshot,
                'recordsByDate' => $records->groupBy(fn ($record) => $record->date->format('Y-m-d')),
            ]);
        }

        $sectionSummaries = $this->service->sectionSummaries($user->sections()->orderBy('name')->get());
        $activeSummary = $sectionSummaries->first(fn (array $summary) => $summary['section']->id === (int) $request->query('section'))
            ?? $sectionSummaries->first();
        $records = $activeSummary
            ? $activeSummary['section']->attendanceRecords()->with('student')->whereBetween('date', [$month, $month->copy()->endOfMonth()])->get()
            : collect();

        return view('attendance.calendar', [
            'user' => $user,
            'month' => $month,
            'sectionSummaries' => $sectionSummaries,
            'activeSummary' => $activeSummary,
            'recordsByDate' => $records->groupBy(fn ($record) => $record->date->format('Y-m-d')),
        ]);
    }
}
