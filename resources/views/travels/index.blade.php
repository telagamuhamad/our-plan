@extends('layouts.app')

@section('title', 'Travel Planner')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-semibold">🌍 Travel Planner</h2>
        <a href="{{ route('travels.create') }}" class="btn btn-primary">➕ Tambah Rencana Perjalanan</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered align-middle bg-white">
            <thead class="table-light text-center">
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
                @forelse ($travels as $index => $travel)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $travel->destination }}</strong></td>
                        <td class="text-center">{{ $travel->formatted_visit_date ?? '-' }}</td>
                        <td class="text-center">
                            @if ($travel->meeting)
                                <span class="badge bg-light text-dark">
                                    {{ $travel->meeting->location }} <br>
                                    <small class="text-muted">({{ $travel->meeting->formatted_start_date }} – {{ $travel->meeting->formatted_end_date }})</small>
                                </span>
                            @else
                                <span class="text-muted fst-italic">Belum Terhubung</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill {{ $travel->completed ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $travel->completed ? 'Selesai' : 'Belum Selesai' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('travels.show', $travel->id) }}" class="btn btn-info btn-sm">👁️ Detail</a>
                            <a href="{{ route('travels.edit', $travel->id) }}" class="btn btn-warning btn-sm">✏️ Edit</a>
                            <form action="{{ route('travels.destroy', $travel->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">🗑️ Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">Belum ada rencana perjalanan yang dibuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
