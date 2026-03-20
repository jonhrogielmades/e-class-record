@extends('layouts.guest')

@section('title', 'E-Class Record System')
@section('meta_description', 'Teacher and student academic record workspace built with Laravel.')
@section('page_name', 'landing')

@section('content')
    <div class="public-shell">
        <header class="glass-card public-nav">
            <div class="brand">
                <div class="brand-mark">EC</div>
                <div class="brand-text">
                    <strong>E-Class Record System</strong>
                    <span>Teacher and student academic record workspace</span>
                </div>
            </div>
            <div class="public-nav-links">
                <a href="{{ route('login') }}" class="btn btn-outline btn-fit">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-fit">Register</a>
                <button class="nav-btn" type="button" data-theme-toggle title="Toggle theme">
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
            </div>
        </header>

        <section class="landing-hero">
            <article class="glass-card hero-copy">
                <span class="eyebrow">Laravel Conversion</span>
                <h2>Track class lists, attendance, and grades in one glass-style workspace.</h2>
                <p>
                    This version keeps the original prototype flow, but now runs on Laravel with real authentication,
                    database-backed records, and teacher and student dashboards powered by PHP instead of localStorage.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-fit">Open Login</a>
                    <a href="{{ route('register') }}" class="btn btn-outline btn-fit">Create Account</a>
                </div>
                <div class="metric-strip">
                    <div class="metric-pill">
                        <strong>2</strong>
                        <span>User roles</span>
                    </div>
                    <div class="metric-pill">
                        <strong>5</strong>
                        <span>Main sitemap pages</span>
                    </div>
                    <div class="metric-pill">
                        <strong>100%</strong>
                        <span>Database-backed demo data</span>
                    </div>
                </div>
            </article>

            <article class="glass-card hero-preview">
                <div class="section-head">
                    <div>
                        <h3>Sitemap Flow</h3>
                        <p>Designed around login, dashboard, class list, students, grading, and settings.</p>
                    </div>
                </div>
                <div class="site-map-grid">
                    <div class="site-map-node">Login</div>
                    <div class="site-map-node accent">Dashboard</div>
                    <div class="site-map-branch">
                        <span>Class List</span>
                        <small>Two active sections</small>
                    </div>
                    <div class="site-map-branch">
                        <span>Students</span>
                        <small>Profiles / Attendance</small>
                    </div>
                    <div class="site-map-branch">
                        <span>Grading</span>
                        <small>Input / Summary</small>
                    </div>
                    <div class="site-map-branch">
                        <span>Settings</span>
                        <small>Profile / Log Out</small>
                    </div>
                </div>
            </article>
        </section>

        <section class="role-card-grid">
            <article class="glass-card role-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"></path><path d="M6 12v5c0 1.1 2.7 3 6 3s6-1.9 6-3v-5"></path></svg>
                </div>
                <h3>Teacher Role</h3>
                <p>Manage sections, monitor attendance, enter quizzes and exam grades, and review per-section performance from one dashboard.</p>
            </article>
            <article class="glass-card role-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 7L12 2 2 7l10 5 10-5z"></path><path d="M6 10.6v4.4c0 1.1 2.7 3 6 3s6-1.9 6-3v-4.4"></path></svg>
                </div>
                <h3>Student Role</h3>
                <p>View assigned section details, attendance records, recent assessments, and a personal grade summary with the same visual system.</p>
            </article>
        </section>

        <div class="section-stack">
            <section class="feature-grid">
                <article class="glass-card feature-card">
                    <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13"></path><path d="M8 12h13"></path><path d="M8 18h13"></path><path d="M3 6h.01"></path><path d="M3 12h.01"></path><path d="M3 18h.01"></path></svg></div>
                    <h3>Class List</h3>
                    <p>Section views show roster counts, adviser information, schedules, and student performance snapshots.</p>
                </article>
                <article class="glass-card feature-card">
                    <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg></div>
                    <h3>Student Records</h3>
                    <p>Teachers can review learner profiles and attendance entries, while students get a personal records page filtered to their own data.</p>
                </article>
                <article class="glass-card feature-card">
                    <div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg></div>
                    <h3>Grading Module</h3>
                    <p>Input quiz or exam scores, review grade summary tables, and show student-friendly assessment history through Laravel forms and records.</p>
                </article>
            </section>

            <section class="spotlight-grid">
                <article class="glass-card spotlight-card">
                    <div class="section-head">
                        <div>
                            <h3>Demo Accounts</h3>
                            <p>Use either role immediately for presentation or testing.</p>
                        </div>
                    </div>
                    <ul class="spotlight-list">
                        @if (! empty($demoAccounts))
                            @foreach ($demoAccounts as $account)
                                <li>
                                    <strong>{{ ucfirst($account['role']) }}:</strong>
                                    <code>{{ $account['email'] }}</code> /
                                    <code>{{ $demoPassword }}</code>
                                    <span>- {{ $account['name'] }}@if (! empty($account['section'])) ({{ $account['section'] }}) @endif</span>
                                </li>
                            @endforeach
                            <li>All listed accounts are loaded from the database seed data.</li>
                        @else
                            <li>No demo accounts found yet. Run <code>php artisan migrate:fresh --seed</code>.</li>
                        @endif
                    </ul>
                </article>
                <article class="glass-card spotlight-card">
                    <div class="section-head">
                        <div>
                            <h3>Design Direction</h3>
                            <p>The layout preserves the original glass-admin visual language while upgrading the project into Laravel.</p>
                        </div>
                    </div>
                    <div class="category-pill-grid">
                        <div class="category-pill">Blade Layouts</div>
                        <div class="category-pill">Role-Based Dashboards</div>
                        <div class="category-pill">Section CRUD</div>
                        <div class="category-pill">Attendance Tables</div>
                        <div class="category-pill">Grade Summaries</div>
                        <div class="category-pill">Responsive Layout</div>
                    </div>
                </article>
            </section>

            <section class="glass-card cta-band">
                <div>
                    <span class="eyebrow">Start Exploring</span>
                    <p>Sign in as a teacher to manage sections or log in as a student to review personal records and grading history.</p>
                </div>
                <div class="hero-actions">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-fit">Go to Login</a>
                    <a href="{{ route('register') }}" class="btn btn-outline btn-fit">Create Account</a>
                </div>
            </section>
        </div>

        <footer class="public-footer">
            <p>E-Class Record System - Laravel academic record platform - <span data-current-year></span></p>
            <p>Built with Laravel, Blade, PHP, SQLite, and database-backed records.</p>
        </footer>
    </div>
@endsection

