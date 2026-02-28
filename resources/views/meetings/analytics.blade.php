@extends('layouts.app')

@section('title', 'Meeting Analytics')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-semibold">📊 Meeting Analytics</h2>
        <a href="{{ route('meetings.index') }}" class="btn btn-secondary">⬅️ Kembali</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Meeting</h6>
                    <h3 class="text-primary mb-0">{{ $analytics['total_meetings'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Selesai</h6>
                    <h3 class="text-success mb-0">{{ $analytics['completed_meetings'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-warning">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Upcoming</h6>
                    <h3 class="text-warning mb-0">{{ $analytics['upcoming_meetings'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-info">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Hari Bersama</h6>
                    <h3 class="text-info mb-0">{{ $analytics['total_days_spent'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Rating Rata-rata</h6>
                    <div class="d-flex align-items-center justify-content-center">
                        <h2 class="mb-0 me-2">{{ $analytics['average_rating'] > 0 ? $analytics['average_rating'] : '-' }}</h2>
                        @if($analytics['average_rating'] > 0)
                        <span class="text-warning fs-4">{{ str_repeat('★', round($analytics['average_rating'])) }}</span>
                        @endif
                    </div>
                    <small class="text-muted">{{ $analytics['total_feedbacks'] }} feedback diberikan</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Lokasi Favorit</h6>
                    <h4 class="mb-0">{{ $analytics['most_frequent_location'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Meeting Tahun Ini</h6>
                    <h3 class="mb-0">{{ $analytics['this_year_meetings'] }}</h3>
                    <small class="text-muted">{{ $analytics['this_month_meetings'] }} bulan ini</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Meetings by Location --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0">📍 Meeting berdasarkan Lokasi</h6>
                </div>
                <div class="card-body">
                    @if($analytics['meetings_by_location']->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Lokasi</th>
                                        <th class="text-center">Jumlah</th>
                                        <th>Kunjungan Terakhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytics['meetings_by_location'] as $location => $data)
                                        <tr>
                                            <td><strong>{{ $location }}</strong></td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $data['count'] }}x</span>
                                            </td>
                                            <td class="text-muted small">{{ \Carbon\Carbon::parse($data['last_visit'])->format('M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3 mb-0">Belum ada data lokasi.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Meetings --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0">📅 Meeting Terakhir</h6>
                </div>
                <div class="card-body">
                    @if($analytics['recent_meetings']->isNotEmpty())
                        <div class="list-group list-group-flush">
                            @foreach($analytics['recent_meetings'] as $meeting)
                                @php
                                    $isPast = \Carbon\Carbon::parse($meeting->end_date)->isPast();
                                    $isFuture = \Carbon\Carbon::parse($meeting->start_date)->isFuture();
                                @endphp
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $meeting->location ?? 'Tanpa Lokasi' }}</h6>
                                            <small class="text-muted">{{ $meeting->formatted_start_date }}</small>
                                        </div>
                                        @if($isPast)
                                            <span class="badge bg-success">Selesai</span>
                                        @elseif($isFuture)
                                            <span class="badge bg-warning text-dark">Akan Datang</span>
                                        @else
                                            <span class="badge bg-info">Berlangsung</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3 mb-0">Belum ada meeting.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Next Meeting Countdown --}}
    @if($analytics['next_meeting'])
        <div class="card shadow-sm mt-4 border-primary">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">⏰ Meeting Berikutnya</h6>
                <h5 class="mb-2">{{ $analytics['next_meeting']->location ?? 'Tanpa Lokasi' }}</h5>
                <p class="text-muted mb-0">{{ $analytics['next_meeting']->formatted_start_date }} – {{ $analytics['next_meeting']->formatted_end_date }}</p>
                <p class="text-primary fw-bold mt-2">{{ $analytics['next_meeting']->formatted_countdown }}</p>
            </div>
        </div>
    @endif
</div>

<style>
    .card {
        border-radius: 12px;
    }
    .list-group-item {
        border: none;
        border-bottom: 1px solid #dee2e6;
    }
    .list-group-item:last-child {
        border-bottom: none;
    }
</style>
@endsection
