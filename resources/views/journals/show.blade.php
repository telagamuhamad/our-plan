@extends('layouts.app')

@section('title', $journal->title)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('journals.index') }}" class="btn btn-secondary">⬅️ Kembali</a>
        </div>
        <div class="btn-group">
            @if($journal->user_id === auth()->id())
                <a href="{{ route('journals.edit', $journal->id) }}" class="btn btn-warning">✏️ Edit</a>
                <form action="{{ route('journals.destroy', $journal->id) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus journal ini?')">🗑️ Hapus</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        @if($journal->is_favorite)
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">⭐ {{ $journal->title }}</h5>
        </div>
        @else
        <div class="card-header bg-light">
            <h5 class="mb-0">📝 {{ $journal->title }}</h5>
        </div>
        @endif
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="mb-1"><strong>📅 Tanggal:</strong> {{ $journal->formatted_journal_date }}</p>
                    <p class="mb-1"><strong>✍️ Penulis:</strong> {{ $journal->user->name }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>😊 Mood:</strong> {{ $journal->mood_emoji }}</p>
                    @if($journal->weather)
                    <p class="mb-1"><strong>🌤️ Cuaca:</strong> {{ $journal->weather }}</p>
                    @endif
                    @if($journal->location)
                    <p class="mb-1"><strong>📍 Lokasi:</strong> {{ $journal->location }}</p>
                    @endif
                </div>
            </div>

            @if($journal->travel)
                <div class="alert alert-info">
                    <strong>🌍 Terkait dengan travel:</strong>
                    <a href="{{ route('travels.show', $journal->travel->id) }}">{{ $journal->travel->destination }}</a>
                    ({{ $journal->travel->formatted_visit_date }})
                </div>
            @endif

            <hr>

            {{-- Journal Content --}}
            <div class="journal-content mt-4">
                <div style="line-height: 1.8; white-space: pre-wrap;">{{ $journal->content }}</div>
            </div>
        </div>
        <div class="card-footer bg-light">
            <form action="{{ route('journals.favorite', $journal->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm {{ $journal->is_favorite ? 'btn-warning' : 'btn-outline-warning' }}">
                    {{ $journal->is_favorite ? '⭐ Hapus dari Favorit' : '☆ Tandai Favorit' }}
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .journal-content {
        font-size: 1.1rem;
    }
</style>
@endsection
