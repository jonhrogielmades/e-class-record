<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionApiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Section::query()->withCount('students')->with('teacher:id,name,email')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'strand' => ['required', 'string', 'max:255'],
            'room' => ['required', 'string', 'max:255'],
            'schedule' => ['required', 'string', 'max:255'],
            'adviser' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $section = Section::create($validated);

        return response()->json(['data' => $section], 201);
    }

    public function show(Section $section): JsonResponse
    {
        return response()->json([
            'data' => $section->load(['teacher:id,name,email', 'students']),
        ]);
    }

    public function update(Request $request, Section $section): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'strand' => ['required', 'string', 'max:255'],
            'room' => ['required', 'string', 'max:255'],
            'schedule' => ['required', 'string', 'max:255'],
            'adviser' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $section->update($validated);

        return response()->json(['data' => $section]);
    }

    public function destroy(Section $section): JsonResponse
    {
        if ($section->students()->exists()) {
            return response()->json([
                'message' => 'Remove all learners before deleting this section.',
            ], 422);
        }

        $section->delete();

        return response()->json(null, 204);
    }
}
