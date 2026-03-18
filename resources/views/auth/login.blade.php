@extends('layouts.guest')

@section('title', 'Login | E-Class Record System')
@section('meta_description', 'Login page for the E-Class Record System.')
@section('page_name', 'login')

@section('floating_theme_toggle')
    <button class="theme-toggle-float" type="button" data-theme-toggle title="Toggle theme">
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="4"></circle>
            <path d="M12 2v2"></path><path d="M12 20v2"></path>
            <path d="M4.93 4.93l1.41 1.41"></path><path d="M17.66 17.66l1.41 1.41"></path>
            <path d="M2 12h2"></path><path d="M20 12h2"></path>
            <path d="M6.34 17.66l-1.41 1.41"></path><path d="M19.07 4.93l-1.41 1.41"></path>
        </svg>
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
    </button>
@endsection

@section('content')
    <div class="auth-shell">
        <div class="auth-grid">
            <section class="glass-card auth-hero-panel">
                <span class="eyebrow">Role-Based Access</span>
                <h1>Sign in as a teacher or student.</h1>
                <p>
                    The system provides two tailored experiences. Teachers manage class lists, attendance, and grading,
                    while students review their personal records, section details, and grade summaries.
                </p>
                <div class="auth-stat-grid">
                    <div class="auth-stat">
                        <strong>Teacher</strong>
                        <span>Sections, attendance, and grading tools</span>
                    </div>
                    <div class="auth-stat">
                        <strong>Student</strong>
                        <span>Personal records and grade summary</span>
                    </div>
                </div>
                <ul class="auth-feature-list">
                    <li>Glass-admin inspired interface adapted to academic records.</li>
                    <li>Section A and Section B data are already seeded for fast testing.</li>
                    <li>All records are now stored in Laravel's database for real persistence.</li>
                </ul>
                <div class="auth-hint">
                    <strong>Load a prepared teacher or student account on the left side.</strong>
                    <div class="button-row">
                          </div>
                </div>
            </section>

            <section class="login-card auth-form-card">
                <div class="login-header">
                    <div class="login-logo">EC</div>
                    <h1 class="login-title">Sign In</h1>
                    <p class="login-subtitle">Access the role-based e-class record dashboard.</p>
                </div>

                @include('partials.flash')

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" value="{{ old('email') }}">
                    </div>

                    <div class="form-group password-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password">
                        <button type="button" class="password-toggle" data-password-toggle="password">Show</button>
                    </div>

                    <button type="submit" class="btn btn-primary">Sign In</button>
                </form>

                <div class="divider"><span>Create accounts</span></div>
                <p class="login-footer">
                    Need a new account? <a href="{{ route('register') }}">Create one here</a><br>
                    <a href="{{ route('landing') }}">Back to landing page</a>
                </p>
            </section>
        </div>

        <footer class="public-footer">
            <p>E-Class Record System - Laravel authentication - <span data-current-year></span></p>
            <p>Teacher and student demo credentials are included for fast presentation testing.</p>
        </footer>
    </div>
@endsection