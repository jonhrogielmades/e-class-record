@extends('layouts.guest')

@section('title', 'Welcome | E-Class Record System')
@section('meta_description', 'Welcome page for the E-Class Record System with seeded account access details.')
@section('page_name', 'welcome')

@php
    use App\Models\User;
    use Illuminate\Support\Facades\Schema;

    $demoAccounts = collect();

    if (Schema::hasTable('users') && Schema::hasTable('student_profiles') && Schema::hasTable('sections')) {
        $demoAccounts = User::query()
            ->with('studentProfile.section')
            ->whereIn('role', [User::ROLE_TEACHER, User::ROLE_STUDENT])
            ->orderByRaw('CASE WHEN role = ? THEN 0 ELSE 1 END', [User::ROLE_TEACHER])
            ->orderBy('name')
            ->get();
    }
@endphp

@section('content')
    <div class="public-shell">
        <section class="glass-card cta-band" style="margin-top: 48px;">
            <div>
                <span class="eyebrow">Welcome</span>
                <p>E-Class Record System is now fully database-backed. Use the seeded accounts below for quick testing.</p>
            </div>
            <div class="hero-actions">
                <a href="{{ route('landing') }}" class="btn btn-outline btn-fit">Open Landing</a>
                <a href="{{ route('login') }}" class="btn btn-primary btn-fit">Go to Login</a>
            </div>
        </section>

        <section class="spotlight-grid" style="margin-top: 24px;">
            <article class="glass-card spotlight-card">
                <div class="section-head">
                    <div>
                        <h3>Seeded Accounts</h3>
                        <p>Password for all listed demo users: <code>password123</code></p>
                    </div>
                </div>
                <ul class="spotlight-list">
                    @forelse ($demoAccounts as $account)
                        <li>
                            <strong>{{ ucfirst($account->role) }}:</strong>
                            <code>{{ $account->email }}</code>
                            <span>- {{ $account->name }}@if ($account->studentProfile?->section?->name) ({{ $account->studentProfile->section->name }}) @endif</span>
                        </li>
                    @empty
                        <li>No accounts found yet. Run <code>php artisan migrate:fresh --seed</code>.</li>
                    @endforelse
                </ul>
            </article>
        </section>

        <footer class="public-footer">
            <p>E-Class Record System - Welcome - <span data-current-year></span></p>
            <p>Accounts and sections are shown from current database records.</p>
        </footer>
    </div>
@endsection
