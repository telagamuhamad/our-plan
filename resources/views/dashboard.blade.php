@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $user = Auth::user();
    $partner = $user->partner();
@endphp

<div class="container py-5">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Countdown Widget -->
    @if($countdown && $countdown['has_upcoming'])
    <div class="card shadow-sm mb-4 border-primary">
        <div class="card-body text-center">
            <h5 class="card-title text-primary mb-3">⏰ Countdown ke Meeting Berikutnya</h5>
            <h4 class="fw-bold mb-2">{{ $countdown['meeting']->location ?? 'Tanpa Lokasi' }}</h4>
            <p class="text-muted mb-3">{{ $countdown['meeting']->formatted_start_date }} – {{ $countdown['meeting']->formatted_end_date }}</p>

            <div class="countdown-timer d-flex justify-content-center gap-3 mb-3" id="countdown-timer" data-seconds="{{ $countdown['countdown']['total_seconds'] }}">
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-days">{{ $countdown['countdown']['days'] }}</div>
                    <div class="countdown-label">Hari</div>
                </div>
                <div class="countdown-separator">:</div>
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-hours">{{ $countdown['countdown']['hours'] }}</div>
                    <div class="countdown-label">Jam</div>
                </div>
                <div class="countdown-separator">:</div>
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-minutes">{{ $countdown['countdown']['minutes'] }}</div>
                    <div class="countdown-label">Menit</div>
                </div>
                <div class="countdown-separator">:</div>
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-seconds">{{ $countdown['countdown']['seconds'] }}</div>
                    <div class="countdown-label">Detik</div>
                </div>
            </div>

            <p class="text-muted small mb-0">{{ $countdown['message'] }}</p>
            <a href="{{ route('meetings.index') }}" class="btn btn-outline-primary btn-sm mt-2">Lihat Semua Meeting</a>
        </div>
    </div>

    <style>
        .countdown-timer {
            font-size: 1.5rem;
        }
        .countdown-item {
            text-align: center;
            min-width: 60px;
        }
        .countdown-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .countdown-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
        }
        .countdown-separator {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0d6efd;
            padding-top: 0.5rem;
        }
    </style>

    <script>
        // Real-time countdown update
        document.addEventListener('DOMContentLoaded', function() {
            const timerElement = document.getElementById('countdown-timer');
            if (!timerElement) return;

            let totalSeconds = parseInt(timerElement.dataset.seconds);

            function updateCountdown() {
                if (totalSeconds <= 0) {
                    setTimeout(() => location.reload(), 1000);
                    return;
                }

                totalSeconds--;

                const days = Math.floor(totalSeconds / (24 * 60 * 60));
                const hours = Math.floor((totalSeconds % (24 * 60 * 60)) / (60 * 60));
                const minutes = Math.floor((totalSeconds % (60 * 60)) / 60);
                const seconds = totalSeconds % 60;

                document.getElementById('countdown-days').textContent = String(days).padStart(2, '0');
                document.getElementById('countdown-hours').textContent = String(hours).padStart(2, '0');
                document.getElementById('countdown-minutes').textContent = String(minutes).padStart(2, '0');
                document.getElementById('countdown-seconds').textContent = String(seconds).padStart(2, '0');
            }

            setInterval(updateCountdown, 1000);
        });
    </script>
    @endif

    <!-- Header with Partner Info -->
    @if($partner)
        <div class="text-center mb-4">
            <div class="display-1 mb-3">💕</div>
            <h2 class="fw-bold">Halo, {{ $user->name }}!</h2>
            <p class="text-muted">
                Anda dan <strong>{{ $partner->name }}</strong> sudah terhubung
            </p>
        </div>

        <!-- Partner Card -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 text-center">
                        @if($partner->avatar_url)
                            <img src="{{ $partner->avatar_url }}"
                                 alt="{{ $partner->name }}"
                                 class="rounded-circle mb-3"
                                 style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width: 80px; height: 80px; font-size: 2rem; color: white;">
                                {{ substr($partner->name, 0, 1) }}
                            </div>
                        @endif
                        <h5 class="fw-bold mb-1">{{ $partner->name }}</h5>
                        <p class="text-muted small mb-0">{{ $partner->username }}</p>
                        <p class="text-muted small mb-0">Timezone: {{ $partner->timezone }}</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center mb-4">
            <div class="display-1 mb-3">👋</div>
            <h2 class="fw-bold">Halo, {{ $user->name }}!</h2>
            <p class="text-muted">
                Mulai hubungkan dengan pasangan untuk menikmati semua fitur
            </p>
        </div>
    @endif

    <!-- Core Features -->
    <h5 class="mb-3 text-center text-muted">💕 Core Features</h5>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4">
        <!-- Timeline -->
        <div class="col">
            <a href="{{ route('timeline.index') }}"
               class="btn btn-primary w-100 h-100 d-flex flex-column justify-content-center"
               style="border-radius: 12px; padding: 1.5rem; min-height: 100px;">
                <span class="fs-2">📰</span>
                <span class="fw-semibold mt-2 small">Timeline</span>
            </a>
        </div>

        <!-- Mood Check-In -->
        <div class="col">
            <a href="{{ route('mood.index') }}"
               class="btn btn-primary w-100 h-100 d-flex flex-column justify-content-center"
               style="border-radius: 12px; padding: 1.5rem; min-height: 100px;">
                <span class="fs-2">😊</span>
                <span class="fw-semibold mt-2 small">Mood</span>
            </a>
        </div>

        <!-- Questions -->
        <div class="col">
            <a href="{{ route('questions.index') }}"
               class="btn btn-primary w-100 h-100 d-flex flex-column justify-content-center"
               style="border-radius: 12px; padding: 1.5rem; min-height: 100px;">
                <span class="fs-2">❓</span>
                <span class="fw-semibold mt-2 small">Questions</span>
            </a>
        </div>

        <!-- Goals & Tasks -->
        <div class="col">
            <a href="{{ route('goals.index') }}"
               class="btn btn-primary w-100 h-100 d-flex flex-column justify-content-center"
               style="border-radius: 12px; padding: 1.5rem; min-height: 100px;">
                <span class="fs-2">🎯</span>
                <span class="fw-semibold mt-2 small">Goals</span>
            </a>
        </div>

        <!-- Missing You -->
        <div class="col">
            <a href="{{ route('missing-you.index') }}"
               class="btn btn-danger w-100 h-100 d-flex flex-column justify-content-center"
               style="border-radius: 12px; padding: 1.5rem; min-height: 100px;">
                <span class="fs-2">💕</span>
                <span class="fw-semibold mt-2 small">Missing You</span>
            </a>
        </div>
    </div>

    <!-- Planning Features -->
    <h5 class="mb-3 text-center text-muted">📅 Planning Features</h5>
    <div class="row row-cols-2 row-cols-md-3 g-3">
        <!-- Meeting Planner -->
        <div class="col">
            <a href="{{ route('meetings.index') }}"
               class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center"
               style="border-radius: 12px; padding: 1.5rem; min-height: 100px;">
                <span class="fs-2">📅</span>
                <span class="fw-semibold mt-2 small">Pertemuan</span>
            </a>
        </div>

        <!-- Travel Planner -->
        <div class="col">
            <a href="{{ route('travels.index') }}"
               class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center"
               style="border-radius: 12px; padding: 1.5rem; min-height: 100px;">
                <span class="fs-2">✈️</span>
                <span class="fw-semibold mt-2 small">Perjalanan</span>
            </a>
        </div>

        <!-- Savings Tracker -->
        <div class="col">
            <a href="{{ route('savings.index') }}"
               class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center"
               style="border-radius: 12px; padding: 1.5rem; min-height: 100px;">
                <span class="fs-2">💰</span>
                <span class="fw-semibold mt-2 small">Tabungan</span>
            </a>
        </div>
    </div>
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

    .btn-danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        transition: all 0.2s ease;
    }

    .btn-danger:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(245, 87, 108, 0.3);
    }
</style>
@endsection
