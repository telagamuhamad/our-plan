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

    {{-- Countdown Widget --}}
    @php
        $countdown = $meeting->countdown;
    @endphp
    <div class="card shadow-sm mb-4 @if($countdown['is_in_progress']) border-success @elseif(!$countdown['is_passed']) border-primary @endif">
        <div class="card-body text-center">
            @if($countdown['is_passed'])
                <h5 class="card-title text-secondary mb-2">✅ Meeting Sudah Selesai</h5>
            @elseif($countdown['is_in_progress'])
                <h5 class="card-title text-success mb-2">🎉 Meeting Sedang Berlangsung!</h5>
            @else
                <h5 class="card-title text-primary mb-2">⏰ Countdown Menuju Meeting</h5>
            @endif

            <h4 class="fw-bold mb-2">{{ $meeting->location ?? 'Tanpa Lokasi' }}</h4>
            <p class="text-muted mb-3">{{ $meeting->formatted_start_date }} – {{ $meeting->formatted_end_date }}</p>

            @if(!$countdown['is_passed'] && !$countdown['is_in_progress'])
            <div class="countdown-timer d-flex justify-content-center gap-3 mb-3" id="countdown-timer" data-seconds="{{ $countdown['total_seconds'] }}">
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-days">{{ $countdown['days'] }}</div>
                    <div class="countdown-label">Hari</div>
                </div>
                <div class="countdown-separator">:</div>
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-hours">{{ $countdown['hours'] }}</div>
                    <div class="countdown-label">Jam</div>
                </div>
                <div class="countdown-separator">:</div>
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-minutes">{{ $countdown['minutes'] }}</div>
                    <div class="countdown-label">Menit</div>
                </div>
                <div class="countdown-separator">:</div>
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-seconds">{{ $countdown['seconds'] }}</div>
                    <div class="countdown-label">Detik</div>
                </div>
            </div>
            @endif

            <p class="mb-0">
                @if($countdown['is_passed'])
                    <span class="badge bg-secondary">Selesai</span>
                @elseif($countdown['is_in_progress'])
                    <span class="badge bg-success">Berlangsung</span>
                @else
                    <span class="badge bg-primary">{{ $countdown['message'] }}</span>
                @endif
            </p>
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

    {{-- Meeting Feedback Section --}}
    <h4 class="mb-3">⭐ Feedback Meeting</h4>

    @php
        $feedbacks = $meeting->feedbacks()->with('user')->get();
        $userFeedback = $meeting->feedbacks()->where('user_id', auth()->id())->first();
        $avgRating = $feedbacks->avg('rating') ? round($feedbacks->avg('rating'), 1) : 0;
    @endphp

    {{-- Feedback Summary --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Rating Rata-rata</h5>
                    <div class="d-flex align-items-center">
                        <span class="display-6 fw-bold me-2">{{ $avgRating }}</span>
                        <span class="text-warning fs-4">{{ str_repeat('★', round($avgRating)) }}{{ str_repeat('☆', 5 - round($avgRating)) }}</span>
                        <span class="text-muted ms-2">({{ $feedbacks->count() }} feedback)</span>
                    </div>
                </div>
                @if($countdown['is_passed'])
                    @if(!$userFeedback)
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                            💬 Berikan Feedback
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                            ✏️ Edit Feedback Anda
                        </button>
                    @endif
                @else
                    <span class="text-muted small">Feedback dapat diberikan setelah meeting selesai</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Feedback List --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Daftar Feedback</h5>
            @forelse($feedbacks as $feedback)
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                            @if($feedback->user->avatar_url)
                                <img src="{{ $feedback->user->avatar_url }}" alt="{{ $feedback->user->name }}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; font-size: 1rem; color: white;">
                                    {{ substr($feedback->user->name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $feedback->user->name }}</h6>
                                <small class="text-muted">{{ $feedback->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="text-warning fs-5">{{ $feedback->stars }}</div>
                    </div>
                    @if($feedback->comment)
                        <p class="mt-2 mb-0">{{ $feedback->comment }}</p>
                    @endif
                </div>
            @empty
                <p class="text-muted text-center py-3">Belum ada feedback.</p>
            @endforelse
        </div>
    </div>

    <style>
        .star-label {
            color: #ddd;
            transition: color 0.2s;
        }
        .star-label:hover,
        .star-label:hover ~ .star-label {
            color: #ffc107;
        }
        input:checked ~ .star-label {
            color: #ffc107;
        }
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 0.25rem;
        }
    </style>
</div>

@endsection

@push('modals')
{{-- Feedback Modal - Placed outside glass-container using @stack('modals') --}}
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ $userFeedback ? route('meetings.feedback.update', $userFeedback->id) : route('meetings.feedback.store', $meeting->id) }}" method="POST">
                @csrf
                @if($userFeedback) @method('PUT') @endif

                <div class="modal-header">
                    <h5 class="modal-title" id="feedbackModalLabel">{{ $userFeedback ? 'Edit' : 'Berikan' }} Feedback Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rating" class="form-label">Rating <span class="text-danger">*</span></label>
                        <select name="rating" id="rating" class="form-select" required>
                            <option value="">-- Pilih Rating --</option>
                            <option value="5" {{ $userFeedback && $userFeedback->rating == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ 5 - Sangat Puas</option>
                            <option value="4" {{ $userFeedback && $userFeedback->rating == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ 4 - Puas</option>
                            <option value="3" {{ $userFeedback && $userFeedback->rating == 3 ? 'selected' : '' }}>⭐⭐⭐ 3 - Biasa</option>
                            <option value="2" {{ $userFeedback && $userFeedback->rating == 2 ? 'selected' : '' }}>⭐⭐ 2 - Kurang</option>
                            <option value="1" {{ $userFeedback && $userFeedback->rating == 1 ? 'selected' : '' }}>⭐ 1 - Sangat Buruk</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">Komentar / Pesan</label>
                        <textarea name="comment" id="comment" class="form-control" rows="4" placeholder="Bagikan pengalaman Anda selama meeting...">{{ $userFeedback->comment ?? '' }}</textarea>
                        <small class="text-muted">Maksimal 1000 karakter</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">{{ $userFeedback ? 'Update' : 'Kirim' }} Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
