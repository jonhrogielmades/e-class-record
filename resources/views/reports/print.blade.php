@extends('layouts.app')

@section('active_page', 'analytics')
@section('page_title', 'Printable Report')
@section('page_subtitle', 'Use your browser print dialog to save this report as PDF, or export CSV files for Excel.')

@section('header_meta')
    <span class="status-pill">{{ $sectionName }}</span>
    <span class="status-pill">{{ $grades->count() }} grade rows</span>
    <span class="status-pill">{{ $attendanceRecords->count() }} attendance rows</span>
@endsection

@section('header_actions')
    <button type="button" class="btn btn-primary btn-fit" onclick="window.print()">Print / Save PDF</button>
    <a href="{{ route('reports.gradesCsv') }}" class="btn btn-outline btn-fit">Grades CSV</a>
    <a href="{{ route('reports.attendanceCsv') }}" class="btn btn-outline btn-fit">Attendance CSV</a>
@endsection

@section('content')
    <section class="glass-card">
        <div class="section-head"><div><h2>Grade Report</h2><p>Excel-compatible rows for recorded assessments.</p></div></div>
        <div class="table-responsive">
            <table class="history-table">
                <thead><tr><th>Date</th><th>Section</th><th>Student</th><th>Assessment</th><th>Score</th><th>Percent</th></tr></thead>
                <tbody>
                    @forelse ($grades as $row)
                        <tr><td>{{ $row['date'] }}</td><td>{{ $row['section'] }}</td><td>{{ $row['student'] }}</td><td>{{ $row['assessment'] }}</td><td>{{ $row['score'] }} / {{ $row['max_score'] }}</td><td>{{ $row['percent'] }}%</td></tr>
                    @empty
                        <tr><td colspan="6">No grade records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="glass-card">
        <div class="section-head"><div><h2>Attendance Report</h2><p>Attendance records grouped into a printable table.</p></div></div>
        <div class="table-responsive">
            <table class="history-table">
                <thead><tr><th>Date</th><th>Section</th><th>Student</th><th>Topic</th><th>Status</th><th>Remarks</th></tr></thead>
                <tbody>
                    @forelse ($attendanceRecords as $row)
                        <tr><td>{{ $row['date'] }}</td><td>{{ $row['section'] }}</td><td>{{ $row['student'] }}</td><td>{{ $row['topic'] }}</td><td>{{ $row['status'] }}</td><td>{{ $row['remarks'] }}</td></tr>
                    @empty
                        <tr><td colspan="6">No attendance records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
