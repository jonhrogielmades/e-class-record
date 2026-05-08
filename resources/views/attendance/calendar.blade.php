@extends('layouts.app')
@php
    $start = $month->copy()->startOfMonth()->startOfWeek();
    $end = $month->copy()->endOfMonth()->endOfWeek();
    $cursor = $start->copy();
    $prevMonth = $month->copy()->subMonth()->format('Y-m-01');
    $nextMonth = $month->copy()->addMonth()->format('Y-m-01');
@endphp

@section('active_page', 'calendar')
@section('page_title', 'Attendance Calendar')
@section('page_subtitle', $user->isTeacher() ? 'Review monthly attendance records by section.' : 'Review your monthly attendance records in calendar view.')

@section('header_meta')
    <span class="status-pill">{{ $month->format('F Y') }}</span>
    @if ($user->isTeacher() && $activeSummary)
        <span class="status-pill">{{ $activeSummary['section']->name }}</span>
    @elseif ($user->isStudent() && $studentSnapshot)
        <span class="status-pill">{{ $studentSnapshot['section']->name }}</span>
    @endif
@endsection

@section('header_actions')
    <a href="{{ route('attendance.calendar', array_filter(['month' => $prevMonth, 'section' => $activeSummary['section']->id ?? null])) }}" class="btn btn-outline btn-fit">Previous</a>
    <a href="{{ route('attendance.calendar', array_filter(['month' => $nextMonth, 'section' => $activeSummary['section']->id ?? null])) }}" class="btn btn-outline btn-fit">Next</a>
@endsection

@section('content')
    @if ($user->isTeacher() && ! empty($sectionSummaries))
        <section class="glass-card section-filter-block">
            <div class="section-head"><div><h2>Section Filter</h2><p>Switch section to inspect a different attendance calendar.</p></div></div>
            <form method="GET" action="{{ route('attendance.calendar') }}" class="form-group-settings section-filter-form">
                <input type="hidden" name="month" value="{{ $month->format('Y-m-01') }}">
                <label for="calendar-section">Section</label>
                <select id="calendar-section" name="section" class="form-input" data-submit-on-change>
                    @foreach ($sectionSummaries as $summary)
                        <option value="{{ $summary['section']->id }}" @selected($activeSummary['section']->id === $summary['section']->id)>{{ $summary['section']->name }} - {{ $summary['section']->strand }}</option>
                    @endforeach
                </select>
            </form>
        </section>
    @endif

    <section class="glass-card">
        <div class="attendance-calendar">
            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="calendar-heading">{{ $day }}</div>
            @endforeach

            @while ($cursor->lte($end))
                @php
                    $dateKey = $cursor->format('Y-m-d');
                    $dayRecords = $recordsByDate->get($dateKey, collect());
                    $outsideMonth = ! $cursor->isSameMonth($month);
                @endphp
                <div class="calendar-cell {{ $outsideMonth ? 'calendar-muted' : '' }}">
                    <div class="calendar-day-number">{{ $cursor->day }}</div>
                    <div class="calendar-events">
                        @foreach ($dayRecords->take(3) as $record)
                            <span class="calendar-event {{ $record->status }}">{{ $user->isTeacher() ? ($record->student?->name ?? 'Learner') . ' - ' : '' }}{{ ucfirst($record->status) }}</span>
                        @endforeach
                        @if ($dayRecords->count() > 3)
                            <span class="calendar-event">+{{ $dayRecords->count() - 3 }} more</span>
                        @endif
                    </div>
                </div>
                @php $cursor->addDay(); @endphp
            @endwhile
        </div>
    </section>
@endsection
