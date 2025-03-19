@extends('layouts.app')

@section('title', 'Detail Rencana Perjalanan')

@section('content')
<div class="container">
    <h2>📍 {{ $travel->destination }}</h2>

    <ul class="list-group">
        <li class="list-group-item"><strong>Tanggal Kunjungan:</strong> {{ $travel->formatted_visit_date }}</li>
        <li class="list-group-item"><strong>Status:</strong> 
            <span class="badge {{ $travel->completed ? 'bg-success' : 'bg-warning' }}">
                {{ $travel->completed ? 'Selesai' : 'Belum Selesai' }}
            </span>
        </li>
        <li class="list-group-item"><strong>Meeting Terkait:</strong>
            @if ($travel->meeting)
                {{ $travel->meeting->location }} ({{ $travel->meeting->formatted_start_date }} - {{ $travel->meeting->formatted_end_date }})
            @else
                <span class="text-muted">Tidak ada Meeting</span>
            @endif
        </li>
    </ul>

    <a href="{{ route('travels.index') }}" class="btn btn-secondary mt-3">⬅️ Kembali</a>
</div>
@endsection
