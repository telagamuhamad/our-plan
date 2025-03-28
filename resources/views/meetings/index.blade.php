@extends('layouts.app')

@section('title', 'Meeting Planner')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-semibold">📅 Meeting Planner</h2>
        <a href="{{ route('meetings.create') }}" class="btn btn-primary">➕ Tambah Pertemuan</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered align-middle shadow-sm bg-white rounded">
            <thead class="table-light">
                <tr class="text-center">
                    <th>#</th>
                    <th>Siapa yang Berangkat</th>
                    <th>Tanggal</th>
                    <th>Lokasi</th>
                    <th>Persiapan</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($meetings as $index => $meeting)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $meeting->user->name }}</td>
                        <td class="fw-semibold">{{ $meeting->formatted_start_date }} – {{ $meeting->formatted_end_date }}</td>
                        <td>{{ $meeting->location ?? 'Belum Ditentukan' }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge rounded-pill {{ $meeting->is_departure_transport_ready ? 'bg-success' : 'bg-danger' }}">
                                    Kendaraan Berangkat {{ $meeting->is_departure_transport_ready ? 'Siap' : 'Belum Siap' }}
                                </span>
                                <span class="badge rounded-pill {{ $meeting->is_return_transport_ready ? 'bg-success' : 'bg-danger' }}">
                                    Kendaraan Pulang {{ $meeting->is_return_transport_ready ? 'Siap' : 'Belum Siap' }}
                                </span>
                                <span class="badge rounded-pill {{ $meeting->is_rest_place_ready ? 'bg-success' : 'bg-danger' }}">
                                    Tempat Istirahat {{ $meeting->is_rest_place_ready ? 'Siap' : 'Belum Siap' }}
                                </span>
                            </div>
                        </td>
                        <td>{{ $meeting->note ?? '-' }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                <a href="{{ route('meetings.show', $meeting->id) }}" class="btn btn-info btn-sm">📝 Detail</a>
                                <a href="{{ route('meetings.edit', $meeting) }}"
                                   class="btn btn-warning btn-sm"
                                   @if($meeting->travelling_user_id != auth()->id()) disabled @endif>
                                   ✏️ Edit
                                </a>
                                <form action="{{ route('meetings.destroy', $meeting) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        @if($meeting->travelling_user_id != auth()->id()) disabled @endif>
                                        🗑️ Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada pertemuan yang dijadwalkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $meetings->links() }}
    </div>
</div>

{{-- pagination --}}
{{ $meetings->links() }}
@endsection
