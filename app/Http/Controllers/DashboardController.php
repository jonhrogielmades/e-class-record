<?php

namespace App\Http\Controllers;

use App\Services\EClassRecordService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly EClassRecordService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'user' => $user,
            'teacherSnapshot' => $user->isTeacher() ? $this->service->teacherSnapshot($user) : null,
            'studentSnapshot' => $user->isStudent() ? $this->service->studentSnapshot($user) : null,
        ]);
    }
}