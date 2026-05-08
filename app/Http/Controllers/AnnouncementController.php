<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AppNotification;
use App\Services\EClassRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(private readonly EClassRecordService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isStudent()) {
            $studentSnapshot = $this->service->studentSnapshot($user);
            $announcements = $studentSnapshot
                ? $studentSnapshot['section']->announcements()->with('author')->latest('published_at')->get()
                : collect();

            return view('announcements.index', compact('user', 'studentSnapshot', 'announcements'));
        }

        $sectionSummaries = $this->service->sectionSummaries($user->sections()->orderBy('name')->get());
        $activeSummary = $sectionSummaries->first(fn (array $summary) => $summary['section']->id === (int) $request->query('section'))
            ?? $sectionSummaries->first();
        $selectedAnnouncement = null;
        $announcements = collect();

        if ($activeSummary) {
            $section = $activeSummary['section'];
            $announcements = $section->announcements()->with('author')->latest('published_at')->get();
            $selectedAnnouncement = $section->announcements()->whereKey($request->query('announcement'))->first();
        }

        return view('announcements.index', compact('user', 'sectionSummaries', 'activeSummary', 'announcements', 'selectedAnnouncement'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $section = $request->user()->sections()->findOrFail($validated['section_id']);
        $announcement = Announcement::create([
            'section_id' => $section->id,
            'created_by' => $request->user()->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'published_at' => now(),
        ]);

        $this->notifySectionStudents($section, 'New announcement', $announcement->title, 'announcement', [
            'announcement_id' => $announcement->id,
            'section_id' => $section->id,
        ]);

        return redirect()->route('announcements.index', ['section' => $section->id])->with('success', 'Announcement published successfully.');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->ensureTeacherOwnsAnnouncement($request, $announcement);

        $validated = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $section = $request->user()->sections()->findOrFail($validated['section_id']);
        $announcement->update([
            'section_id' => $section->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
        ]);

        return redirect()->route('announcements.index', ['section' => $section->id, 'announcement' => $announcement->id])->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->ensureTeacherOwnsAnnouncement($request, $announcement);
        $sectionId = $announcement->section_id;
        $announcement->delete();

        return redirect()->route('announcements.index', ['section' => $sectionId])->with('success', 'Announcement deleted successfully.');
    }

    private function ensureTeacherOwnsAnnouncement(Request $request, Announcement $announcement): void
    {
        abort_unless($announcement->section && $announcement->section->teacher_id === $request->user()->id, 403);
    }

    private function notifySectionStudents($section, string $title, string $message, string $type, array $data): void
    {
        $section->students()->with('user')->get()->each(function ($student) use ($title, $message, $type, $data) {
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
        });
    }
}
