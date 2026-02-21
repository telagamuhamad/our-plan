@extends('layouts.app')

@section('title', 'Travel Journal')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-semibold">📖 Travel Journal</h2>
        <a href="{{ route('journals.create') }}" class="btn btn-primary">✍️ Tulis Journal</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Search --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('journals.index') }}" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari journal..." value="{{ $search ?? '' }}">
                    <button type="submit" class="btn btn-outline-secondary">🔍 Cari</button>
                    @if($search)
                        <a href="{{ route('journals.index') }}" class="btn btn-outline-secondary">✖️ Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Journals List --}}
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
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">{{ $journal->mood_emoji }}</span>
                                @if($journal->weather)
                                <span class="badge bg-info text-white">🌤️ {{ $journal->weather }}</span>
                                @endif
                                @if($journal->location)
                                <span class="badge bg-success text-white">📍 {{ $journal->location }}</span>
                                @endif
                            </div>

                            @if($journal->travel)
                                <small class="text-muted">
                                    🌍 Travel: <a href="{{ route('travels.show', $journal->travel->id) }}">{{ $journal->travel->destination }}</a>
                                </small>
                            @endif

                            <small class="text-muted d-block mt-2">oleh {{ $journal->user->name }}</small>
                        </div>
                        <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                            <a href="{{ route('journals.show', $journal->id) }}" class="btn btn-sm btn-outline-primary">Baca Selengkapnya →</a>
                            <div class="btn-group">
                                @if($journal->user_id === auth()->id())
                                    <a href="{{ route('journals.edit', $journal->id) }}" class="btn btn-sm btn-outline-warning">✏️</a>
                                @endif
                                <form action="{{ route('journals.favorite', $journal->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $journal->is_favorite ? 'btn-warning' : 'btn-outline-warning' }}">
                                        {{ $journal->is_favorite ? '⭐' : '☆' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div style="font-size: 4rem;">📖</div>
                <h5 class="text-muted mb-3">Belum ada journal</h5>
                <p class="text-muted mb-3">Mulai menulis tentang perjalanan kalian bersama!</p>
                <a href="{{ route('journals.create') }}" class="btn btn-primary">✍️ Tulis Journal Pertama</a>
            </div>
        </div>
    @endif
</div>
@endsection
