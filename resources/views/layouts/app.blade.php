@php
    use App\Support\EClassUi;

    $activePage = trim($__env->yieldContent('active_page', 'dashboard'));
    $pageTitle = trim($__env->yieldContent('page_title', 'Dashboard'));
    $pageSubtitle = trim($__env->yieldContent('page_subtitle', ''));
    $unreadNotificationCount = $user->notifications()->unread()->count();
    $navigation = $user->isAdmin()
        ? [
            ['key' => 'admin', 'label' => 'Admin Panel', 'route' => 'admin.index'],
            ['key' => 'notifications', 'label' => 'Notifications', 'route' => 'notifications.index'],
            ['key' => 'settings', 'label' => 'Settings', 'route' => 'settings.index'],
        ]
        : ($user->isTeacher()
        ? [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'dashboard'],
            ['key' => 'analytics', 'label' => 'Analytics', 'route' => 'analytics.index'],
            ['key' => 'class-list', 'label' => 'Class List', 'route' => 'sections.index'],
            ['key' => 'students', 'label' => 'Students', 'route' => 'students.index'],
            ['key' => 'grading', 'label' => 'Grading', 'route' => 'grades.index'],
            ['key' => 'calendar', 'label' => 'Calendar', 'route' => 'attendance.calendar'],
            ['key' => 'announcements', 'label' => 'Announcements', 'route' => 'announcements.index'],
            ['key' => 'assignments', 'label' => 'Assignments', 'route' => 'assignments.index'],
            ['key' => 'notifications', 'label' => 'Notifications', 'route' => 'notifications.index'],
            ['key' => 'settings', 'label' => 'Settings', 'route' => 'settings.index'],
        ]
        : [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'dashboard'],
            ['key' => 'analytics', 'label' => 'Analytics', 'route' => 'analytics.index'],
            ['key' => 'class-list', 'label' => 'My Class', 'route' => 'sections.index'],
            ['key' => 'students', 'label' => 'My Records', 'route' => 'students.index'],
            ['key' => 'grading', 'label' => 'Grades', 'route' => 'grades.index'],
            ['key' => 'calendar', 'label' => 'Calendar', 'route' => 'attendance.calendar'],
            ['key' => 'announcements', 'label' => 'Announcements', 'route' => 'announcements.index'],
            ['key' => 'assignments', 'label' => 'Assignments', 'route' => 'assignments.index'],
            ['key' => 'notifications', 'label' => 'Notifications', 'route' => 'notifications.index'],
            ['key' => 'settings', 'label' => 'Settings', 'route' => 'settings.index'],
        ]);
    $navIcons = [
        'admin' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l7 4v6c0 5-3 8-7 10-4-2-7-5-7-10V6l7-4z"></path><path d="M9 12l2 2 4-4"></path></svg>',
        'dashboard' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect></svg>',
        'analytics' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"></path><path d="M7 15l4-4 3 3 5-7"></path></svg>',
        'class-list' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13"></path><path d="M8 12h13"></path><path d="M8 18h13"></path><path d="M3 6h.01"></path><path d="M3 12h.01"></path><path d="M3 18h.01"></path></svg>',
        'students' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>',
        'grading' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>',
        'calendar' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4"></path><path d="M8 2v4"></path><path d="M3 10h18"></path></svg>',
        'announcements' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l18-5v12L3 13v-2z"></path><path d="M11 14v5a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-6"></path></svg>',
        'assignments' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5h6"></path><path d="M9 12h6"></path><path d="M9 19h6"></path><rect x="4" y="3" width="16" height="18" rx="2"></rect></svg>',
        'notifications' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>',
        'settings' => '<svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.33 1V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-.33-1A1.65 1.65 0 0 0 8 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1-.33H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1-.33A1.65 1.65 0 0 0 4.6 8a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 8 4.6c.36 0 .71-.13 1-.37.28-.24.46-.58.5-.94V3a2 2 0 1 1 4 0v.09c.04.36.22.7.5.94.29.24.64.37 1 .37.49 0 .96-.19 1.31-.53l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06c-.34.35-.53.82-.53 1.31 0 .36.13.71.37 1 .24.29.58.46.94.5H21a2 2 0 1 1 0 4h-.09c-.36.04-.7.22-.94.5-.24.29-.37.64-.37 1z"></path></svg>',
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} | E-Class Record System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/templatemo-glass-admin-style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/laravel-bridge.css') }}">
    @stack('styles')
</head>
<body data-page="{{ $activePage }}">
    <div class="background"></div>

    <div class="dashboard">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">EC</div>
                <span class="logo-text">E-Class Record</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-section">
                    <span class="nav-section-title">Navigation</span>
                    <ul>
                        @foreach ($navigation as $item)
                            <li class="nav-item">
                                <a href="{{ route($item['route']) }}" class="nav-link {{ $activePage === $item['key'] ? 'active' : '' }}">
                                    {!! $navIcons[$item['key']] !!}
                                    {{ $item['label'] }}
                                    @if ($item['key'] === 'notifications' && $unreadNotificationCount > 0)
                                        <span class="nav-badge">{{ $unreadNotificationCount }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </ul>
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">{{ EClassUi::initials($user->name) }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ $user->name }}</div>
                        <div class="user-role">{{ EClassUi::roleLabel($user->role) }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <nav class="navbar app-navbar">
                <div class="navbar-left">
                    <button class="mobile-menu-toggle" type="button" data-menu-toggle aria-label="Open navigation">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <div>
                        <h1 class="page-title">{{ $pageTitle }}</h1>
                        <p class="page-subtitle">{{ $pageSubtitle }}</p>
                    </div>
                </div>
                <div class="navbar-right">
                    <div class="navbar-user-chip">
                        <span class="user-chip-avatar">{{ EClassUi::initials($user->name) }}</span>
                        <span>{{ $user->name }}</span>
                    </div>
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
            </nav>

            <section class="glass-card hero-panel">
                <div>
                    <span class="eyebrow">{{ $user->isTeacher() ? 'Teacher Workspace' : 'Student Workspace' }}</span>
                    <div class="hero-meta">@yield('header_meta')</div>
                </div>
                <div class="hero-actions no-print">@yield('header_actions')</div>
            </section>

            @include('partials.flash')

            @yield('content')

            <footer class="site-footer app-footer">
                <p>E-Class Record System - Laravel workspace - <span data-current-year></span></p>
            </footer>
        </main>
    </div>

    <script src="{{ asset('js/eclass.js') }}"></script>
    @stack('scripts')
</body>
</html>
