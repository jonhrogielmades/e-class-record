@extends('layouts.app')

@section('active_page', 'announcements')
@section('page_title', 'Announcements')
@section('page_subtitle', $user->isTeacher() ? 'Publish and manage announcements for each class section.' : 'Read the latest announcements from your section.')

@section('header_meta')
    @if ($user->isTeacher() && $activeSummary)
        <span class="status-pill">{{ $activeSummary['section']->name }}</span>
        <span class="status-pill">{{ $announcements->count() }} announcements</span>
    @elseif ($user->isStudent() && $studentSnapshot)
        <span class="status-pill">{{ $studentSnapshot['section']->name }}</span>
        <span class="status-pill">{{ $announcements->count() }} announcements</span>
    @else
        <span class="status-pill">No section available</span>
    @endif
@endsection

@section('header_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-outline btn-fit">Dashboard</a>
@endsection

@section('content')
    @if ($user->isTeacher())
        @if (empty($activeSummary))
            <section class="glass-card"><div class="empty-state"><div><h3>No section available</h3><p>Create a section before publishing announcements.</p></div></div></section>
        @else
            <section class="glass-card section-filter-block">
                <div class="section-head"><div><h2>Section Filter</h2><p>Choose which class receives or displays announcements.</p></div></div>
                <form method="GET" action="{{ route('announcements.index') }}" class="form-group-settings section-filter-form">
                    <label for="announcement-section">Section</label>
                    <select id="announcement-section" name="section" class="form-input" data-submit-on-change>
                        @foreach ($sectionSummaries as $summary)
                            <option value="{{ $summary['section']->id }}" @selected($activeSummary['section']->id === $summary['section']->id)>{{ $summary['section']->name }} - {{ $summary['section']->strand }}</option>
                        @endforeach
                    </select>
                </form>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>{{ $selectedAnnouncement ? 'Edit Announcement' : 'Create Announcement' }}</h2><p>Students in the section receive a notification when a new announcement is posted.</p></div></div>
                    <form method="POST" action="{{ $selectedAnnouncement ? route('announcements.update', $selectedAnnouncement) : route('announcements.store') }}" class="form-grid">
                        @csrf
                        @if ($selectedAnnouncement)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="section_id" value="{{ $activeSummary['section']->id }}">
                        <div class="form-group-settings full-width"><label for="announcement-title">Title</label><input id="announcement-title" class="form-input" name="title" value="{{ old('title', $selectedAnnouncement->title ?? '') }}"></div>
                        <div class="form-group-settings full-width"><label for="announcement-body">Message</label><textarea id="announcement-body" class="form-input" name="body">{{ old('body', $selectedAnnouncement->body ?? '') }}</textarea></div>
                        <div class="btn-group no-print">
                            <button type="submit" class="btn btn-primary btn-fit">{{ $selectedAnnouncement ? 'Save Announcement' : 'Publish Announcement' }}</button>
                            @if ($selectedAnnouncement)
                                <a href="{{ route('announcements.index', ['section' => $activeSummary['section']->id]) }}" class="btn btn-outline btn-fit">New Announcement</a>
                            @endif
                        </div>
                    </form>
                </article>
                <article class="glass-card">
                    <div class="section-head"><div><h2>Announcement List</h2><p>Published messages for the selected section.</p></div></div>
                    <div class="recent-session-list">
                        @forelse ($announcements as $announcement)
                            <article class="recent-session-card">
                                <span class="status-pill">{{ optional($announcement->published_at)->format('M j, Y') }}</span>
                                <h3>{{ $announcement->title }}</h3>
                                <p>{{ $announcement->body }}</p>
                                <div class="button-row entity-actions no-print">
                                    <a href="{{ route('announcements.index', ['section' => $activeSummary['section']->id, 'announcement' => $announcement->id]) }}" class="btn btn-outline btn-fit btn-xs">Edit</a>
                                    <form method="POST" action="{{ route('announcements.destroy', $announcement) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-fit btn-xs" onclick="return confirm('Delete this announcement?')">Delete</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="empty-state"><div><h3>No announcements yet</h3><p>Published announcements will appear here.</p></div></div>
                        @endforelse
                    </div>
                </article>
            </section>
        @endif
    @else
        <section class="glass-card">
            <div class="section-head"><div><h2>Section Announcements</h2><p>Latest messages from your teacher.</p></div></div>
            <div class="recent-session-list">
                @forelse ($announcements as $announcement)
                    <article class="recent-session-card">
                        <span class="status-pill">{{ optional($announcement->published_at)->format('M j, Y') }}</span>
                        <h3>{{ $announcement->title }}</h3>
                        <p>{{ $announcement->body }}</p>
                    </article>
                @empty
                    <div class="empty-state"><div><h3>No announcements yet</h3><p>Your section announcements will appear here.</p></div></div>
                @endforelse
            </div>
        </section>
    @endif
@endsection
