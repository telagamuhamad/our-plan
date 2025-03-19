@extends('layouts.app')

@section('title', 'Detail Meeting')

@section('content')
<div class="container">
    <h2>📅 Detail Meeting: {{ $meeting->location }} ({{ $meeting->formatted_start_date }} - {{ $meeting->formatted_end_date }})</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5>🧑 Siapa yang Berangkat:</h5>
            <p>{{ $meeting->user->name }}</p>

            <h5>🗓️ Rentang Tanggal:</h5>
            <p>{{ $meeting->formatted_start_date }} - {{ $meeting->formatted_end_date }}</p>

            <h5>📍 Lokasi:</h5>
            <p>{{ $meeting->location }}</p>

            <h5>📝 Catatan:</h5>
            <p>{{ $meeting->note ?? 'Tidak ada catatan' }}</p>
        </div>
    </div>

    <h3>🌍 Travel Planner</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Destinasi</th>
                <th>Tanggal Kunjungan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        @if (empty($meeting->travels))
            <tbody>
                <tr>
                    <td colspan="4">Belum ada rencana perjalanan yang dibuat.</td>
                </tr>
            </tbody>
        @else
            <tbody>
                @foreach ($meeting->travels as $travel)
                <tr>
                    <td>{{ $travel->destination}}</td>
                    <td>{{ $travel->formatted_visit_date}}</td>
                    <td>
                        <span class="badge {{ $travel->completed ? 'bg-success' : 'bg-warning' }}">
                            {{ $travel->completed ? 'Selesai' : 'Belum Selesai' }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('travels.complete-travel', $travel->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Travel ini sudah selesai?')" @if($travel->completed) disabled @endif>✅ Complete</button>
                        </form>
                        <form action="{{ route('travels.remove-from-meeting', $travel->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">🚫 Hapus dari Meeting</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        @endif
    </table>

    <h3>🔗 Assign Travel Planner ke Meeting</h3>
    <form action="{{ route('travels.assign-to-meeting', $meeting->id) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="travel_id" class="form-label">Pilih Travel Planner:</label>
            <select name="travel_id" id="travel_id" class="form-control">
                <option value="">-- Pilih Travel Planner --</option>
                @foreach ($availableTravels as $travel)
                    <option value="{{ $travel->id }}">{{ $travel->destination }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="visit_date" class="form-label">Tanggal Berkunjung:</label>
            <input type="date" name="visit_date" id="visit_date" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">➕ Assign Travel Planner</button>
    </form>

    <a href="{{ route('meetings.index') }}" class="btn btn-secondary mt-3">⬅️ Kembali ke Meeting Planner</a>
</div>
@endsection
