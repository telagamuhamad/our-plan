@extends('layouts.app')

@section('title', 'Journal - ' . $travel->destination)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('travels.show', $travel->id) }}" class="btn btn-secondary">⬅️ Kembali ke Travel</a>
        </div>
        <a href="{{ route('journals.create') }}?travel_id={{ $travel->id }}" class="btn btn-primary">➕ Tulis Journal untuk Travel Ini</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="mb-2">🌍 {{ $travel->destination }}</h4>
            <p class="text-muted mb-0">Journal dari perjalanan ini</p>
        </div>
    </div>

    @if($journals->isNotEmpty())
        <div class="row g-4">
            @foreach($journals as $journal)
                <div class="col-md-6">
                    <div class="card shadow-sm h-100 {{ $journal->is_favorite ? 'border-warning' : '' }}">
                        @if($journal->is_favorite)
                        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                            <span>⭐ Favorit</span>
                            <small>{{ $journal->formatted_journal_date }}</small>
                        </div>
                        @else
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <span>📝 Journal</span>
                            <small>{{ $journal->formatted_journal_date }}</small>
                        </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $journal->title }}</h5>
                            <p class="card-text text-muted small mb-2">{{ Str::limit($journal->content, 150) }}</p>

                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-light text-dark">{{ $journal->mood_emoji }}</span>
                                @if($journal->weather)
                                <span class="badge bg-info text-white">🌤️ {{ $journal->weather }}</span>
                                @endif
                                @if($journal->location)
                                <span class="badge bg-success text-white">📍 {{ $journal->location }}</span>
                                @endif
                            </div>

                            <small class="text-muted">oleh {{ $journal->user->name }}</small>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('journals.show', $journal->id) }}" class="btn btn-sm btn-primary w-100">Baca Selengkapnya →</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div style="font-size: 4rem;">📖</div>
                <h5 class="text-muted mb-3">Belum ada journal untuk travel ini</h5>
                <p class="text-muted mb-3">Tulis pengalaman kalian selama di {{ $travel->destination }}!</p>
                <a href="{{ route('journals.create') }}?travel_id={{ $travel->id }}" class="btn btn-primary">✍️ Tulis Journal Pertama</a>
            </div>
        </div>
    @endif
</div>
@endsection
