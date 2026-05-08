@extends('layouts.guest')

@section('title', 'Guardian Portal | E-Class Record System')
@section('page_name', 'guardian')

@section('floating_theme_toggle')
    <button class="theme-toggle-float" type="button" data-theme-toggle title="Toggle theme">
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="M4.93 4.93l1.41 1.41"></path><path d="M17.66 17.66l1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path></svg>
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
    </button>
@endsection

@section('content')
    <div class="auth-shell">
        <div class="auth-grid">
            <section class="glass-card auth-hero-panel">
                <span class="eyebrow">Guardian View</span>
                <h1>Review a learner record securely.</h1>
                <p>Enter the student number and guardian name or contact number saved in the student profile to view grades and attendance.</p>
                <div class="auth-stat-grid">
                    <div class="auth-stat"><strong>Grades</strong><span>Assessment scores and averages</span></div>
                    <div class="auth-stat"><strong>Attendance</strong><span>Present, late, and absent records</span></div>
                </div>
                <div class="button-row">
                    <a href="{{ route('login') }}" class="btn btn-outline btn-fit">Back to Login</a>
                    <a href="{{ route('landing') }}" class="btn btn-outline btn-fit">Home</a>
                </div>
            </section>

            <section class="login-card auth-form-card">
                <div class="login-header">
                    <div class="login-logo">EC</div>
                    <h1 class="login-title">Guardian Lookup</h1>
                    <p class="login-subtitle">Use saved guardian details as the access code.</p>
                </div>

                @include('partials.flash')

                @if (! empty($lookupFailed))
                    <div class="alert alert-error">No learner matched that student number and access code.</div>
                @endif

                <form method="POST" action="{{ route('guardian.lookup') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="student_number">Student Number</label>
                        <input class="form-input" id="student_number" name="student_number" value="{{ old('student_number') }}" placeholder="2026-A-001">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="access_code">Guardian Name or Contact Number</label>
                        <input class="form-input" id="access_code" name="access_code" value="{{ old('access_code') }}" placeholder="Mila Santos">
                    </div>
                    <button type="submit" class="btn btn-primary">View Record</button>
                </form>
            </section>
        </div>

        @if ($studentSnapshot)
            <section class="section-grid two-column">
                <article class="glass-card">
                    <div class="section-head"><div><h2>{{ $studentSnapshot['student']->name }}</h2><p>{{ $studentSnapshot['section']->name }} - {{ $studentSnapshot['section']->strand }}</p></div></div>
                    <div class="summary-grid">
                        <div class="summary-item"><span class="summary-emphasis">Average</span><strong>{{ $studentSnapshot['gradeAverage'] }}%</strong></div>
                        <div class="summary-item"><span class="summary-emphasis">Attendance</span><strong>{{ $studentSnapshot['attendanceSummary']['rate'] }}%</strong></div>
                        <div class="summary-item"><span class="summary-emphasis">Assessments</span><strong>{{ $studentSnapshot['grades']->count() }}</strong></div>
                    </div>
                </article>
                <article class="glass-card">
                    <div class="section-head"><div><h2>Recent Grades</h2><p>Latest recorded assessments.</p></div></div>
                    <ul class="detail-list">
                        @foreach ($studentSnapshot['grades']->take(5) as $grade)
                            <li><strong>{{ $grade->title }}:</strong> {{ $grade->score }} / {{ $grade->max_score }}</li>
                        @endforeach
                    </ul>
                </article>
            </section>
        @endif
    </div>
@endsection
