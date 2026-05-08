<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use App\Services\EClassRecordService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardianController extends Controller
{
    public function __construct(private readonly EClassRecordService $service)
    {
    }

    public function index(): View
    {
        return view('guardian.index', [
            'studentSnapshot' => null,
        ]);
    }

    public function lookup(Request $request): View
    {
        $validated = $request->validate([
            'student_number' => ['required', 'string', 'max:255'],
            'access_code' => ['required', 'string', 'max:255'],
        ]);

        $student = StudentProfile::query()
            ->with(['section', 'grades', 'attendanceRecords'])
            ->where('student_number', $validated['student_number'])
            ->where(function ($query) use ($validated) {
                $query->where('guardian', $validated['access_code'])
                    ->orWhere('contact', $validated['access_code']);
            })
            ->first();

        return view('guardian.index', [
            'studentSnapshot' => $student ? $this->service->studentRecord($student) : null,
            'lookupFailed' => ! $student,
        ]);
    }
}
