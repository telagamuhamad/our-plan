@extends('layouts.app')

@section('title', 'Travel Analytics')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-semibold">📊 Travel Analytics</h2>
        <a href="{{ route('travels.index') }}" class="btn btn-secondary">⬅️ Kembali</a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Travel</h6>
                    <h3 class="text-primary mb-0">{{ $analytics['total_travels'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Selesai</h6>
                    <h3 class="text-success mb-0">{{ $analytics['completed_travels'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-warning">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Pending</h6>
                    <h3 class="text-warning mb-0">{{ $analytics['pending_travels'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-info">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Completion Rate</h6>
                    <h3 class="text-info mb-0">{{ $analytics['completion_rate'] }}%</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Foto</h6>
                    <h3 class="mb-0">📷 {{ $analytics['total_photos'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Journal</h6>
                    <h3 class="mb-0">📖 {{ $analytics['total_journals'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Journal Favorit</h6>
                    <h3 class="mb-0">⭐ {{ $analytics['favorite_journals'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Dengan Meeting</h6>
                    <h3 class="mb-0">{{ $analytics['travels_with_meeting'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Most Visited Destinations --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0">🏆 Destinasi Terfavorit</h6>
                </div>
                <div class="card-body">
                    @if($analytics['most_visited_destinations']->isNotEmpty())
                        <div class="list-group list-group-flush">
                            @foreach($analytics['most_visited_destinations'] as $destination => $count)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $destination }}</span>
                                    <span class="badge bg-primary">{{ $count }}x</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3 mb-0">Belum ada data destinasi.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Completed --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0">✅ Travel Selesai Terakhir</h6>
                </div>
                <div class="card-body">
                    @if($analytics['recent_completed']->isNotEmpty())
                        <div class="list-group list-group-flush">
                            @foreach($analytics['recent_completed'] as $travel)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $travel->destination }}</h6>
                                        <small class="text-muted">{{ $travel->formatted_visit_date }}</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">{{ $travel->photos->count() }} foto</small><br>
                                        <small class="text-muted">{{ $travel->journals->count() }} journal</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3 mb-0">Belum ada travel yang selesai.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Upcoming Travels --}}
    @if($analytics['upcoming_travels']->isNotEmpty())
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-warning">
            <h6 class="mb-0">📅 Travel Akan Datang</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($analytics['upcoming_travels'] as $travel)
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="mb-1">{{ $travel->destination }}</h6>
                                <p class="text-muted mb-2">{{ $travel->formatted_visit_date }}</p>
                                @if($travel->meeting)
                                    <small class="badge bg-light text-dark">🤝 {{ $travel->meeting->location }}</small>
                                @endif
                                <a href="{{ route('travels.show', $travel->id) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Travels by Month (Simple List) --}}
    @if(!empty($analytics['travels_by_month']))
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">📊 Travel per Bulan</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach(array_slice(array_reverse($analytics['travels_by_month']), 0, 6) as $month => $count)
                    @php
                        $date = \Carbon\Carbon::createFromFormat('Y-m', $month);
                        $monthName = $date->format('F Y');
                    @endphp
                    <div class="col-md-4 col-sm-6">
                        <div class="card bg-light">
                            <div class="card-body text-center py-2">
                                <h4 class="mb-0">{{ $count }}</h4>
                                <small class="text-muted">{{ $monthName }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
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
