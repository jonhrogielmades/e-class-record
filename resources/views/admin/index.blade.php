@extends('layouts.app')

@section('active_page', 'admin')
@section('page_title', 'Admin Panel')
@section('page_subtitle', 'Manage teachers, students, sections, and system records from one role-based workspace.')

@section('header_meta')
    <span class="status-pill">{{ $teachers->count() }} teachers</span>
    <span class="status-pill">{{ $students->count() }} students</span>
    <span class="status-pill">{{ $sections->count() }} sections</span>
@endsection

@section('header_actions')
    <a href="{{ route('reports.print') }}" class="btn btn-primary btn-fit">Print Reports</a>
    <a href="{{ url('/api/sections') }}" class="btn btn-outline btn-fit">API Sections</a>
@endsection

@section('content')
    <section class="stats-grid">
        <div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Teachers</h3><div class="stat-value">{{ $teachers->count() }}</div><span class="stat-change emerald">Faculty accounts</span></div><div class="stat-icon emerald">T</div></div></div>
        <div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Students</h3><div class="stat-value">{{ $students->count() }}</div><span class="stat-change blue">Learner accounts</span></div><div class="stat-icon blue">S</div></div></div>
        <div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>Sections</h3><div class="stat-value">{{ $sections->count() }}</div><span class="stat-change gold">Active classes</span></div><div class="stat-icon gold">C</div></div></div>
        <div class="glass-card stat-card"><div class="stat-card-inner"><div class="stat-info"><h3>API</h3><div class="stat-value">REST</div><span class="stat-change rose">GET POST PUT DELETE</span></div><div class="stat-icon rose">A</div></div></div>
    </section>

    <section class="section-grid two-column">
        <article class="glass-card">
            <div class="section-head"><div><h2>Create Teacher</h2><p>Add a teacher account that can manage class sections.</p></div></div>
            <form method="POST" action="{{ route('admin.teachers.store') }}" class="form-grid">
                @csrf
                <div class="form-group-settings full-width"><label for="teacher-name">Full Name</label><input id="teacher-name" class="form-input" name="name" value="{{ old('name') }}"></div>
                <div class="form-group-settings"><label for="teacher-email">Email</label><input id="teacher-email" class="form-input" type="email" name="email" value="{{ old('email') }}"></div>
                <div class="form-group-settings"><label for="teacher-password">Password</label><input id="teacher-password" class="form-input" type="password" name="password"></div>
                <div class="form-group-settings"><label for="teacher-department">Department</label><input id="teacher-department" class="form-input" name="department" value="{{ old('department') }}"></div>
                <div class="form-group-settings"><label for="teacher-phone">Contact</label><input id="teacher-phone" class="form-input" name="phone" value="{{ old('phone') }}"></div>
                <div class="btn-group no-print"><button type="submit" class="btn btn-primary btn-fit">Create Teacher</button></div>
            </form>
        </article>

        <article class="glass-card">
            <div class="section-head"><div><h2>Sections</h2><p>Review section ownership and enrollment count.</p></div></div>
            <div class="table-responsive">
                <table class="history-table">
                    <thead><tr><th>Section</th><th>Teacher</th><th>Learners</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($sections as $section)
                            <tr>
                                <td>{{ $section->name }} - {{ $section->strand }}</td>
                                <td>{{ $section->teacher?->name ?? 'Unassigned' }}</td>
                                <td>{{ $section->students_count }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.sections.destroy', $section) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-fit btn-xs" onclick="return confirm('Delete this empty section?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="section-grid two-column">
        <article class="glass-card">
            <div class="section-head"><div><h2>Teacher Accounts</h2><p>Teacher account management.</p></div></div>
            <div class="table-responsive">
                <table class="history-table">
                    <thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($teachers as $teacher)
                            <tr>
                                <td>{{ $teacher->name }}</td>
                                <td>{{ $teacher->email }}</td>
                                <td>{{ $teacher->department }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.users.destroy', $teacher) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-fit btn-xs" onclick="return confirm('Delete this teacher account?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
        <article class="glass-card">
            <div class="section-head"><div><h2>Student Accounts</h2><p>Student and section assignment overview.</p></div></div>
            <div class="table-responsive">
                <table class="history-table">
                    <thead><tr><th>Name</th><th>Email</th><th>Section</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->email }}</td>
                                <td>{{ $student->studentProfile?->section?->name ?? 'No profile' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.users.destroy', $student) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-fit btn-xs" onclick="return confirm('Delete this student account?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
