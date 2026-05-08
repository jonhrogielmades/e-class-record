@extends('layouts.app')

@section('active_page', 'assignments')
@section('page_title', 'Assignments')
@section('page_subtitle', $user->isTeacher() ? 'Create and manage assignments, activities, and due dates per section.' : 'View assigned activities, due dates, and instructions for your section.')

@section('header_meta')
    @if ($user->isTeacher() && $activeSummary)
        <span class="status-pill">{{ $activeSummary['section']->name }}</span>
        <span class="status-pill">{{ $assignments->count() }} activities</span>
    @elseif ($user->isStudent() && $studentSnapshot)
        <span class="status-pill">{{ $studentSnapshot['section']->name }}</span>
        <span class="status-pill">{{ $assignments->count() }} activities</span>
    @else
        <span class="status-pill">No assignment data</span>
    @endif
@endsection

@section('header_actions')
    <a href="{{ route('grades.index') }}" class="btn btn-outline btn-fit">Grades</a>
@endsection

@section('content')
    @if ($user->isTeacher())
        @if (empty($activeSummary))
            <section class="glass-card"><div class="empty-state"><div><h3>No section available</h3><p>Create a section before adding assignments.</p></div></div></section>
        @else
            <section class="glass-card section-filter-block">
                <div class="section-head"><div><h2>Section Filter</h2><p>Choose which class receives or displays assignments.</p></div></div>
                <form method="GET" action="{{ route('assignments.index') }}" class="form-group-settings section-filter-form">
                    <label for="assignment-section">Section</label>
                    <select id="assignment-section" name="section" class="form-input" data-submit-on-change>
                        @foreach ($sectionSummaries as $summary)
                            <option value="{{ $summary['section']->id }}" @selected($activeSummary['section']->id === $summary['section']->id)>{{ $summary['section']->name }} - {{ $summary['section']->strand }}</option>
                        @endforeach
                    </select>
                </form>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>{{ $selectedAssignment ? 'Edit Assignment' : 'Create Assignment' }}</h2><p>Students receive a notification when a new assignment is created.</p></div></div>
                    <form method="POST" action="{{ $selectedAssignment ? route('assignments.update', $selectedAssignment) : route('assignments.store') }}" class="form-grid">
                        @csrf
                        @if ($selectedAssignment)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="section_id" value="{{ $activeSummary['section']->id }}">
                        <div class="form-group-settings full-width"><label for="assignment-title">Title</label><input id="assignment-title" class="form-input" name="title" value="{{ old('title', $selectedAssignment->title ?? '') }}"></div>
                        <div class="form-group-settings"><label for="assignment-category">Category</label><input id="assignment-category" class="form-input" name="category" value="{{ old('category', $selectedAssignment->category ?? 'Activity') }}"></div>
                        <div class="form-group-settings"><label for="assignment-due-date">Due Date</label><input id="assignment-due-date" class="form-input" type="date" name="due_date" value="{{ old('due_date', optional($selectedAssignment?->due_date)->format('Y-m-d')) }}"></div>
                        <div class="form-group-settings"><label for="assignment-max-score">Max Score</label><input id="assignment-max-score" class="form-input" type="number" min="1" name="max_score" value="{{ old('max_score', $selectedAssignment->max_score ?? '') }}"></div>
                        <div class="form-group-settings"><label for="assignment-status">Status</label><select id="assignment-status" class="form-input" name="status"><option @selected(old('status', $selectedAssignment->status ?? 'Assigned') === 'Assigned')>Assigned</option><option @selected(old('status', $selectedAssignment->status ?? '') === 'In Progress')>In Progress</option><option @selected(old('status', $selectedAssignment->status ?? '') === 'Closed')>Closed</option></select></div>
                        <div class="form-group-settings full-width"><label for="assignment-instructions">Instructions</label><textarea id="assignment-instructions" class="form-input" name="instructions">{{ old('instructions', $selectedAssignment->instructions ?? '') }}</textarea></div>
                        <div class="btn-group no-print">
                            <button type="submit" class="btn btn-primary btn-fit">{{ $selectedAssignment ? 'Save Assignment' : 'Create Assignment' }}</button>
                            @if ($selectedAssignment)
                                <a href="{{ route('assignments.index', ['section' => $activeSummary['section']->id]) }}" class="btn btn-outline btn-fit">New Assignment</a>
                            @endif
                        </div>
                    </form>
                </article>
                <article class="glass-card">
                    <div class="section-head"><div><h2>Assignment List</h2><p>Activities for the selected section.</p></div></div>
                    <div class="recent-session-list">
                        @forelse ($assignments as $assignment)
                            <article class="recent-session-card">
                                <span class="status-pill">{{ $assignment->status }}</span>
                                <h3>{{ $assignment->title }}</h3>
                                <p>{{ $assignment->instructions }}</p>
                                <div class="recent-session-meta">
                                    <span class="feature-badge">{{ $assignment->category }}</span>
                                    <span class="feature-badge">Due {{ optional($assignment->due_date)->format('M j, Y') ?? 'Anytime' }}</span>
                                    <span class="feature-badge">{{ $assignment->max_score ? $assignment->max_score.' pts' : 'No points' }}</span>
                                </div>
                                <div class="button-row entity-actions no-print">
                                    <a href="{{ route('assignments.index', ['section' => $activeSummary['section']->id, 'assignment' => $assignment->id]) }}" class="btn btn-outline btn-fit btn-xs">Edit</a>
                                    <form method="POST" action="{{ route('assignments.destroy', $assignment) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-fit btn-xs" onclick="return confirm('Delete this assignment?')">Delete</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="empty-state"><div><h3>No assignments yet</h3><p>Created assignments will appear here.</p></div></div>
                        @endforelse
                    </div>
                </article>
            </section>
        @endif
    @else
        <section class="glass-card">
            <div class="section-head"><div><h2>My Assignments</h2><p>Activities and due dates assigned to your section.</p></div></div>
            <div class="recent-session-list">
                @forelse ($assignments as $assignment)
                    <article class="recent-session-card">
                        <span class="status-pill">{{ $assignment->status }}</span>
                        <h3>{{ $assignment->title }}</h3>
                        <p>{{ $assignment->instructions }}</p>
                        <div class="recent-session-meta">
                            <span class="feature-badge">{{ $assignment->category }}</span>
                            <span class="feature-badge">Due {{ optional($assignment->due_date)->format('M j, Y') ?? 'Anytime' }}</span>
                            <span class="feature-badge">{{ $assignment->max_score ? $assignment->max_score.' pts' : 'No points' }}</span>
                        </div>
                    </article>
                @empty
                    <div class="empty-state"><div><h3>No assignments yet</h3><p>Assigned activities will appear here.</p></div></div>
                @endforelse
            </div>
        </section>
    @endif
@endsection
