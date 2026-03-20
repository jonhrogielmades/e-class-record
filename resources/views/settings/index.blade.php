@extends('layouts.app')
@php use App\Support\EClassUi; @endphp

@section('active_page', 'settings')
@section('page_title', 'Settings')
@section('page_subtitle', $user->isTeacher() ? 'Update your teacher profile and use settings as the home for profile details and logout actions.' : 'Update your student profile details and use settings as the place for profile and logout actions.')

@section('header_meta')
    @if ($user->isTeacher())
        <span class="status-pill">{{ EClassUi::roleLabel($user->role) }}</span>
        <span class="status-pill">{{ optional($user->created_at)->format('M j, Y') }}</span>
        <span class="status-pill">{{ $user->department ?: 'Not set' }}</span>
    @elseif ($studentSnapshot)
        <span class="status-pill">{{ EClassUi::roleLabel($user->role) }}</span>
        <span class="status-pill">{{ $studentSnapshot['section']->name }}</span>
        <span class="status-pill">{{ $studentSnapshot['student']->student_number }}</span>
    @else
        <span class="status-pill">{{ EClassUi::roleLabel($user->role) }}</span>
        <span class="status-pill">Profile unavailable</span>
    @endif
@endsection

@section('header_actions')
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline btn-fit">Log Out</button>
    </form>
@endsection

@section('content')
    @if ($user->isTeacher())
        <section class="profile-grid">
            <article class="glass-card profile-summary-card">
                <div class="profile-header"><div class="profile-avatar">{{ EClassUi::initials($user->name) }}</div><div><h2>{{ $user->name }}</h2><p>{{ $user->email }}</p><span class="status-pill good">{{ EClassUi::roleLabel($user->role) }}</span></div></div>
                <ul class="detail-list">
                    <li><strong>Department:</strong> {{ $user->department ?: 'Not provided' }}</li>
                    <li><strong>Contact Number:</strong> {{ $user->phone ?: 'Not provided' }}</li>
                    <li><strong>Joined Date:</strong> {{ optional($user->created_at)->format('M j, Y') }}</li>
                </ul>
                <div class="profile-note"><strong>Teacher Note:</strong> Use this page to update your profile while keeping the teacher workflow inside the same glass-style shell.</div>
            </article>
            <article class="glass-card">
                <div class="section-head"><div><h2>Profile Settings</h2><p>Edit your teacher information. Account role is read-only.</p></div></div>
                <form method="POST" action="{{ route('settings.update') }}" class="form-grid">
                    @csrf
                    @method('PUT')
                    <div class="form-group-settings full-width"><label for="settings-name">Full Name</label><input id="settings-name" class="form-input" type="text" name="name" value="{{ old('name', $user->name) }}"></div>
                    <div class="form-group-settings"><label>Email Address</label><div class="readonly-field">{{ $user->email }}</div></div>
                    <div class="form-group-settings"><label>Role</label><div class="readonly-field">{{ EClassUi::roleLabel($user->role) }}</div></div>
                    <div class="form-group-settings"><label for="settings-department">Department</label><input id="settings-department" class="form-input" type="text" name="department" value="{{ old('department', $user->department) }}"></div>
                    <div class="form-group-settings"><label for="settings-phone">Contact Number</label><input id="settings-phone" class="form-input" type="text" name="phone" value="{{ old('phone', $user->phone) }}"></div>
                    <div class="btn-group no-print"><button type="submit" class="btn btn-primary btn-fit">Save Changes</button></div>
                </form>
                <form method="POST" action="{{ route('settings.destroy') }}" class="inline-form-delete no-print">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger-outline btn-fit" onclick="return confirm('Delete this teacher account from the system?')">Delete Account</button>
                </form>
            </article>
        </section>
    @else
        @if (! $studentSnapshot)
            <section class="glass-card"><div class="empty-state"><div><h3>Student profile not found</h3><p>Your account is still available, but the linked learner profile is missing. You can sign out or remove this account.</p></div></div></section>
            <form method="POST" action="{{ route('settings.destroy') }}" class="inline-form-delete no-print">@csrf @method('DELETE')<button type="submit" class="btn btn-danger-outline btn-fit" onclick="return confirm('Delete this student account from the system?')">Delete Account</button></form>
        @else
            <section class="profile-grid">
                <article class="glass-card profile-summary-card">
                    <div class="profile-header"><div class="profile-avatar">{{ EClassUi::initials($user->name) }}</div><div><h2>{{ $user->name }}</h2><p>{{ $user->email }}</p><span class="status-pill good">{{ $studentSnapshot['section']->name }}</span></div></div>
                    <ul class="detail-list">
                        <li><strong>Student Number:</strong> {{ $studentSnapshot['student']->student_number }}</li>
                        <li><strong>Section:</strong> {{ $studentSnapshot['section']->name }} - {{ $studentSnapshot['section']->strand }}</li>
                        <li><strong>Guardian:</strong> {{ $studentSnapshot['student']->guardian }}</li>
                        <li><strong>Contact Number:</strong> {{ $studentSnapshot['student']->contact }}</li>
                    </ul>
                    <div class="profile-note"><strong>Student Note:</strong> Update your guardian and contact details while keeping grades and attendance in the other pages.</div>
                </article>
                <article class="glass-card">
                    <div class="section-head"><div><h2>Profile Settings</h2><p>Edit your personal student information. Section assignment is shown as read-only.</p></div></div>
                    <form method="POST" action="{{ route('settings.update') }}" class="form-grid">
                        @csrf
                        @method('PUT')
                        <div class="form-group-settings full-width"><label for="settings-name">Full Name</label><input id="settings-name" class="form-input" type="text" name="name" value="{{ old('name', $user->name) }}"></div>
                        <div class="form-group-settings"><label>Email Address</label><div class="readonly-field">{{ $user->email }}</div></div>
                        <div class="form-group-settings"><label>Section</label><div class="readonly-field">{{ $studentSnapshot['section']->name }} - {{ $studentSnapshot['section']->strand }}</div></div>
                        <div class="form-group-settings"><label for="settings-phone">Contact Number</label><input id="settings-phone" class="form-input" type="text" name="phone" value="{{ old('phone', $studentSnapshot['student']->contact) }}"></div>
                        <div class="form-group-settings"><label for="settings-guardian">Guardian / Parent</label><input id="settings-guardian" class="form-input" type="text" name="guardian" value="{{ old('guardian', $studentSnapshot['student']->guardian) }}"></div>
                        <div class="btn-group no-print"><button type="submit" class="btn btn-primary btn-fit">Save Changes</button></div>
                    </form>
                    <form method="POST" action="{{ route('settings.destroy') }}" class="inline-form-delete no-print">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger-outline btn-fit" onclick="return confirm('Delete this student account and its linked records?')">Delete Account</button>
                    </form>
                </article>
            </section>
        @endif
    @endif
@endsection


