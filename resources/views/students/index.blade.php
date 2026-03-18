@extends('layouts.app')
@php use App\Support\EClassUi; @endphp

@section('active_page', 'students')
@section('page_title', $user->isTeacher() ? 'Students' : 'My Records')
@section('page_subtitle', $user->isTeacher() ? 'Manage learner profiles and full attendance CRUD operations for the selected class section.' : 'Review your profile details and personal attendance history stored inside the class record system.')

@section('header_meta')
    @if ($user->isTeacher() && $activeSummary)
        <span class="status-pill">{{ $activeSummary['section']->name }}</span>
        <span class="status-pill">{{ $activeSummary['studentCount'] }} learners</span>
        <span class="status-pill">{{ $activeSummary['attendanceRate'] }}% attendance</span>
    @elseif ($user->isStudent() && $studentSnapshot)
        <span class="status-pill">{{ $studentSnapshot['student']->student_number }}</span>
        <span class="status-pill">{{ $studentSnapshot['section']->name }}</span>
        <span class="status-pill">{{ $studentSnapshot['attendanceSummary']['rate'] }}% attendance</span>
    @else
        <span class="status-pill">Profile unavailable</span>
    @endif
@endsection

@section('header_actions')
    @if ($user->isTeacher())
        <a href="{{ route('sections.index', $activeSummary ? ['section' => $activeSummary['section']->id] : []) }}" class="btn btn-primary btn-fit">Back to Class List</a>
        <a href="{{ route('grades.index', $activeSummary ? ['section' => $activeSummary['section']->id] : []) }}" class="btn btn-outline btn-fit">Open Grading</a>
    @else
        <a href="{{ route('sections.index') }}" class="btn btn-primary btn-fit">My Class</a>
        <a href="{{ route('grades.index') }}" class="btn btn-outline btn-fit">View Grades</a>
    @endif
@endsection

@section('content')
    @if ($user->isTeacher())
        @if (empty($activeSummary))
            <section class="glass-card"><div class="empty-state"><div><h3>No section is available for student management</h3><p>Create a class section first, then return here to add learner profiles and attendance records.</p></div></div></section>
        @else
            <section class="glass-card">
                <div class="section-head"><div><h2>Section Filter</h2><p>Switch section to manage another set of student and attendance records.</p></div></div>
                <form method="GET" action="{{ route('students.index') }}" class="form-group-settings section-filter-form">
                    <label for="records-section">Section</label>
                    <select id="records-section" name="section" class="form-input" data-submit-on-change>
                        @foreach ($sectionSummaries as $summary)
                            <option value="{{ $summary['section']->id }}" @selected($activeSummary['section']->id === $summary['section']->id)>{{ $summary['section']->name }} - {{ $summary['section']->strand }}</option>
                        @endforeach
                    </select>
                </form>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>Student Profiles</h2><p>Teacher-side student record CRUD for the current section.</p></div></div>
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
                                <ul class="detail-list compact-detail-list">
                                    <li><strong>Focus:</strong> {{ $record['student']->focus }}</li>
                                    <li><strong>Guardian:</strong> {{ $record['student']->guardian }}</li>
                                    <li><strong>Contact:</strong> {{ $record['student']->contact }}</li>
                                </ul>
                                <div class="recent-session-meta">
                                    <span class="feature-badge">{{ $record['gradeAverage'] }}% average</span>
                                    <span class="feature-badge">{{ $record['attendanceSummary']['rate'] }}% attendance</span>
                                </div>
                                <div class="button-row entity-actions no-print">
                                    <a href="{{ route('students.index', ['section' => $activeSummary['section']->id, 'student' => $record['student']->id]) }}" class="btn btn-outline btn-fit btn-xs">Edit</a>
                                    <form method="POST" action="{{ route('students.destroy', $record['student']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-fit btn-xs" onclick="return confirm('Delete this student profile and all of its attendance and grade records?')">Delete</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="empty-state"><div><h3>No student profiles yet</h3><p>Create a learner profile to populate the roster and attendance tools for this section.</p></div></div>
                        @endforelse
                    </div>
                </article>
                <article class="glass-card">
                    <div class="section-head"><div><h2>{{ $selectedStudent ? 'Edit Student' : 'Create Student' }}</h2><p>Save new learners or update the selected profile.</p></div></div>
                    <form method="POST" action="{{ $selectedStudent ? route('students.update', $selectedStudent) : route('students.store') }}" class="form-grid">
                        @csrf
                        @if ($selectedStudent)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="section_id" value="{{ $activeSummary['section']->id }}">
                        <div class="form-group-settings full-width"><label for="student-name">Full Name</label><input id="student-name" class="form-input" type="text" name="name" value="{{ old('name', $selectedStudent->name ?? '') }}"></div>
                        <div class="form-group-settings"><label for="student-email">Email</label><input id="student-email" class="form-input" type="email" name="email" value="{{ old('email', $selectedStudent->email ?? '') }}"></div>
                        <div class="form-group-settings"><label for="student-number">Student Number</label><input id="student-number" class="form-input" type="text" name="student_number" value="{{ old('student_number', $selectedStudent->student_number ?? '') }}"></div>
                        <div class="form-group-settings"><label for="student-focus">Focus</label><input id="student-focus" class="form-input" type="text" name="focus" value="{{ old('focus', $selectedStudent->focus ?? '') }}"></div>
                        <div class="form-group-settings"><label for="student-guardian">Guardian</label><input id="student-guardian" class="form-input" type="text" name="guardian" value="{{ old('guardian', $selectedStudent->guardian ?? '') }}"></div>
                        <div class="form-group-settings"><label for="student-contact">Contact</label><input id="student-contact" class="form-input" type="text" name="contact" value="{{ old('contact', $selectedStudent->contact ?? '') }}"></div>
                        <div class="form-group-settings full-width"><label for="student-address">Address</label><input id="student-address" class="form-input" type="text" name="address" value="{{ old('address', $selectedStudent->address ?? '') }}"></div>
                        <div class="form-group-settings"><label for="student-status">Status</label><input id="student-status" class="form-input" type="text" name="status" value="{{ old('status', $selectedStudent->status ?? 'Regular') }}"></div>
                        <div class="btn-group no-print">
                            <button type="submit" class="btn btn-primary btn-fit">{{ $selectedStudent ? 'Save Changes' : 'Create Student' }}</button>
                            @if ($selectedStudent)
                                <a href="{{ route('students.index', ['section' => $activeSummary['section']->id]) }}" class="btn btn-outline btn-fit">New Student</a>
                            @endif
                        </div>
                    </form>
                </article>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>{{ $selectedAttendance ? 'Edit Attendance' : 'Create Attendance' }}</h2><p>Save or update attendance for a learner on a specific class date.</p></div></div>
                    <form method="POST" action="{{ $selectedAttendance ? route('attendance.update', $selectedAttendance) : route('attendance.store') }}" class="form-grid">
                        @csrf
                        @if ($selectedAttendance)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="section_id" value="{{ $activeSummary['section']->id }}">
                        <div class="form-group-settings full-width"><label for="attendance-student">Learner</label><select id="attendance-student" name="student_profile_id" class="form-input">
                            <option value="">Select learner</option>
                            @foreach ($activeSummary['roster'] as $record)
                                <option value="{{ $record['student']->id }}" @selected((string) old('student_profile_id', $selectedAttendance->student_profile_id ?? ($selectedStudent->id ?? '')) === (string) $record['student']->id)>{{ $record['student']->name }} ({{ $record['student']->student_number }})</option>
                            @endforeach
                        </select></div>
                        <div class="form-group-settings"><label for="attendance-date">Date</label><input id="attendance-date" class="form-input" type="date" name="date" value="{{ old('date', optional($selectedAttendance?->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"></div>
                        <div class="form-group-settings"><label for="attendance-status">Status</label><select id="attendance-status" name="status" class="form-input"><option value="present" @selected(old('status', $selectedAttendance->status ?? 'present') === 'present')>Present</option><option value="late" @selected(old('status', $selectedAttendance->status ?? '') === 'late')>Late</option><option value="absent" @selected(old('status', $selectedAttendance->status ?? '') === 'absent')>Absent</option></select></div>
                        <div class="form-group-settings full-width"><label for="attendance-topic">Topic / Meeting</label><input id="attendance-topic" class="form-input" type="text" name="topic" value="{{ old('topic', $selectedAttendance->topic ?? '') }}"></div>
                        <div class="form-group-settings full-width"><label for="attendance-remarks">Remarks</label><textarea id="attendance-remarks" class="form-input" name="remarks">{{ old('remarks', $selectedAttendance->remarks ?? '') }}</textarea></div>
                        <div class="btn-group no-print">
                            <button type="submit" class="btn btn-primary btn-fit">{{ $selectedAttendance ? 'Save Attendance' : 'Create Attendance' }}</button>
                            @if ($selectedAttendance)
                                <a href="{{ route('students.index', ['section' => $activeSummary['section']->id]) }}" class="btn btn-outline btn-fit">New Attendance</a>
                            @endif
                        </div>
                    </form>
                </article>
                <article class="glass-card">
                    <div class="section-head"><div><h2>Attendance Records</h2><p>All attendance entries for the selected section with edit and delete actions.</p></div></div>
                    <div class="table-responsive">
                        <table class="history-table">
                            <thead><tr><th>Date</th><th>Learner</th><th>Topic</th><th>Status</th><th>Remarks</th><th>Actions</th></tr></thead>
                            <tbody>
                                @forelse ($attendanceRecords as $record)
                                    <tr>
                                        <td>{{ optional($record['model']->date)->format('M j, Y') }}</td>
                                        <td>{{ $record['studentName'] }}</td>
                                        <td>{{ $record['model']->topic }}</td>
                                        <td><span class="status-pill {{ $record['attendanceTone'] }}">{{ $record['statusLabel'] }}</span></td>
                                        <td>{{ $record['model']->remarks }}</td>
                                        <td>
                                            <div class="button-row table-action-row">
                                                <a href="{{ route('students.index', ['section' => $activeSummary['section']->id, 'attendance' => $record['model']->id]) }}" class="btn btn-outline btn-fit btn-xs">Edit</a>
                                                <form method="POST" action="{{ route('attendance.destroy', $record['model']) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger-outline btn-fit btn-xs" onclick="return confirm('Delete this attendance record?')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6">No attendance records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        @endif
    @else
        @if (! $studentSnapshot)
            <section class="glass-card"><div class="empty-state"><div><h3>Student profile not found</h3><p>There is no linked learner profile available for this account, so personal attendance records cannot be shown.</p></div></div></section>
        @else
            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>Profile Details</h2><p>Your saved learner profile and contact information.</p></div></div>
                    <div class="roster-grid single-card-grid">
                        <article class="student-card">
                            <div class="student-card-head"><div class="profile-avatar">{{ EClassUi::initials($studentSnapshot['student']->name) }}</div><div><h3>{{ $studentSnapshot['student']->name }}</h3><p>{{ $studentSnapshot['student']->student_number }}</p></div></div>
                            <ul class="detail-list compact-detail-list">
                                <li><strong>Focus:</strong> {{ $studentSnapshot['student']->focus }}</li>
                                <li><strong>Guardian:</strong> {{ $studentSnapshot['student']->guardian }}</li>
                                <li><strong>Contact:</strong> {{ $studentSnapshot['student']->contact }}</li>
                            </ul>
                            <div class="recent-session-meta"><span class="feature-badge">{{ $studentSnapshot['gradeAverage'] }}% average</span><span class="feature-badge">{{ $studentSnapshot['attendanceSummary']['rate'] }}% attendance</span></div>
                        </article>
                    </div>
                </article>
                <article class="glass-card">
                    <div class="section-head"><div><h2>Attendance Summary</h2><p>Present and late records are counted as attended meetings.</p></div></div>
                    <div class="summary-grid">
                        <div class="summary-item"><span class="summary-emphasis">Present</span><strong>{{ $studentSnapshot['attendanceSummary']['present'] }}</strong></div>
                        <div class="summary-item"><span class="summary-emphasis">Late</span><strong>{{ $studentSnapshot['attendanceSummary']['late'] }}</strong></div>
                        <div class="summary-item"><span class="summary-emphasis">Absent</span><strong>{{ $studentSnapshot['attendanceSummary']['absent'] }}</strong></div>
                    </div>
                    <div class="profile-note"><strong>Attendance Rate:</strong> {{ $studentSnapshot['attendanceSummary']['rate'] }}%</div>
                </article>
            </section>

            <section class="glass-card">
                <div class="section-head"><div><h2>Attendance Records</h2><p>Your complete attendance history for the currently seeded meetings.</p></div></div>
                <div class="table-responsive">
                    <table class="history-table">
                        <thead><tr><th>Date</th><th>Topic</th><th>Status</th><th>Remarks</th></tr></thead>
                        <tbody>
                            @foreach ($studentSnapshot['attendance'] as $record)
                                <tr>
                                    <td>{{ optional($record->date)->format('M j, Y') }}</td>
                                    <td>{{ $record->topic }}</td>
                                    <td><span class="status-pill {{ EClassUi::attendanceTone($record->status) }}">{{ EClassUi::attendanceStatusLabel($record->status) }}</span></td>
                                    <td>{{ $record->remarks }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    @endif
@endsection