@extends('layouts.app')

@section('title', 'Notifikasi - Our Plan')

@section('content')
<div class="notifications-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">🔔 Notifikasi</h4>
            <p class="text-muted mb-0 small">Aktivitas terbaru dari pasanganmu</p>
        </div>
        @if($unreadCount > 0)
            <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Notifications List -->
    @if($notifications->count() > 0)
        <div class="list-group">
            @foreach($notifications as $notification)
                <div class="list-group-item list-group-item-action
                    {{ !$notification->is_read ? 'bg-light' : '' }}
                    d-flex gap-3 align-items-start">
                    <!-- Icon based on type -->
                    <div class="flex-shrink-0">
                        @if($notification->type === 'reaction')
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                 style="width: 40px; height: 40px;">
                                ❤️
                            </div>
                        @elseif($notification->type === 'comment')
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                 style="width: 40px; height: 40px;">
                                💬
                            </div>
                        @else
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                 style="width: 40px; height: 40px;">
                                🔔
                            </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="flex-grow-1">
                        @if($notification->link)
                            <a href="{{ $notification->link }}?notification_id={{ $notification->id }}"
                               class="text-decoration-none notification-link d-block">
                                <p class="mb-1 {{ !$notification->is_read ? 'fw-semibold' : '' }}">
                                    {{ $notification->message }}
                                    @if(!$notification->is_read)
                                        <span class="badge bg-primary rounded-pill ms-1 align-middle">Baru</span>
                                    @endif
                                </p>
                                <small class="text-muted">
                                    {{ $notification->created_at->diffForHumans() }}
                                </small>
                            </a>
                        @else
                            <p class="mb-1 {{ !$notification->is_read ? 'fw-semibold' : '' }}">
                                {{ $notification->message }}
                                @if(!$notification->is_read)
                                    <span class="badge bg-primary rounded-pill ms-1 align-middle">Baru</span>
                                @endif
                            </p>
                            <small class="text-muted">
                                {{ $notification->created_at->diffForHumans() }}
                            </small>
                        @endif
                    </div>

                    <!-- Action buttons -->
                    <div class="flex-shrink-0 d-flex align-items-center">
                        @if(!$notification->is_read)
                            <button type="button"
                                    class="btn btn-link text-primary p-0 mark-read-btn d-flex align-items-center"
                                    data-notification-id="{{ $notification->id }}"
                                    title="Tandai sebagai dibaca">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                                </svg>
                            </button>
                        @endif
                        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline ms-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link text-muted p-0 d-flex align-items-center"
                                    onclick="return confirm('Hapus notifikasi ini?');"
                                    title="Hapus notifikasi">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="mt-4">
                {{ $notifications->appends([])->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-5">
            <div class="mb-3" style="font-size: 64px;">🔕</div>
            <h5 class="text-muted">Tidak ada notifikasi</h5>
            <p class="text-muted">Belum ada aktivitas dari pasanganmu.</p>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle mark as read button
    document.querySelectorAll('.mark-read-btn').forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.dataset.notificationId;
            const notificationItem = this.closest('.list-group-item');
            const badge = notificationItem.querySelector('.badge');
            const messageText = notificationItem.querySelector('.mb-1');

            fetch(`/notifications/mark-read/${notificationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the mark as read button
                    this.remove();

                    // Remove the "Baru" badge
                    if (badge) {
                        badge.remove();
                    }

                    // Remove bold styling from message
                    if (messageText) {
                        messageText.classList.remove('fw-semibold');
                    }

                    // Remove light background
                    notificationItem.classList.remove('bg-light');

                    // Update unread count in navbar if exists
                    const unreadCountBadge = document.querySelector('.navbar .badge');
                    if (unreadCountBadge) {
                        const currentCount = parseInt(unreadCountBadge.textContent);
                        if (currentCount > 1) {
                            unreadCountBadge.textContent = currentCount - 1;
                        } else {
                            unreadCountBadge.closest('.position-absolute')?.remove();
                            if (!unreadCountBadge.closest('.position-absolute')) {
                                unreadCountBadge.remove();
                            }
                        }
                    }

                    // Hide "Mark all as read" button if no more unread notifications
                    const markAllButton = document.querySelector('form[action*="mark-all-read"]');
                    if (markAllButton && !document.querySelector('.mark-read-btn')) {
                        markAllButton.remove();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
        });
    });
});
</script>
@endsection
