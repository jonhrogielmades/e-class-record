@extends('layouts.app')

@section('active_page', 'notifications')
@section('page_title', 'Notifications')
@section('page_subtitle', 'View grade, attendance, assignment, and announcement updates.')

@section('header_meta')
    <span class="status-pill">{{ $notifications->total() }} total</span>
    <span class="status-pill">{{ $user->notifications()->unread()->count() }} unread</span>
@endsection

@section('header_actions')
    <form method="POST" action="{{ route('notifications.readAll') }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-fit">Mark All Read</button>
    </form>
@endsection

@section('content')
    <section class="glass-card">
        <div class="recent-session-list">
            @forelse ($notifications as $notification)
                <article class="recent-session-card {{ $notification->read_at ? '' : 'notification-unread' }}">
                    <span class="status-pill">{{ ucfirst($notification->type) }}</span>
                    <h3>{{ $notification->title }}</h3>
                    <p>{{ $notification->message }}</p>
                    <div class="recent-session-meta" style="align-items: center; justify-content: space-between;">
                        <span class="feature-badge">{{ $notification->created_at->format('M j, Y g:i A') }}</span>
                        <form method="POST" action="{{ route('notifications.toggleRead', $notification->id) }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-xs">
                                {{ $notification->read_at ? 'Read' : 'Unread' }}
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="empty-state"><div><h3>No notifications yet</h3><p>Updates will appear here when records are posted for your account.</p></div></div>
            @endforelse
        </div>
        <div class="pagination-wrap">{{ $notifications->links() }}</div>
    </section>
@endsection
