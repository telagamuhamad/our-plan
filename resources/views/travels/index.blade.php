@extends('layouts.app')

@section('title', 'Travel Planner')

@section('content')
<div class="container">
    <h2>🌍 Travel Planner</h2>
    <a href="{{ route('travels.create') }}" class="btn btn-primary mb-3">➕ Tambah Rencana Perjalanan</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Destinasi</th>
                <th>Tanggal Kunjungan</th>
                <th>Meeting Terkait</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($travels as $index => $travel)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $travel->destination }}</strong></td>
                <td>{{ $travel->formatted_visit_date ?? '-'}}</td>
                <td>
                    @if ($travel->meeting)
                        {{ $travel->meeting->location }} ({{ $travel->meeting->formatted_start_date }} - {{ $travel->meeting->formatted_end_date }})
                    @else
                        <span class="text-muted">Tidak ada Meeting</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $travel->completed ? 'bg-success' : 'bg-warning' }}">
                        {{ $travel->completed ? 'Selesai' : 'Belum Selesai' }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('travels.show', $travel->id) }}" class="btn btn-info btn-sm">👁️ Detail</a>
                    <a href="{{ route('travels.edit', $travel->id) }}" class="btn btn-warning btn-sm">✏️ Edit</a>
                    <form action="{{ route('travels.destroy', $travel->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">🗑️ Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($travels->isEmpty())
        <div class="alert alert-warning text-center">
            Belum ada rencana perjalanan yang dibuat.
        </div>
    @endif
</div>
@endsection
