@extends('layouts.app')
@php use App\Support\EClassUi; @endphp

@section('active_page', 'grading')
@section('page_title', $user->isTeacher() ? 'Grading' : 'Grades')
@section('page_subtitle', $user->isTeacher() ? 'Create, update, and delete quiz, exam, or project scores for the selected class section.' : 'Review your assessment history and grouped averages for quizzes, exams, and projects.')

@section('header_meta')
    @if ($user->isTeacher() && $activeSummary)
        <span class="status-pill">{{ $activeSummary['section']->name }}</span>
        <span class="status-pill">{{ $activeSummary['averageGrade'] }}% class average</span>
        <span class="status-pill">{{ $activeSummary['pendingGrades'] }} pending slots</span>
    @elseif ($user->isStudent() && $studentSnapshot)
        <span class="status-pill">{{ $studentSnapshot['student']->student_number }}</span>
        <span class="status-pill">{{ $studentSnapshot['gradeAverage'] }}% average</span>
        <span class="status-pill">{{ $studentSnapshot['grades']->count() }} assessments</span>
    @else
        <span class="status-pill">Profile unavailable</span>
    @endif
@endsection

@section('header_actions')
    @if ($user->isTeacher())
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-fit">Dashboard</a>
        <a href="{{ route('students.index', $activeSummary ? ['section' => $activeSummary['section']->id] : []) }}" class="btn btn-outline btn-fit">Attendance</a>
    @else
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-fit">Dashboard</a>
        <a href="{{ route('students.index') }}" class="btn btn-outline btn-fit">My Records</a>
    @endif
@endsection

@section('content')
    @if ($user->isTeacher())
        @if (empty($activeSummary))
            <section class="glass-card"><div class="empty-state"><div><h3>No section is available for grading</h3><p>Create a class section first, then return here to record quizzes, exams, and project scores.</p></div></div></section>
        @else
            <section class="glass-card section-filter-block">
                <div class="section-head"><div><h2>Section Filter</h2><p>Switch section to review another gradebook and leaderboard.</p></div></div>
                <form method="GET" action="{{ route('grades.index') }}" class="form-group-settings section-filter-form">
                    <label for="grading-section-filter">Section</label>
                    <select id="grading-section-filter" name="section" class="form-input" data-submit-on-change>
                        @foreach ($sectionSummaries as $summary)
                            <option value="{{ $summary['section']->id }}" @selected($activeSummary['section']->id === $summary['section']->id)>{{ $summary['section']->name }} - {{ $summary['section']->strand }}</option>
                        @endforeach
                    </select>
                </form>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>{{ $selectedGrade ? 'Edit Grade' : 'Create Grade' }}</h2><p>Use full CRUD controls for assessments in the current section.</p></div></div>
                    <form method="POST" action="{{ $selectedGrade ? route('grades.update', $selectedGrade) : route('grades.store') }}" class="form-grid">
                        @csrf
                        @if ($selectedGrade)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="section_id" value="{{ $activeSummary['section']->id }}">
                        <div class="form-group-settings full-width"><label for="grading-student">Learner</label><select id="grading-student" name="student_profile_id" class="form-input">
                            <option value="">Select learner</option>
                            @foreach ($activeSummary['roster'] as $record)
                                <option value="{{ $record['student']->id }}" @selected((string) old('student_profile_id', $selectedGrade->student_profile_id ?? '') === (string) $record['student']->id)>{{ $record['student']->name }} ({{ $record['student']->student_number }})</option>
                            @endforeach
                        </select></div>
                        <div class="form-group-settings full-width"><label for="assessment-title">Assessment Title</label><input id="assessment-title" class="form-input" type="text" name="title" value="{{ old('title', $selectedGrade->title ?? '') }}"></div>
                        <div class="form-group-settings"><label for="assessment-category">Category</label><select id="assessment-category" name="category" class="form-input"><option value="Quiz" @selected(old('category', $selectedGrade->category ?? 'Quiz') === 'Quiz')>Quiz</option><option value="Exam" @selected(old('category', $selectedGrade->category ?? '') === 'Exam')>Exam</option><option value="Project" @selected(old('category', $selectedGrade->category ?? '') === 'Project')>Project</option><option value="Performance Task" @selected(old('category', $selectedGrade->category ?? '') === 'Performance Task')>Performance Task</option></select></div>
                        <div class="form-group-settings"><label for="assessment-score">Score</label><input id="assessment-score" class="form-input" type="number" min="0" name="score" value="{{ old('score', $selectedGrade->score ?? '') }}"></div>
                        <div class="form-group-settings"><label for="assessment-max-score">Max Score</label><input id="assessment-max-score" class="form-input" type="number" min="1" name="max_score" value="{{ old('max_score', $selectedGrade->max_score ?? 100) }}"></div>
                        <div class="form-group-settings full-width"><label for="assessment-remarks">Remarks</label><textarea id="assessment-remarks" class="form-input" name="remarks">{{ old('remarks', $selectedGrade->remarks ?? '') }}</textarea></div>
                        <div class="btn-group no-print">
                            <button type="submit" class="btn btn-primary btn-fit">{{ $selectedGrade ? 'Save Changes' : 'Create Grade' }}</button>
                            @if ($selectedGrade)
                                <a href="{{ route('grades.index', ['section' => $activeSummary['section']->id]) }}" class="btn btn-outline btn-fit">New Grade</a>
                            @endif
                        </div>
                    </form>
                </article>
                <article class="glass-card chart-card">
                    <div class="section-head"><div><h2>Section Grade Summary</h2><p>Current learner averages inside the selected section.</p></div></div>
                    <canvas class="chart-canvas" data-chart-type="bar" data-labels="{{ e(json_encode($activeSummary['roster']->map(fn ($record) => explode(' ', $record['student']->name)[0])->all())) }}" data-values="{{ e(json_encode($activeSummary['roster']->pluck('gradeAverage')->all())) }}" data-max="100" data-color="#38bdf8"></canvas>
                </article>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>Leaderboard</h2><p>Average standing for each learner in the selected class.</p></div></div>
                    <div class="table-responsive"><table class="history-table"><thead><tr><th>Learner</th><th>Student No.</th><th>Average</th><th>Attendance</th></tr></thead><tbody>
                        @foreach ($activeSummary['roster'] as $record)
                            <tr><td>{{ $record['student']->name }}</td><td>{{ $record['student']->student_number }}</td><td><span class="status-pill {{ EClassUi::performanceTone($record['gradeAverage']) }}">{{ $record['gradeAverage'] }}%</span></td><td>{{ $record['attendanceSummary']['rate'] }}%</td></tr>
                        @endforeach
                    </tbody></table></div>
                </article>
                <article class="glass-card">
                    <div class="section-head"><div><h2>Grade Records</h2><p>All saved records filtered by section with edit and delete actions.</p></div></div>
                    <div class="table-responsive"><table class="history-table"><thead><tr><th>Date</th><th>Learner</th><th>Assessment</th><th>Category</th><th>Score</th><th>Percent</th><th>Actions</th></tr></thead><tbody>
                        @foreach ($gradeRecords as $grade)
                            <tr>
                                <td>{{ optional($grade['model']->recorded_at)->format('M j, Y') }}</td>
                                <td>{{ $grade['studentName'] }}</td>
                                <td>{{ $grade['model']->title }}</td>
                                <td>{{ $grade['model']->category }}</td>
                                <td>{{ $grade['model']->score }} / {{ $grade['model']->max_score }}</td>
                                <td><span class="status-pill {{ $grade['performanceTone'] }}">{{ $grade['percentage'] }}%</span></td>
                                <td><div class="button-row table-action-row"><a href="{{ route('grades.index', ['section' => $activeSummary['section']->id, 'grade' => $grade['model']->id]) }}" class="btn btn-outline btn-fit btn-xs">Edit</a><form method="POST" action="{{ route('grades.destroy', $grade['model']) }}">@csrf @method('DELETE') <button type="submit" class="btn btn-danger-outline btn-fit btn-xs" onclick="return confirm('Delete this grade record?')">Delete</button></form></div></td>
                            </tr>
                        @endforeach
                    </tbody></table></div>
                </article>
            </section>
        @endif
    @else
        @if (! $studentSnapshot)
            <section class="glass-card"><div class="empty-state"><div><h3>Student profile not found</h3><p>There is no linked learner profile available for this account, so grade history cannot be displayed.</p></div></div></section>
        @else
            <section class="stats-grid compact-stats">
                <div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>General Average</h3><div class="stat-value">{{ $studentSnapshot['gradeAverage'] }}%</div><span class="stat-change gold">{{ EClassUi::performanceLabel($studentSnapshot['gradeAverage']) }}</span></div><div class="stat-icon gold">G</div></div></div>
                <div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Latest Score</h3><div class="stat-value">{{ $studentSnapshot['latestGrade'] ? number_format(($studentSnapshot['latestGrade']->score / $studentSnapshot['latestGrade']->max_score) * 100, 1) . '%' : 'N/A' }}</div><span class="stat-change emerald">Most recent assessment</span></div><div class="stat-icon emerald">L</div></div></div>
                <div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Section</h3><div class="stat-value">{{ $studentSnapshot['section']->name }}</div><span class="stat-change blue">{{ $studentSnapshot['section']->room }}</span></div><div class="stat-icon blue">S</div></div></div>
                <div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Completed</h3><div class="stat-value">{{ $studentSnapshot['grades']->count() }}</div><span class="stat-change rose">Saved grade entries</span></div><div class="stat-icon rose">C</div></div></div>
            </section>

            <section class="section-grid two-column">
                <article class="glass-card chart-card"><div class="section-head"><div><h2>Assessment Trend</h2><p>Your latest assessment percentages plotted over time.</p></div></div><canvas class="chart-canvas" data-chart-type="line" data-labels="{{ e(json_encode($studentSnapshot['grades']->take(6)->reverse()->values()->map(fn ($grade, $index) => 'A'.($index + 1))->all())) }}" data-values="{{ e(json_encode($studentSnapshot['grades']->take(6)->reverse()->values()->map(fn ($grade) => round(($grade->score / $grade->max_score) * 100, 1))->all())) }}" data-max="100" data-color="#38bdf8" data-area-color="rgba(56, 189, 248, 0.14)"></canvas></article>
                <article class="glass-card"><div class="section-head"><div><h2>Category Summary</h2><p>Average score by assessment type.</p></div></div><div class="summary-grid">@foreach ($studentSnapshot['categoryAverages'] as $item)<div class="summary-item"><span class="summary-emphasis">{{ $item['category'] }}</span><strong>{{ $item['average'] }}%</strong></div>@endforeach</div></article>
            </section>

            <section class="glass-card">
                <div class="section-head"><div><h2>Grade History</h2><p>Complete saved assessment history for your student profile.</p></div></div>
                <div class="table-responsive"><table class="history-table"><thead><tr><th>Date</th><th>Assessment</th><th>Category</th><th>Score</th><th>Percent</th></tr></thead><tbody>
                    @foreach ($studentSnapshot['grades'] as $grade)
                        <tr><td>{{ optional($grade->recorded_at)->format('M j, Y') }}</td><td>{{ $grade->title }}</td><td>{{ $grade->category }}</td><td>{{ $grade->score }} / {{ $grade->max_score }}</td><td><span class="status-pill {{ EClassUi::performanceTone(($grade->score / $grade->max_score) * 100) }}">{{ number_format(($grade->score / $grade->max_score) * 100, 1) }}%</span></td></tr>
                    @endforeach
                </tbody></table></div>
            </section>
        @endif
    @endif
@endsection
