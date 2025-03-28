@extends('layouts.app')

@section('title', 'Detail Rencana Perjalanan')

@section('content')
<div class="container">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h3 class="card-title mb-4">📍 <strong>{{ $travel->destination }}</strong></h3>

            <p><strong>🗓️ Tanggal Kunjungan:</strong> {{ $travel->formatted_visit_date }}</p>

            <p>
                <strong>📌 Status:</strong>
                <span class="badge {{ $travel->completed ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ $travel->completed ? 'Selesai' : 'Belum Selesai' }}
                </span>
            </p>

            <p>
                <strong>🤝 Meeting Terkait:</strong>
                @if ($travel->meeting)
                    {{ $travel->meeting->location }} <br>
                    <small class="text-muted">({{ $travel->meeting->formatted_start_date }} – {{ $travel->meeting->formatted_end_date }})</small>
                @else
                    <span class="text-muted fst-italic">Tidak ada Meeting</span>
                @endif
            </p>
        </div>
    </div>

    <a href="{{ route('travels.index') }}" class="btn btn-secondary">⬅️ Kembali</a>
</div>
@endsection
