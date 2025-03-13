@extends('layouts.app')

@section('title', 'Meeting Planner')

@section('content')
<div class="container">
    <h2>📅 Meeting Planner</h2>
    <a href="{{ route('meetings.create') }}" class="btn btn-primary mb-3">➕ Tambah Pertemuan</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">Siapa yang Berangkat</th>
                <th class="text-center">Tanggal</th>
                <th class="text-center">Lokasi</th>
                <th class="text-center">Persiapan</th>
                <th class="text-center">Catatan</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($meetings as $index => $meeting)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $meeting->user->name }}</td>
                <td><strong>{{ $meeting->meeting_date }}</strong></td>
                <td>{{ $meeting->location ?? 'Belum Ditentukan' }}</td>
                <td>
                    @if ($meeting->is_departure_transport_ready)
                        <span class="badge bg-success">Kendaraan Berangkat Siap</span>
                    @else
                        <span class="badge bg-danger">Kendaraan Berangkat Belum Siap</span>
                    @endif
                    @if ($meeting->is_return_transport_ready)
                        <span class="badge bg-success">Kendaraan Pulang Siap</span>
                    @else
                        <span class="badge bg-danger">Kendaraan Pulang Belum Siap</span>
                    @endif
                    @if ($meeting->is_rest_place_ready)
                        <span class="badge bg-success">Tempat Istirahat Siap</span>
                    @else
                        <span class="badge bg-danger">Tempat Istirahat Belum Siap</span>
                    @endif
                </td>
                <td>{{ $meeting->note ?? '-' }}</td>
                <td>
                    <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-warning btn-sm" @if($meeting->travelling_user_id != auth()->user()->id) disabled @endif>✏️ Edit</a>
                    <form action="{{ route('meetings.destroy', $meeting) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')" @if($meeting->travelling_user_id != auth()->user()->id) disabled @endif>🗑️ Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($meetings->isEmpty())
        <div class="alert alert-warning text-center">
            Belum ada pertemuan yang dijadwalkan.
        </div>
    @endif
</div>
{{-- pagination --}}
{{ $meetings->links() }}
@endsection
