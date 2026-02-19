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

    {{-- Countdown Widget --}}
    @if($countdown['has_upcoming'])
    <div class="card shadow-sm mb-4 border-primary">
        <div class="card-body text-center">
            <h5 class="card-title text-primary mb-3">⏰ Countdown ke Meeting Berikutnya</h5>
            <h4 class="fw-bold mb-2">{{ $countdown['meeting']->location ?? 'Tanpa Lokasi' }}</h4>
            <p class="text-muted mb-3">{{ $countdown['meeting']->formatted_start_date }} – {{ $countdown['meeting']->formatted_end_date }}</p>

            <div class="countdown-timer d-flex justify-content-center gap-3 mb-3" id="countdown-timer" data-seconds="{{ $countdown['countdown']['total_seconds'] }}">
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-days">{{ $countdown['countdown']['days'] }}</div>
                    <div class="countdown-label">Hari</div>
                </div>
                <div class="countdown-separator">:</div>
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-hours">{{ $countdown['countdown']['hours'] }}</div>
                    <div class="countdown-label">Jam</div>
                </div>
                <div class="countdown-separator">:</div>
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-minutes">{{ $countdown['countdown']['minutes'] }}</div>
                    <div class="countdown-label">Menit</div>
                </div>
                <div class="countdown-separator">:</div>
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-seconds">{{ $countdown['countdown']['seconds'] }}</div>
                    <div class="countdown-label">Detik</div>
                </div>
            </div>

            <p class="text-muted small mb-0">{{ $countdown['message'] }}</p>
        </div>
    </div>

    <style>
        .countdown-timer {
            font-size: 1.5rem;
        }
        .countdown-item {
            text-align: center;
            min-width: 60px;
        }
        .countdown-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .countdown-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
        }
        .countdown-separator {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0d6efd;
            padding-top: 0.5rem;
        }
    </style>

    <script>
        // Real-time countdown update
        document.addEventListener('DOMContentLoaded', function() {
            const timerElement = document.getElementById('countdown-timer');
            if (!timerElement) return;

            let totalSeconds = parseInt(timerElement.dataset.seconds);

            function updateCountdown() {
                if (totalSeconds <= 0) {
                    // Reload page to get updated data
                    setTimeout(() => location.reload(), 1000);
                    return;
                }

                totalSeconds--;

                const days = Math.floor(totalSeconds / (24 * 60 * 60));
                const hours = Math.floor((totalSeconds % (24 * 60 * 60)) / (60 * 60));
                const minutes = Math.floor((totalSeconds % (60 * 60)) / 60);
                const seconds = totalSeconds % 60;

                document.getElementById('countdown-days').textContent = String(days).padStart(2, '0');
                document.getElementById('countdown-hours').textContent = String(hours).padStart(2, '0');
                document.getElementById('countdown-minutes').textContent = String(minutes).padStart(2, '0');
                document.getElementById('countdown-seconds').textContent = String(seconds).padStart(2, '0');
            }

            setInterval(updateCountdown, 1000);
        });
    </script>
    @else
    <div class="alert alert-info mb-4">
        <strong>ℹ️ {{ $countdown['message'] }}</strong><br>
        <small>Buat meeting baru untuk mulai menghitung mundur! ❤️</small>
    </div>
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
