@extends('layouts.app')

@section('title', 'Detail Meeting')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-semibold">📅 Detail Meeting</h2>
        <a href="{{ route('meetings.index') }}" class="btn btn-secondary">⬅️ Kembali</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3"><strong>📍 {{ $meeting->location }}</strong> <span class="text-muted">({{ $meeting->formatted_start_date }} – {{ $meeting->formatted_end_date }})</span></h5>
            
            <ul class="list-unstyled mb-0">
                <li><strong>🧑 Siapa yang Berangkat:</strong> {{ $meeting->user->name }}</li>
                <li><strong>🗓️ Rentang Tanggal:</strong> {{ $meeting->formatted_start_date }} – {{ $meeting->formatted_end_date }}</li>
                <li><strong>📌 Lokasi:</strong> {{ $meeting->location ?? '-' }}</li>
                <li><strong>📝 Catatan:</strong> {{ $meeting->note ?? '-' }}</li>
            </ul>
        </div>
    </div>

    <h4 class="mb-3">🌍 Travel Planner Terkait</h4>
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr class="text-center">
                    <th>Destinasi</th>
                    <th>Tanggal Kunjungan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($meeting->travels as $travel)
                    <tr>
                        <td>{{ $travel->destination }}</td>
                        <td class="text-center">{{ $travel->formatted_visit_date }}</td>
                        <td class="text-center">
                            <span class="badge rounded-pill {{ $travel->completed ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $travel->completed ? 'Selesai' : 'Belum Selesai' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('travels.complete-travel', $travel->id) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Travel ini sudah selesai?')" @if($travel->completed) disabled @endif>
                                    ✅ Complete
                                </button>
                            </form>
                            <form action="{{ route('travels.remove-from-meeting', $travel->id) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">
                                    🚫 Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">Belum ada rencana perjalanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h4 class="mb-3">🔗 Assign Travel Planner ke Meeting</h4>
    <form action="{{ route('travels.assign-to-meeting', $meeting->id) }}" method="POST" class="card p-4 shadow-sm mb-4">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label for="travel_id" class="form-label">Pilih Travel Planner</label>
                <select name="travel_id" id="travel_id" class="form-select" required>
                    <option value="">-- Pilih Travel Planner --</option>
                    @foreach ($availableTravels as $travel)
                        <option value="{{ $travel->id }}">{{ $travel->destination }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="visit_date" class="form-label">Tanggal Berkunjung</label>
                <input type="date" name="visit_date" id="visit_date" class="form-control" required>
            </div>
        </div>

        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-primary">➕ Assign Travel Planner</button>
        </div>
    </form>
</div>

@endsection
