@extends('layouts.app')

@section('active_page', 'analytics')
@section('page_title', 'Analytics')
@section('page_subtitle', $user->isTeacher() ? 'Review section performance, attendance distribution, and assessment category averages.' : 'Track your grade trend, attendance distribution, and assessment category averages.')

@section('header_meta')
    @if ($user->isTeacher())
        <span class="status-pill">{{ $sectionSummaries->count() }} sections</span>
        <span class="status-pill">{{ $sectionSummaries->sum('studentCount') }} learners</span>
        <span class="status-pill">{{ $sectionSummaries->avg('averageGrade') ? round($sectionSummaries->avg('averageGrade'), 1) : 0 }}% average</span>
    @elseif (! empty($studentSnapshot))
        <span class="status-pill">{{ $studentSnapshot['section']->name }}</span>
        <span class="status-pill">{{ $studentSnapshot['gradeAverage'] }}% average</span>
        <span class="status-pill">{{ $studentSnapshot['attendanceSummary']['rate'] }}% attendance</span>
    @else
        <span class="status-pill">No analytics available</span>
    @endif
@endsection

@section('header_actions')
    <a href="{{ route('reports.print') }}" class="btn btn-primary btn-fit">Print / PDF Report</a>
    <a href="{{ route('reports.gradesCsv') }}" class="btn btn-outline btn-fit">Export Grades CSV</a>
    <a href="{{ route('reports.attendanceCsv') }}" class="btn btn-outline btn-fit">Export Attendance CSV</a>
@endsection

@section('content')
    <section class="section-grid two-column">
        <article class="glass-card chart-card">
            <div class="section-head"><div><h2>Grade Trend</h2><p>Latest assessment percentages plotted over time.</p></div></div>
            <canvas class="chart-canvas" data-chart-type="line" data-labels="{{ e(json_encode($gradeTrend->pluck('label')->all())) }}" data-values="{{ e(json_encode($gradeTrend->pluck('value')->all())) }}" data-max="100" data-color="#0f766e" data-area-color="rgba(15, 118, 110, 0.14)"></canvas>
        </article>
        <article class="glass-card chart-card">
            <div class="section-head"><div><h2>Attendance Mix</h2><p>Present, late, and absent records for the selected learner scope.</p></div></div>
            <canvas class="chart-canvas" data-chart-type="bar" data-labels="{{ e(json_encode($attendanceMix->pluck('label')->all())) }}" data-values="{{ e(json_encode($attendanceMix->pluck('value')->all())) }}" data-max="{{ max(1, $attendanceMix->max('value') ?: 1) }}" data-color="#2563eb"></canvas>
        </article>
    </section>

    <section class="section-grid two-column">
        <article class="glass-card">
            <div class="section-head"><div><h2>Category Averages</h2><p>Average scores grouped by assessment type.</p></div></div>
            <div class="summary-grid">
                @forelse ($categoryAverages as $item)
                    <div class="summary-item">
                        <span class="summary-emphasis">{{ $item['category'] }}</span>
                        <strong>{{ $item['average'] }}%</strong>
                    </div>
                @empty
                    <div class="empty-state"><div><h3>No grade data yet</h3><p>Grade analytics will appear once assessments are recorded.</p></div></div>
                @endforelse
            </div>
        </article>
        @if ($user->isTeacher())
            <article class="glass-card">
                <div class="section-head"><div><h2>Section Comparison</h2><p>Quick performance view across active sections.</p></div></div>
                <div class="table-responsive">
                    <table class="history-table">
                        <thead><tr><th>Section</th><th>Learners</th><th>Average</th><th>Attendance</th></tr></thead>
                        <tbody>
                            @foreach ($sectionSummaries as $summary)
                                <tr>
                                    <td>{{ $summary['section']->name }}</td>
                                    <td>{{ $summary['studentCount'] }}</td>
                                    <td>{{ $summary['averageGrade'] }}%</td>
                                    <td>{{ $summary['attendanceRate'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @else
            <article class="glass-card">
                <div class="section-head"><div><h2>Personal Snapshot</h2><p>Your current academic record summary.</p></div></div>
                @if (! empty($studentSnapshot))
                    <ul class="detail-list">
                        <li><strong>Section:</strong> {{ $studentSnapshot['section']->name }} - {{ $studentSnapshot['section']->strand }}</li>
                        <li><strong>Assessments:</strong> {{ $studentSnapshot['grades']->count() }}</li>
                        <li><strong>Attendance Records:</strong> {{ $studentSnapshot['attendance']->count() }}</li>
                        <li><strong>Attendance Rate:</strong> {{ $studentSnapshot['attendanceSummary']['rate'] }}%</li>
                    </ul>
                @else
                    <div class="empty-state"><div><h3>No profile linked</h3><p>Analytics need a student profile to display personal metrics.</p></div></div>
                @endif
            </article>
        @endif
    </section>
@endsection
