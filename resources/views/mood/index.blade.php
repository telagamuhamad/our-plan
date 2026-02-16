@extends('layouts.app')

@section('title', 'Mood Check-In - Our Plan')

@section('content')
<div class="mood-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">📊 Mood Check-In</h4>
            <p class="text-muted mb-0 small">Bagaimana perasaanmu hari ini?</p>
        </div>
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

    <div class="row g-4">
        <!-- Today's Check-in -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Hari Ini</h5>
                    <p class="text-muted small">{{ now()->translatedFormat('l, d F Y') }}</p>

                    @if($hasCheckedIn)
                        <!-- Already Checked In -->
                        <div class="text-center my-4">
                            <div class="display-1 mb-2">{{ $myTodayMood->mood_emoji }}</div>
                            <h6 class="fw-semibold">{{ $myTodayMood->mood_label }}</h6>
                            @if($myTodayMood->note)
                                <p class="text-muted small mt-2">{{ e($myTodayMood->note) }}</p>
                            @endif
                        </div>

                        <!-- Update Button -->
                        <button type="button" class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#updateMoodModal">
                            Update Mood
                        </button>
                    @else
                        <!-- Check-in Form -->
                        <form action="{{ route('mood.check-in') }}" method="POST" class="check-in-form">
                            @csrf
                            <div class="mood-options mb-3">
                                @foreach($availableMoods as $key => $emoji)
                                    <label class="mood-option">
                                        <input type="radio" name="mood" value="{{ $key }}" required>
                                        <span class="mood-emoji">{{ $emoji }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <label class="form-label small">Catatan (opsional)</label>
                                <textarea name="note" class="form-control" rows="2" maxlength="500" placeholder="Ceritakan sedikit tentang harimu..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Check-In</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Partner's Mood -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Mood Pasangan</h5>
                    <p class="text-muted small">Hari Ini</p>

                    @php
                        $partnerTodayMood = $todayMoods->firstWhere('user_id', '!=', Auth::id());
                    @endphp

                    @if($partnerTodayMood)
                        <div class="text-center my-4">
                            <div class="display-1 mb-2">{{ $partnerTodayMood->mood_emoji }}</div>
                            <h6 class="fw-semibold">{{ $partnerTodayMood->mood_label }}</h6>
                            @if($partnerTodayMood->note)
                                <p class="text-muted small mt-2">{{ e($partnerTodayMood->note) }}</p>
                            @endif
                        </div>
                    @else
                        <div class="text-center my-4">
                            <div class="display-1 mb-2 text-muted">💭</div>
                            <p class="text-muted">Belum check-in hari ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Mood Stats -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Statistik Mood</h5>
                    <p class="text-muted small">{{ $days }} hari terakhir</p>

                    <div id="moodStats" class="mt-3">
                        @foreach($moodStats as $mood => $count)
                            @if($mood !== 'total' && $count > 0)
                                @php
                                    $emoji = $availableMoods[$mood] ?? '';
                                    $percentage = $moodStats['total'] > 0 ? round(($count / $moodStats['total']) * 100) : 0;
                                @endphp
                                <div class="d-flex align-items-center mb-2">
                                    <span class="me-2">{{ $emoji }}</span>
                                    <div class="flex-grow-1">
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                    <span class="ms-2 small">{{ $count }}</span>
                                </div>
                            @endif
                        @endforeach

                        @if($moodStats['total'] === 0)
                            <p class="text-muted text-center">Belum ada data mood</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mood History -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title mb-3">📅 Riwayat Mood</h5>

            @if($moodHistory->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Mood</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($moodHistory as $mood)
                                <tr>
                                    <td>{{ $mood->formatted_date }}</td>
                                    <td>{{ $mood->user->name }}</td>
                                    <td>
                                        <span class="badge bg-light">
                                            {{ $mood->mood_emoji }} {{ $mood->mood_label }}
                                        </span>
                                        @if($mood->is_updated)
                                            <span class="badge bg-warning text-dark ms-1">Diupdate</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $mood->note ? e($mood->note) : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">Belum ada riwayat mood check-in</p>
            @endif
        </div>
    </div>
</div>
@endsection

<!-- Update Mood Modal - placed in modals stack to avoid z-index issues with glass-container -->
@push('modals')
@if($hasCheckedIn)
<div class="modal fade" id="updateMoodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('mood.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="mood_id" value="{{ $myTodayMood->id }}">

                <div class="modal-header">
                    <h5 class="modal-title">Update Mood Hari Ini</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mood-options mb-3">
                        @foreach($availableMoods as $key => $emoji)
                            <label class="mood-option @if($myTodayMood->mood === $key) active @endif">
                                <input type="radio" name="mood" value="{{ $key }}" required @if($myTodayMood->mood === $key) checked @endif>
                                <span class="mood-emoji">{{ $emoji }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Catatan (opsional)</label>
                        <textarea name="note" class="form-control" rows="3" maxlength="500">{{ e($myTodayMood->note ?? '') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endpush

<style>
    .mood-options {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
    }

    .mood-option {
        cursor: pointer;
        position: relative;
    }

    .mood-option input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .mood-emoji {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        font-size: 1.75rem;
        border-radius: 12px;
        background-color: #f8f9fa;
        border: 2px solid transparent;
        transition: all 0.2s;
    }

    .mood-option:hover .mood-emoji {
        background-color: #e9ecef;
        transform: scale(1.1);
    }

    .mood-option input:checked + .mood-emoji,
    .mood-option.active .mood-emoji {
        background-color: #d0ebff;
        border-color: #0d6efd;
    }

    .progress-bar {
        transition: width 0.5s ease;
    }
</style>
