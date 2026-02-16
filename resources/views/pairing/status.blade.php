@extends('layouts.app')

@section('title', 'Status Pairing')

@section('content')
@php
    $hasCouple = $coupleInfo !== null;
    $isPending = $hasCouple && $coupleInfo['status'] === 'pending';
    $isActive = $hasCouple && $coupleInfo['status'] === 'active';
@endphp

<div class="container py-5">
    @if (!$hasCouple)
        <!-- Not Paired State -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="text-center mb-4">
                    <div class="display-1 mb-3">💑</div>
                    <h2 class="fw-bold">Belum Terhubung</h2>
                    <p class="text-muted">Mulai dengan membuat kode undangan atau gabung dengan pasangan</p>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <a href="{{ route('pairing.create-invite') }}"
                           class="btn btn-primary w-100 h-100 d-flex flex-column justify-content-center"
                           style="border-radius: 12px; padding: 1.5rem;">
                            <span class="fs-2">📨</span>
                            <span class="fw-semibold mt-2">Buat Kode</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('pairing.join') }}"
                           class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center"
                           style="border-radius: 12px; padding: 1.5rem;">
                            <span class="fs-2">🔗</span>
                            <span class="fw-semibold mt-2">Masuk Kode</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    @elseif($isPending)
        <!-- Pending State -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="text-center mb-4">
                    <div class="display-1 mb-3">⏳</div>
                    <h2 class="fw-bold">Hampir Selesai!</h2>
                    <p class="text-muted">Menunggu konfirmasi dari kedua belah pihak</p>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        @if($coupleInfo['is_user_one'])
                            <!-- User One View -->
                            <p class="mb-3 fw-semibold">Bagikan kode ini ke pasangan Anda:</p>
                            <div class="alert alert-info text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                                <span class="fs-2 fw-bold" style="letter-spacing: 0.3rem;">{{ $coupleInfo['invite_code'] }}</span>
                            </div>

                            @if(!$coupleInfo['user_two_confirmed'])
                                <p class="text-muted small text-center mt-3 mb-0">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Menunggu pasangan bergabung...
                                </p>
                            @else
                                <p class="text-success small text-center mt-3 mb-3">
                                    ✓ Pasangan sudah bergabung!
                                </p>
                                @if($coupleInfo['can_confirm'])
                                    <form method="POST" action="{{ route('pairing.confirm') }}" class="mt-3">
                                        @csrf
                                        <input type="hidden" name="couple_id" value="{{ $coupleInfo['id'] }}">
                                        <button type="submit" class="btn btn-success w-100 btn-lg">
                                            ✓ Konfirmasi Pairing
                                        </button>
                                    </form>
                                @else
                                    <p class="text-muted small text-center">
                                        Menunggu konfirmasi dari pasangan...
                                    </p>
                                @endif
                            @endif
                        @else
                            <!-- User Two View -->
                            <p class="mb-3 fw-semibold">Anda sudah bergabung!</p>

                            @if($coupleInfo['partner'])
                                <div class="text-center mb-3">
                                    <div class="display-6 mb-2">👤</div>
                                    <p class="fw-semibold mb-0">{{ $coupleInfo['partner']->name }}</p>
                                    <p class="text-muted small">{{ $coupleInfo['partner']->email }}</p>
                                </div>
                            @endif

                            @if($coupleInfo['can_confirm'])
                                <form method="POST" action="{{ route('pairing.confirm') }}" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="couple_id" value="{{ $coupleInfo['id'] }}">
                                    <button type="submit" class="btn btn-success w-100 btn-lg">
                                        ✓ Konfirmasi Pairing
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-info text-center">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Menunggu konfirmasi dari pasangan...
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('pairing.leave') }}" class="text-center">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted text-decoration-none">
                        Batal dan keluar
                    </button>
                </form>
            </div>
        </div>

    @else
        <!-- Active State -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="text-center mb-4">
                    <div class="display-1 mb-3">💕</div>
                    <h2 class="fw-bold">Anda Sudah Terhubung!</h2>
                    <p class="text-muted">
                        @if($coupleInfo['partner'])
                            Anda dan <strong>{{ $coupleInfo['partner']->name }}</strong> sudah terhubung
                        @else
                            Anda sudah terhubung dengan pasangan
                        @endif
                    </p>
                </div>

                @if($coupleInfo['partner'])
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4 text-center">
                            @if($coupleInfo['partner']->avatar_url)
                                <img src="{{ $coupleInfo['partner']->avatar_url }}"
                                     alt="{{ $coupleInfo['partner']->name }}"
                                     class="rounded-circle mb-3"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3"
                                     style="width: 100px; height: 100px; font-size: 2.5rem; color: white;">
                                    {{ substr($coupleInfo['partner']->name, 0, 1) }}
                                </div>
                            @endif
                            <h4 class="fw-bold mb-1">{{ $coupleInfo['partner']->name }}</h4>
                            <p class="text-muted mb-0">{{ $coupleInfo['partner']->email }}</p>
                            <p class="text-muted small mb-0">
                                Timezone: {{ $coupleInfo['partner']->timezone }}
                            </p>
                        </div>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-4">
                        <a href="{{ route('meetings.index') }}"
                           class="btn btn-primary w-100 h-100 d-flex flex-column justify-content-center"
                           style="border-radius: 12px; padding: 1.5rem; min-height: 120px;">
                            <span class="fs-2">📅</span>
                            <span class="fw-semibold mt-2">Pertemuan</span>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="{{ route('travels.index') }}"
                           class="btn btn-primary w-100 h-100 d-flex flex-column justify-content-center"
                           style="border-radius: 12px; padding: 1.5rem; min-height: 120px;">
                            <span class="fs-2">✈️</span>
                            <span class="fw-semibold mt-2">Perjalanan</span>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="{{ route('savings.index') }}"
                           class="btn btn-primary w-100 h-100 d-flex flex-column justify-content-center"
                           style="border-radius: 12px; padding: 1.5rem; min-height: 120px;">
                            <span class="fs-2">💰</span>
                            <span class="fw-semibold mt-2">Tabungan</span>
                        </a>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <form method="POST" action="{{ route('pairing.leave') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link text-danger text-decoration-none small">
                            Keluar dari pasangan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .card {
        border-radius: 12px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
    }

    .btn-outline-primary {
        border-width: 2px;
        transition: all 0.2s ease;
    }

    .btn-outline-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 15px rgba(102, 126, 234, 0.2);
    }
</style>
@endsection
