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
                               class="text-decoration-none notification-link">
                                <div class="d-flex justify-content-between">
                                    <p class="mb-1 {{ !$notification->is_read ? 'fw-semibold' : '' }}">
                                        {{ $notification->message }}
                                    </p>
                                    @if(!$notification->is_read)
                                        <span class="badge bg-primary rounded-pill">Baru</span>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ $notification->created_at->diffForHumans() }}
                                </small>
                            </a>
                        @else
                            <div class="d-flex justify-content-between">
                                <p class="mb-1 {{ !$notification->is_read ? 'fw-semibold' : '' }}">
                                    {{ $notification->message }}
                                </p>
                                @if(!$notification->is_read)
                                    <span class="badge bg-primary rounded-pill">Baru</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ $notification->created_at->diffForHumans() }}
                            </small>
                        @endif
                    </div>

                    <!-- Delete button -->
                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="flex-shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-link text-muted p-0"
                                onclick="return confirm('Hapus notifikasi ini?');">
                            ✕
                        </button>
                    </form>
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
@endsection
