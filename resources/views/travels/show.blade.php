@extends('layouts.app')

@section('title', 'Detail Rencana Perjalanan')

@section('content')
@php
    $photos = $travel->photos;
@endphp

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-semibold">🌍 {{ $travel->destination }}</h2>
        <a href="{{ route('travels.index') }}" class="btn btn-secondary">⬅️ Kembali</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Travel Info Card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="card-title mb-3">📍 Informasi Travel</h5>
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

                    <p><strong>📷 Jumlah Foto:</strong> {{ $photos->count() }} foto</p>
                </div>

                <div class="col-md-4 text-center">
                    {{-- Primary Photo or Placeholder --}}
                    @if($photos->isNotEmpty())
                        @php $primaryPhoto = $photos->first(); @endphp
                        <img src="{{ $primaryPhoto->url }}"
                             alt="{{ $travel->destination }}"
                             class="img-fluid rounded mb-2"
                             style="max-height: 200px; object-fit: cover; width: 100%;">
                        @if($primaryPhoto->caption)
                            <p class="text-muted small mb-0">"{{ $primaryPhoto->caption }}"</p>
                        @endif
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-2"
                             style="height: 200px;">
                            <div class="text-center text-muted">
                                <div style="font-size: 3rem;">📷</div>
                                <small>Belum ada foto</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Photo Gallery --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0">📸 Galeri Foto ({{ $photos->count() }})</h6>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                ➕ Upload Foto
            </button>
        </div>
        <div class="card-body">
            @if($photos->isNotEmpty())
                <div class="row g-3" id="photoGallery">
                    @foreach($photos as $photo)
                        <div class="col-md-4 col-sm-6" data-photo-id="{{ $photo->id }}">
                            <div class="card h-100">
                                <div class="card-img-top position-relative" style="height: 200px;">
                                    <img src="{{ $photo->url }}"
                                         alt="Foto travel"
                                         class="w-100 h-100 object-fit-cover"
                                         style="cursor: pointer;"
                                         onclick="openPhotoModal({{ $photo->id }}, '{{ $photo->url }}', '{{ $photo->caption ?? '' }}', '{{ $photo->uploader->name ?? '' }}')">
                                    <button type="button"
                                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                            onclick="deletePhoto({{ $photo->id }})">
                                        🗑️
                                    </button>
                                </div>
                                @if($photo->caption)
                                <div class="card-body p-2">
                                    <p class="card-text small text-muted mb-0">{{ $photo->caption }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <div style="font-size: 4rem;">📷</div>
                    <p class="text-muted">Belum ada foto. Upload foto pertama kamu!</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Travel Journals --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0">📖 Journal ({{ $travel->journals->count() }})</h6>
            <a href="{{ route('journals.create') }}?travel_id={{ $travel->id }}" class="btn btn-primary btn-sm">➕ Tulis Journal</a>
        </div>
        <div class="card-body">
            @if($travel->journals->isNotEmpty())
                <div class="list-group list-group-flush">
                    @foreach($travel->journals->take(3) as $journal)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">{{ $journal->title }}</h6>
                                    <small class="text-muted">{{ $journal->formatted_journal_date }} • {{ $journal->mood_emoji }}</small>
                                </div>
                                <a href="{{ route('journals.show', $journal->id) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($travel->journals->count() > 3)
                    <div class="text-center mt-2">
                        <a href="{{ route('travels.journals.index', $travel->id) }}" class="btn btn-sm btn-link">Lihat semua ({{ $travel->journals->count() }}) →</a>
                    </div>
                @endif
            @else
                <p class="text-muted text-center py-3 mb-0">Belum ada journal. Tulis pengalaman kalian!</p>
                <div class="text-center">
                    <a href="{{ route('journals.create') }}?travel_id={{ $travel->id }}" class="btn btn-primary btn-sm">➕ Tulis Journal</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex gap-2">
        <a href="{{ route('travels.edit', $travel->id) }}" class="btn btn-warning">✏️ Edit Travel</a>
        @if($travel->meeting)
            <form action="{{ route('travels.remove-from-meeting', $travel->id) }}" method="POST" class="d-inline">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-outline-secondary" onclick="return confirm('Hapus hubungan dengan meeting?')">🔗 Lepas dari Meeting</button>
            </form>
        @endif
        @if(!$travel->completed)
            <form action="{{ route('travels.complete-travel', $travel->id) }}" method="POST" class="d-inline">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-success">✅ Tandai Selesai</button>
            </form>
        @endif
        <form action="{{ route('travels.destroy', $travel->id) }}" method="POST" class="d-inline">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus travel ini?')">🗑️ Hapus</button>
        </form>
    </div>
</div>

{{-- Upload Photo Modal --}}
@push('modals')
<div class="modal fade" id="uploadPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('travels.photos.store', $travel->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="photo" class="form-label">Foto <span class="text-danger">*</span></label>
                        <input type="file" name="photo" id="photo" class="form-control" accept="image/*" required>
                        <small class="text-muted">Maksimal 5MB. Format: JPEG, PNG, GIF, WebP</small>
                    </div>
                    <div class="mb-3">
                        <label for="caption" class="form-label">Caption (opsional)</label>
                        <input type="text" name="caption" id="caption" class="form-control" placeholder="Tulis caption untuk foto...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Photo View Modal --}}
<div class="modal fade" id="viewPhotoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPhotoCaption">Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="viewPhotoImage" src="" alt="Foto" class="img-fluid" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto" id="viewPhotoUploader"></small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
function openPhotoModal(photoId, url, caption, uploader) {
    document.getElementById('viewPhotoImage').src = url;
    document.getElementById('viewPhotoCaption').textContent = caption || 'Foto Travel';
    document.getElementById('viewPhotoUploader').textContent = 'Uploaded by ' + uploader;

    const modal = new bootstrap.Modal(document.getElementById('viewPhotoModal'));
    modal.show();
}

function deletePhoto(photoId) {
    if (confirm('Yakin ingin menghapus foto ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('travels.photos.destroy', ':id') }}'.replace(':id', photoId);
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush

<style>
.card-img-top {
    background-color: #f8f9fa;
}
.card-img-top img {
    transition: transform 0.2s;
}
.card-img-top:hover img {
    transform: scale(1.05);
}
</style>

@endsection
