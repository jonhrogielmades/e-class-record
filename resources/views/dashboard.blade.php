@extends('layouts.app')
@php use App\Support\EClassUi; @endphp

@section('active_page', 'dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', $user->isTeacher() ? 'Monitor sections, attendance performance, and recently recorded grades from one teacher workspace.' : 'View your class section, attendance rate, grade summary, and recent academic record updates.')

@section('header_meta')
    @if ($user->isTeacher())
        <span class="status-pill">{{ $teacherSnapshot['totalStudents'] }} learners</span>
        <span class="status-pill">{{ $teacherSnapshot['sections']->count() }} active sections</span>
        <span class="status-pill">{{ $teacherSnapshot['pendingGrades'] }} pending grade slots</span>
    @elseif ($studentSnapshot)
        <span class="status-pill">{{ $studentSnapshot['section']->name }}</span>
        <span class="status-pill">{{ $studentSnapshot['section']->schedule }}</span>
        <span class="status-pill">{{ $studentSnapshot['completedAssessments'] }} recorded assessments</span>
    @else
        <span class="status-pill">Profile unavailable</span>
    @endif
@endsection

@section('header_actions')
    @if ($user->isTeacher())
        <a href="{{ route('sections.index') }}" class="btn btn-primary btn-fit">Open Class List</a>
        <a href="{{ route('grades.index') }}" class="btn btn-outline btn-fit">Open Grading</a>
    @else
        <a href="{{ route('students.index') }}" class="btn btn-primary btn-fit">My Records</a>
        <a href="{{ route('grades.index') }}" class="btn btn-outline btn-fit">View Grades</a>
    @endif
@endsection

@section('content')
    @if ($user->isTeacher())
        @if ($teacherSnapshot['sections']->isEmpty())
            <section class="glass-card">
                <div class="empty-state">
                    <div>
                        <h3>No sections are available yet</h3>
                        <p>The teacher dashboard needs at least one section before it can show attendance and grading summaries.</p>
                        <div class="button-row no-print">
                            <a href="{{ route('sections.index') }}" class="btn btn-primary btn-fit">Go to Class List</a>
                        </div>
                    </div>
                </div>
            </section>
        @else
            <section class="stats-grid">
                <div class="glass-card glass-card-3d stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Total Learners</h3><div class="stat-value">{{ $teacherSnapshot['totalStudents'] }}</div><span class="stat-change emerald">Across advisory sections</span></div><div class="stat-icon emerald">T</div></div></div>
                <div class="glass-card glass-card-3d stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Grade Average</h3><div class="stat-value">{{ $teacherSnapshot['averageGrade'] }}%</div><span class="stat-change gold">{{ EClassUi::performanceLabel($teacherSnapshot['averageGrade']) }}</span></div><div class="stat-icon gold">G</div></div></div>
                <div class="glass-card glass-card-3d stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Attendance Rate</h3><div class="stat-value">{{ $teacherSnapshot['averageAttendanceRate'] }}%</div><span class="stat-change blue">Present + late counted</span></div><div class="stat-icon blue">A</div></div></div>
                <div class="glass-card glass-card-3d stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Pending Grades</h3><div class="stat-value">{{ $teacherSnapshot['pendingGrades'] }}</div><span class="stat-change rose">Based on current assessment records</span></div><div class="stat-icon rose">P</div></div></div>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>Section Snapshot</h2><p>Overview cards for each managed class section.</p></div></div>
                    <div class="quick-action-grid">
                        @foreach ($teacherSnapshot['sections'] as $summary)
                            <article class="action-card">
                                <div class="feature-badges">
                                    <span class="feature-badge">{{ $summary['section']->strand }}</span>
                                    <span class="feature-badge">{{ $summary['section']->room }}</span>
                                </div>
                                <h3>{{ $summary['section']->name }}</h3>
                                <p>{{ $summary['section']->schedule }}</p>
                                <div class="recent-session-meta">
                                    <span class="feature-badge">{{ $summary['studentCount'] }} learners</span>
                                    <span class="feature-badge">{{ $summary['attendanceRate'] }}% attendance</span>
                                    <span class="feature-badge">{{ $summary['averageGrade'] }}% average</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </article>
                <article class="glass-card activity-card">
                    <div class="section-head"><div><h2>Recent Activity</h2><p>Latest grading and attendance updates across your sections.</p></div></div>
                    <div class="activity-list">
                        @forelse ($teacherSnapshot['recentGrades']->take(3) as $grade)
                            <div class="activity-item">
                                <div class="activity-avatar success">{{ strtoupper(substr($grade['studentName'], 0, 2)) }}</div>
                                <div class="activity-content">
                                    <div class="activity-text"><strong>{{ $grade['studentName'] }}</strong> received {{ $grade['percentage'] }}% in {{ $grade['model']->title }}.</div>
                                    <div class="activity-time">{{ optional($grade['model']->recorded_at)->format('M j, Y g:i A') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state"><div><h3>No recent activity yet</h3><p>Attendance and grading updates will appear here after you save records for your sections.</p></div></div>
                        @endforelse
                        @foreach ($teacherSnapshot['recentAttendance']->take(3) as $record)
                            <div class="activity-item">
                                <div class="activity-avatar warning">{{ strtoupper(substr($record['studentName'], 0, 2)) }}</div>
                                <div class="activity-content">
                                    <div class="activity-text"><strong>{{ $record['studentName'] }}</strong> was marked {{ strtolower($record['statusLabel']) }} for {{ $record['model']->topic }}.</div>
                                    <div class="activity-time">{{ optional($record['model']->date)->format('M j, Y') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card chart-card">
                    <div class="section-head"><div><h2>Section Average by Class</h2><p>Current section grade averages based on recorded assessments.</p></div></div>
                    <canvas class="chart-canvas" data-chart-type="bar" data-labels="{{ e(json_encode($teacherSnapshot['sections']->map(fn ($summary) => str_replace('Section ', 'S', $summary['section']->name))->values()->all())) }}" data-values="{{ e(json_encode($teacherSnapshot['sections']->pluck('averageGrade')->values()->all())) }}" data-max="100" data-color="#d4a574"></canvas>
                </article>
                <article class="glass-card chart-card">
                    <div class="section-head"><div><h2>Recent Grade Trend</h2><p>The latest recorded grade percentages entered into the system.</p></div></div>
                    <canvas class="chart-canvas" data-chart-type="line" data-labels="{{ e(json_encode($teacherSnapshot['recentGrades']->take(6)->reverse()->values()->map(fn ($grade, $index) => 'G'.($index + 1))->all())) }}" data-values="{{ e(json_encode($teacherSnapshot['recentGrades']->take(6)->reverse()->values()->pluck('percentage')->all())) }}" data-max="100" data-color="#34d399" data-area-color="rgba(52, 211, 153, 0.14)"></canvas>
                </article>
            </section>

            <section class="glass-card">
                <div class="section-head"><div><h2>Recent Gradebook Entries</h2><p>Most recent assessments recorded across sections.</p></div></div>
                <div class="recent-session-list">
                    @foreach ($teacherSnapshot['recentGrades']->take(4) as $grade)
                        <div class="recent-session-card">
                            <span class="status-pill {{ $grade['performanceTone'] }}">{{ $grade['performanceLabel'] }}</span>
                            <h3>{{ $grade['studentName'] }}</h3>
                            <p>{{ $grade['model']->title }} - {{ $grade['model']->category }}</p>
                            <div class="recent-session-meta">
                                <span class="feature-badge">{{ $grade['model']->score }} / {{ $grade['model']->max_score }}</span>
                                <span class="feature-badge">{{ $grade['percentage'] }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @else
        @if (! $studentSnapshot)
            <section class="glass-card"><div class="empty-state"><div><h3>Student profile not found</h3><p>Your account is signed in, but the linked learner profile is missing. Open Settings or sign out and register again.</p></div></div></section>
        @else
            <section class="stats-grid">
                <div class="glass-card glass-card-3d stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Assigned Section</h3><div class="stat-value">{{ $studentSnapshot['section']->name }}</div><span class="stat-change emerald">{{ $studentSnapshot['section']->room }}</span></div><div class="stat-icon emerald">S</div></div></div>
                <div class="glass-card glass-card-3d stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Grade Average</h3><div class="stat-value">{{ $studentSnapshot['gradeAverage'] }}%</div><span class="stat-change gold">{{ EClassUi::performanceLabel($studentSnapshot['gradeAverage']) }}</span></div><div class="stat-icon gold">G</div></div></div>
                <div class="glass-card glass-card-3d stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Attendance Rate</h3><div class="stat-value">{{ $studentSnapshot['attendanceSummary']['rate'] }}%</div><span class="stat-change blue">{{ $studentSnapshot['attendanceSummary']['present'] }} present records</span></div><div class="stat-icon blue">A</div></div></div>
                <div class="glass-card glass-card-3d stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Assessments</h3><div class="stat-value">{{ $studentSnapshot['completedAssessments'] }}</div><span class="stat-change rose">Saved grade entries</span></div><div class="stat-icon rose">R</div></div></div>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>My Section</h2><p>{{ $studentSnapshot['section']->description }}</p></div></div>
                    <ul class="detail-list">
                        <li><strong>Section:</strong> {{ $studentSnapshot['section']->name }} - {{ $studentSnapshot['section']->strand }}</li>
                        <li><strong>Adviser:</strong> {{ $studentSnapshot['section']->adviser }}</li>
                        <li><strong>Schedule:</strong> {{ $studentSnapshot['section']->schedule }}</li>
                        <li><strong>Room:</strong> {{ $studentSnapshot['section']->room }}</li>
                    </ul>
                </article>
                <article class="glass-card">
                    <div class="section-head"><div><h2>Recent Assessments</h2><p>Your latest recorded grades inside the class record system.</p></div></div>
                    <div class="recent-session-list">
                        @foreach ($studentSnapshot['grades']->take(4) as $grade)
                            <div class="recent-session-card">
                                <span class="status-pill {{ EClassUi::performanceTone(($grade->score / $grade->max_score) * 100) }}">{{ EClassUi::performanceLabel(($grade->score / $grade->max_score) * 100) }}</span>
                                <h3>{{ $grade->title }}</h3>
                                <p>{{ $grade->category }}</p>
                                <div class="recent-session-meta">
                                    <span class="feature-badge">{{ $grade->score }} / {{ $grade->max_score }}</span>
                                    <span class="feature-badge">{{ number_format(($grade->score / $grade->max_score) * 100, 1) }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card chart-card">
                    <div class="section-head"><div><h2>Recent Grade Trend</h2><p>Your latest assessment percentages plotted over time.</p></div></div>
                    <canvas class="chart-canvas" data-chart-type="line" data-labels="{{ e(json_encode($studentSnapshot['grades']->take(6)->reverse()->values()->map(fn ($grade, $index) => 'A'.($index + 1))->all())) }}" data-values="{{ e(json_encode($studentSnapshot['grades']->take(6)->reverse()->values()->map(fn ($grade) => round(($grade->score / $grade->max_score) * 100, 1))->all())) }}" data-max="100" data-color="#34d399" data-area-color="rgba(52, 211, 153, 0.14)"></canvas>
                </article>
                <article class="glass-card chart-card">
                    <div class="section-head"><div><h2>Category Averages</h2><p>Grouped by quiz, exam, and project records.</p></div></div>
                    <canvas class="chart-canvas" data-chart-type="bar" data-labels="{{ e(json_encode($studentSnapshot['categoryAverages']->pluck('category')->all())) }}" data-values="{{ e(json_encode($studentSnapshot['categoryAverages']->pluck('average')->all())) }}" data-max="100" data-color="#38bdf8"></canvas>
                </article>
            </section>
        @endif
    @endif
@endsection


