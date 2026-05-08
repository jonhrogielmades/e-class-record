@extends('layouts.guest')

@section('title', 'Register | E-Class Record System')
@section('meta_description', 'Registration page for the E-Class Record System.')
@section('page_name', 'register')

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
                <span class="eyebrow">Create Account</span>
                <h1>Set up a teacher or student workspace.</h1>
                <p>
                    Registration creates a database-backed profile and immediately opens the matching role-based experience.
                    Student accounts can be linked to a section, while teacher accounts are prepared for section monitoring,
                    attendance entry, and grade input.
                </p>
                <div class="auth-stat-grid">
                    <div class="auth-stat">
                        <strong>Teacher</strong>
                        <span>Manage class list, attendance, and grading</span>
                    </div>
                    <div class="auth-stat">
                        <strong>Student</strong>
                        <span>Track section details, records, and grades</span>
                    </div>
                </div>
                <ul class="auth-feature-list">
                    <li>Real Laravel session authentication and persistent database records.</li>
                    <li>Role-specific navigation that follows the provided sitemap.</li>
                    <li>Responsive UI tuned for everyday academic record work.</li>
                </ul>
                <div class="auth-hint">
                    <strong id="role-helper">Students can be linked to a section and personal record profile immediately after registration.</strong>
                </div>
            </section>

            <section class="login-card auth-form-card">
                <div class="login-header">
                    <div class="login-logo">EC</div>
                    <h1 class="login-title">Create Account</h1>
                    <p class="login-subtitle">Choose a role and prepare your e-class record access.</p>
                </div>

                @include('partials.flash')

                @if (isset($databaseReady) && ! $databaseReady)
                    <div class="alert alert-warning auth-setup-alert">
                        Database setup required. Run <code>php artisan migrate --seed</code>.
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" id="register-form">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-input" placeholder="Enter your full name" value="{{ old('name') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" value="{{ old('email') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="role">Role</label>
                        <select id="role" name="role" class="form-input" data-role-select>
                            <option value="student" @selected(old('role', 'student') === 'student')>Student</option>
                            <option value="teacher" @selected(old('role') === 'teacher')>Teacher</option>
                        </select>
                    </div>

                    <div class="form-group" data-student-only>
                        <label class="form-label" for="section_id">Section</label>
                        <select id="section_id" name="section_id" class="form-input">
                            <option value="">Select section</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" @selected((string) old('section_id') === (string) $section->id)>
                                    {{ $section->name }} - {{ $section->strand }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Contact Number</label>
                        <input type="text" id="phone" name="phone" class="form-input" placeholder="Optional contact number" value="{{ old('phone') }}">
                    </div>

                    <div class="form-group" data-student-only>
                        <label class="form-label" for="guardian">Guardian / Parent</label>
                        <input type="text" id="guardian" name="guardian" class="form-input" placeholder="Guardian name" value="{{ old('guardian') }}">
                    </div>

                    <div class="form-group password-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="password-input-wrap">
                            <input type="password" id="password" name="password" class="form-input" placeholder="Create a password">
                            <button type="button" class="password-toggle" data-password-toggle="password">Show</button>
                        </div>
                    </div>

                    <div class="form-group password-group">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <div class="password-input-wrap">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Confirm your password">
                            <button type="button" class="password-toggle" data-password-toggle="password_confirmation">Show</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Account</button>
                </form>

                <div class="divider"><span>ready to continue?</span></div>

                <p class="login-footer">
                    Already registered? <a href="{{ route('login') }}">Sign in here</a><br>
                    <a href="{{ route('landing') }}">Back to landing page</a>
                </p>
            </section>
        </div>

        <footer class="public-footer">
            <p>E-Class Record System - Laravel registration module - <span data-current-year></span></p>
            <p>New accounts are stored in the database and redirected straight into the dashboard.</p>
        </footer>
    </div>
@endsection
