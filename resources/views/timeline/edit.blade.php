@extends('layouts.app')

@section('title', 'Edit Postingan - Our Plan')

@section('content')
<div class="timeline-edit-page">
    <!-- Back Button -->
    <a href="{{ route('timeline.show', $post->id) }}" class="btn btn-link text-decoration-none mb-3 ps-0">
        ← Kembali ke Postingan
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Postingan</h4>
            <p class="text-muted mb-0 small">Edit postingan yang kamu buat</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Edit Post Form -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('timeline.update', $post->id) }}" method="POST" enctype="multipart/form-data" id="editPostForm">
                @csrf
                @method('PUT')

                <!-- Post Type Selection -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Tipe Postingan</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="post_type" id="typeText" value="text"
                               @if(old('post_type', $post->post_type) === 'text') checked @endif>
                        <label class="btn btn-outline-primary" for="typeText">
                            📝 Teks
                        </label>

                        <input type="radio" class="btn-check" name="post_type" id="typePhoto" value="photo"
                               @if(old('post_type', $post->post_type) === 'photo') checked @endif>
                        <label class="btn btn-outline-primary" for="typePhoto">
                            📷 Foto
                        </label>

                        <input type="radio" class="btn-check" name="post_type" id="typeVoice" value="voice_note"
                               @if(old('post_type', $post->post_type) === 'voice_note') checked @endif>
                        <label class="btn btn-outline-primary" for="typeVoice">
                            🎤 Voice Note
                        </label>
                    </div>
                    @error('post_type')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Content -->
                <div class="mb-4">
                    <label for="content" class="form-label fw-semibold">Konten</label>
                    <textarea class="form-control" name="content" id="postContent" rows="6"
                              maxlength="5000" placeholder="Apa yang ingin kamu bagikan?">{{ old('content', $post->content) }}</textarea>
                    <div class="form-text">
                        <span id="contentCount">{{ mb_strlen(old('content', $post->content ?? '')) }}</span>/5000 karakter
                    </div>
                    @error('content')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Current Attachment Preview -->
                @if($post->hasAttachment())
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Lampiran Saat Ini</label>
                        <div class="border rounded p-3 bg-light">
                            @if($post->post_type === 'photo')
                                <img src="{{ $post->attachment_url }}" alt="Current photo" class="img-fluid rounded mb-2" style="max-height: 300px;">
                            @elseif($post->post_type === 'voice_note')
                                <p class="mb-2">🎤 Voice Note</p>
                                <audio controls class="w-100" src="{{ $post->attachment_url }}">
                                    Browser Anda tidak mendukung audio.
                                </audio>
                            @endif
                            <div class="form-text text-muted">
                                Upload file baru untuk menggantinya, atau biarkan kosong untuk tetap menggunakan lampiran ini.
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Attachment (optional replacement) -->
                <div class="mb-4 @if(old('post_type', $post->post_type) === 'text') d-none @endif" id="attachmentContainer">
                    <label for="attachment" class="form-label fw-semibold">
                        <span id="attachmentLabel">Ganti Lampiran</span>
                        <span class="text-muted">(opsional, maks. <span id="maxSize">10MB</span>)</span>
                    </label>
                    <input type="file" class="form-control" name="attachment" id="attachment"
                           accept="" aria-describedby="attachmentHelp">
                    @if(old('post_type', $post->post_type) === 'photo')
                        <div class="form-text">Format: JPG, PNG, GIF, WEBP (maks. 10MB)</div>
                    @elseif(old('post_type', $post->post_type) === 'voice_note')
                        <div class="form-text">Format: MP3, WAV, M4A, OGG (maks. 5MB)</div>
                    @else
                        <div id="attachmentHelp" class="form-text">Biarkan kosong untuk tetap menggunakan lampiran yang ada.</div>
                    @endif
                    @error('attachment')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex gap-2">
                    <a href="{{ route('timeline.show', $post->id) }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <span class="btn-text">Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .btn-check:checked + .btn-outline-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeInputs = document.querySelectorAll('input[name="post_type"]');
    const attachmentContainer = document.getElementById('attachmentContainer');
    const attachmentInput = document.getElementById('attachment');
    const attachmentLabel = document.getElementById('attachmentLabel');
    const maxSizeSpan = document.getElementById('maxSize');
    const attachmentHelp = document.getElementById('attachmentHelp');
    const contentInput = document.getElementById('postContent');
    const contentCount = document.getElementById('contentCount');

    const typeConfig = {
        text: {
            hasAttachment: false,
            label: 'Ganti Lampiran',
            maxSize: '10MB',
            accept: '',
            help: 'Tidak perlu lampiran untuk postingan teks.'
        },
        photo: {
            hasAttachment: true,
            label: 'Ganti Foto',
            maxSize: '10MB',
            accept: 'image/*',
            help: 'Format: JPG, PNG, GIF, WEBP (maks. 10MB)'
        },
        voice_note: {
            hasAttachment: true,
            label: 'Ganti Voice Note',
            maxSize: '5MB',
            accept: 'audio/*',
            help: 'Format: MP3, WAV, M4A, OGG (maks. 5MB)'
        }
    };

    typeInputs.forEach(input => {
        input.addEventListener('change', function() {
            const config = typeConfig[this.value];

            if (config.hasAttachment) {
                attachmentContainer.classList.remove('d-none');
                attachmentLabel.textContent = config.label;
                maxSizeSpan.textContent = config.maxSize;
                attachmentInput.setAttribute('accept', config.accept);
                attachmentInput.removeAttribute('required');
                if (attachmentHelp) {
                    attachmentHelp.textContent = config.help;
                }
            } else {
                attachmentContainer.classList.add('d-none');
                attachmentInput.removeAttribute('required');
                attachmentInput.value = '';
            }
        });
    });

    contentInput.addEventListener('input', function() {
        contentCount.textContent = this.value.length;
    });

    // Form submission with loading state
    const editPostForm = document.getElementById('editPostForm');
    if (editPostForm) {
        editPostForm.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            const spinner = btn.querySelector('.spinner-border');
            const btnText = btn.querySelector('.btn-text');

            spinner.classList.remove('d-none');
            btnText.textContent = 'Menyimpan...';
            btn.disabled = true;
        });
    }
});
</script>
@endsection
