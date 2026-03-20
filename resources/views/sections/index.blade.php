@extends('layouts.app')
@php use App\Support\EClassUi; @endphp

@section('active_page', 'class-list')
@section('page_title', $user->isTeacher() ? 'Class List' : 'My Class')
@section('page_subtitle', $user->isTeacher() ? 'Switch between sections and review the current learner roster with attendance and grade snapshots.' : 'Review your assigned section details, adviser information, and a quick snapshot of classmates.')

@section('header_meta')
    @if ($user->isTeacher() && $activeSummary)
        <span class="status-pill">{{ $activeSummary['section']->name }}</span>
        <span class="status-pill">{{ $activeSummary['studentCount'] }} learners</span>
        <span class="status-pill">{{ $activeSummary['section']->schedule }}</span>
    @elseif ($user->isStudent() && $studentSnapshot)
        <span class="status-pill">{{ $studentSnapshot['section']->name }}</span>
        <span class="status-pill">{{ $studentSnapshot['section']->room }}</span>
        <span class="status-pill">{{ $studentSnapshot['classmates']->count() + 1 }} learners</span>
    @else
        <span class="status-pill">Create your first section</span>
    @endif
@endsection

@section('header_actions')
    @if ($user->isTeacher())
        <a href="{{ route('students.index', $activeSummary ? ['section' => $activeSummary['section']->id] : []) }}" class="btn btn-primary btn-fit">Open Student Records</a>
        <a href="{{ route('grades.index', $activeSummary ? ['section' => $activeSummary['section']->id] : []) }}" class="btn btn-outline btn-fit">Go to Grading</a>
    @else
        <a href="{{ route('students.index') }}" class="btn btn-primary btn-fit">Attendance Records</a>
        <a href="{{ route('grades.index') }}" class="btn btn-outline btn-fit">Grade Summary</a>
    @endif
@endsection

@section('content')
    @if ($user->isTeacher())
        @if (empty($activeSummary))
            <section class="glass-card">
                <div class="section-head"><div><h2>Create Your First Section</h2><p>Add a class section to unlock roster, attendance, and grading views.</p></div></div>
                <form method="POST" action="{{ route('sections.store') }}" class="form-grid">
                    @csrf
                    <div class="form-group-settings"><label for="section-name">Section Name</label><input id="section-name" class="form-input" type="text" name="name" value="{{ old('name') }}"></div>
                    <div class="form-group-settings"><label for="section-strand">Strand / Label</label><input id="section-strand" class="form-input" type="text" name="strand" value="{{ old('strand') }}"></div>
                    <div class="form-group-settings"><label for="section-room">Room</label><input id="section-room" class="form-input" type="text" name="room" value="{{ old('room') }}"></div>
                    <div class="form-group-settings"><label for="section-schedule">Schedule</label><input id="section-schedule" class="form-input" type="text" name="schedule" value="{{ old('schedule') }}"></div>
                    <div class="form-group-settings full-width"><label for="section-adviser">Adviser</label><input id="section-adviser" class="form-input" type="text" name="adviser" value="{{ old('adviser', $user->name) }}"></div>
                    <div class="form-group-settings full-width"><label for="section-description">Description</label><textarea id="section-description" class="form-input" name="description">{{ old('description') }}</textarea></div>
                    <div class="btn-group no-print"><button type="submit" class="btn btn-primary btn-fit">Create Section</button></div>
                </form>
            </section>
        @else
            <section class="glass-card">
                <div class="section-head"><div><h2>Section Selector</h2><p>Choose a class section to inspect roster details and current performance status.</p></div></div>
                <div class="segment-control segment-control-links">
                    @foreach ($sectionSummaries as $summary)
                        <a href="{{ route('sections.index', ['section' => $summary['section']->id]) }}" class="segment-button {{ $activeSummary['section']->id === $summary['section']->id ? 'active' : '' }}">{{ $summary['section']->name }}</a>
                    @endforeach
                </div>
            </section>

            <section class="stats-grid compact-stats">
                <div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Enrolled Learners</h3><div class="stat-value">{{ $activeSummary['studentCount'] }}</div><span class="stat-change emerald">Class roster size</span></div><div class="stat-icon emerald">L</div></div></div>
                <div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Section Average</h3><div class="stat-value">{{ $activeSummary['averageGrade'] }}%</div><span class="stat-change gold">{{ EClassUi::performanceLabel($activeSummary['averageGrade']) }}</span></div><div class="stat-icon gold">G</div></div></div>
                <div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Attendance Rate</h3><div class="stat-value">{{ $activeSummary['attendanceRate'] }}%</div><span class="stat-change blue">Present + late counted</span></div><div class="stat-icon blue">A</div></div></div>
                <div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Pending Grades</h3><div class="stat-value">{{ $activeSummary['pendingGrades'] }}</div><span class="stat-change rose">Assessment checklist gap</span></div><div class="stat-icon rose">P</div></div></div>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>Section Overview</h2><p>{{ $activeSummary['section']->description }}</p></div></div>
                    <ul class="detail-list">
                        <li><strong>Section:</strong> {{ $activeSummary['section']->name }} - {{ $activeSummary['section']->strand }}</li>
                        <li><strong>Adviser:</strong> {{ $activeSummary['section']->adviser }}</li>
                        <li><strong>Schedule:</strong> {{ $activeSummary['section']->schedule }}</li>
                        <li><strong>Room:</strong> {{ $activeSummary['section']->room }}</li>
                    </ul>
                </article>
                <article class="glass-card">
                    <div class="section-head"><div><h2>Edit Section</h2><p>Update the currently selected class section.</p></div></div>
                    <form method="POST" action="{{ route('sections.update', $activeSummary['section']) }}" class="form-grid">
                        @csrf
                        @method('PUT')
                        <div class="form-group-settings"><label for="edit-section-name">Section Name</label><input id="edit-section-name" class="form-input" type="text" name="name" value="{{ old('name', $activeSummary['section']->name) }}"></div>
                        <div class="form-group-settings"><label for="edit-section-strand">Strand / Label</label><input id="edit-section-strand" class="form-input" type="text" name="strand" value="{{ old('strand', $activeSummary['section']->strand) }}"></div>
                        <div class="form-group-settings"><label for="edit-section-room">Room</label><input id="edit-section-room" class="form-input" type="text" name="room" value="{{ old('room', $activeSummary['section']->room) }}"></div>
                        <div class="form-group-settings"><label for="edit-section-schedule">Schedule</label><input id="edit-section-schedule" class="form-input" type="text" name="schedule" value="{{ old('schedule', $activeSummary['section']->schedule) }}"></div>
                        <div class="form-group-settings full-width"><label for="edit-section-adviser">Adviser</label><input id="edit-section-adviser" class="form-input" type="text" name="adviser" value="{{ old('adviser', $activeSummary['section']->adviser) }}"></div>
                        <div class="form-group-settings full-width"><label for="edit-section-description">Description</label><textarea id="edit-section-description" class="form-input" name="description">{{ old('description', $activeSummary['section']->description) }}</textarea></div>
                        <div class="btn-group no-print"><button type="submit" class="btn btn-primary btn-fit">Save Changes</button></div>
                    </form>
                    <form method="POST" action="{{ route('sections.destroy', $activeSummary['section']) }}" class="inline-form-delete no-print">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger-outline btn-fit" onclick="return confirm('Delete this section? The section must not contain any learners.')">Delete Section</button>
                    </form>
                </article>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>Create Section</h2><p>Add another class section to your workspace.</p></div></div>
                    <form method="POST" action="{{ route('sections.store') }}" class="form-grid">
                        @csrf
                        <div class="form-group-settings"><label for="create-section-name">Section Name</label><input id="create-section-name" class="form-input" type="text" name="name"></div>
                        <div class="form-group-settings"><label for="create-section-strand">Strand / Label</label><input id="create-section-strand" class="form-input" type="text" name="strand"></div>
                        <div class="form-group-settings"><label for="create-section-room">Room</label><input id="create-section-room" class="form-input" type="text" name="room"></div>
                        <div class="form-group-settings"><label for="create-section-schedule">Schedule</label><input id="create-section-schedule" class="form-input" type="text" name="schedule"></div>
                        <div class="form-group-settings full-width"><label for="create-section-adviser">Adviser</label><input id="create-section-adviser" class="form-input" type="text" name="adviser" value="{{ $user->name }}"></div>
                        <div class="form-group-settings full-width"><label for="create-section-description">Description</label><textarea id="create-section-description" class="form-input" name="description"></textarea></div>
                        <div class="btn-group no-print"><button type="submit" class="btn btn-outline btn-fit">Create Section</button></div>
                    </form>
                </article>
                <article class="glass-card">
                    <div class="section-head"><div><h2>Roster Cards</h2><p>Each card combines a learner profile with attendance and grading snapshots.</p></div></div>
                    <div class="roster-grid">
                        @forelse ($activeSummary['roster'] as $record)
                            <article class="student-card">
                                <div class="student-card-head">
                                    <div class="profile-avatar">{{ EClassUi::initials($record['student']->name) }}</div>
                                    <div>
                                        <h3>{{ $record['student']->name }}</h3>
                                        <p>{{ $record['student']->student_number }}</p>
                                    </div>
                                </div>
                                <p class="category-copy">{{ $record['student']->focus }}</p>
                                <div class="recent-session-meta">
                                    <span class="feature-badge">{{ $record['gradeAverage'] }}% average</span>
                                    <span class="feature-badge">{{ $record['attendanceSummary']['rate'] }}% attendance</span>
                                </div>
                            </article>
                        @empty
                            <div class="empty-state"><div><h3>No learners in this section yet</h3><p>Create student profiles from the Students page to populate this class roster.</p></div></div>
                        @endforelse
                    </div>
                </article>
            </section>
        @endif
    @else
        @if (! $studentSnapshot)
            <section class="glass-card"><div class="empty-state"><div><h3>Student profile not found</h3><p>Your account is signed in, but there is no linked learner profile to display on the class page.</p></div></div></section>
        @else
            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>Section Information</h2><p>{{ $studentSnapshot['section']->description }}</p></div></div>
                    <ul class="detail-list">
                        <li><strong>Section:</strong> {{ $studentSnapshot['section']->name }} - {{ $studentSnapshot['section']->strand }}</li>
                        <li><strong>Adviser:</strong> {{ $studentSnapshot['section']->adviser }}</li>
                        <li><strong>Schedule:</strong> {{ $studentSnapshot['section']->schedule }}</li>
                        <li><strong>Room:</strong> {{ $studentSnapshot['section']->room }}</li>
                    </ul>
                </article>
                <article class="glass-card">
                    <div class="section-head"><div><h2>Class Snapshot</h2><p>Your current class overview based on your section roster.</p></div></div>
                    <div class="roster-grid">
                        @foreach (collect([$studentSnapshot])->concat($studentSnapshot['classmates']) as $record)
                            <article class="student-card">
                                <div class="student-card-head">
                                    <div class="profile-avatar">{{ EClassUi::initials($record['student']->name) }}</div>
                                    <div>
                                        <h3>{{ $record['student']->name }}</h3>
                                        <p>{{ $record['student']->student_number }}</p>
                                    </div>
                                </div>
                                <p class="category-copy">{{ $record['student']->focus }}</p>
                                <div class="recent-session-meta">
                                    <span class="feature-badge">{{ $record['gradeAverage'] }}% average</span>
                                    <span class="feature-badge">{{ $record['attendanceSummary']['rate'] }}% attendance</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </article>
            </section>
        @endif
    @endif
@endsection

