@extends('layouts.app')

@section('title', 'Buat Postingan - Our Plan')

@section('content')
<div class="timeline-create-page">
    <!-- Back Button -->
    <a href="{{ route('timeline.index') }}" class="btn btn-link text-decoration-none mb-3 ps-0">
        ← Kembali ke Timeline
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Buat Postingan Baru</h4>
            <p class="text-muted mb-0 small">Bagikan momen spesial dengan pasanganmu</p>
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

    <!-- Create Post Form -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('timeline.store') }}" method="POST" enctype="multipart/form-data" id="createPostForm">
                @csrf

                <!-- Post Type Selection -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Tipe Postingan</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="post_type" id="typeText" value="text"
                               @if(old('post_type', 'text') === 'text') checked @endif>
                        <label class="btn btn-outline-primary" for="typeText">
                            📝 Teks
                        </label>

                        <input type="radio" class="btn-check" name="post_type" id="typePhoto" value="photo"
                               @if(old('post_type') === 'photo') checked @endif>
                        <label class="btn btn-outline-primary" for="typePhoto">
                            📷 Foto
                        </label>

                        <input type="radio" class="btn-check" name="post_type" id="typeVoice" value="voice_note"
                               @if(old('post_type') === 'voice_note') checked @endif>
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
                              maxlength="5000" placeholder="Apa yang ingin kamu bagikan?">{{ old('content') }}</textarea>
                    <div class="form-text">
                        <span id="contentCount">{{ mb_strlen(old('content')) }}</span>/5000 karakter
                    </div>
                    @error('content')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Attachment (for photo/voice note) -->
                <div class="mb-4 @if(old('post_type', 'text') === 'text') d-none @endif" id="attachmentContainer">
                    <label for="attachment" class="form-label fw-semibold">
                        <span id="attachmentLabel">Lampiran</span>
                        <span class="text-muted">(maks. <span id="maxSize">10MB</span>)</span>
                    </label>
                    <input type="file" class="form-control" name="attachment" id="attachment"
                           accept="" aria-describedby="attachmentHelp"
                           @if(old('post_type', 'text') !== 'text') required @endif>
                    @if(old('post_type') === 'photo')
                        <div class="form-text">Format: JPG, PNG, GIF, WEBP (maks. 10MB)</div>
                    @elseif(old('post_type') === 'voice_note')
                        <div class="form-text">Format: MP3, WAV, M4A, OGG (maks. 5MB)</div>
                    @else
                        <div id="attachmentHelp" class="form-text">File yang diupload akan disimpan secara aman.</div>
                    @endif
                    @error('attachment')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Preview attachment (if any) -->
                @if(old('post_type') === 'photo' && old('attachment_path'))
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Preview Foto</label>
                        <img src="{{ asset('storage/' . old('attachment_path')) }}" alt="Preview" class="img-fluid rounded">
                    </div>
                @endif

                <!-- Submit Buttons -->
                <div class="d-flex gap-2">
                    <a href="{{ route('timeline.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <span class="btn-text">Posting</span>
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
            label: 'Lampiran',
            maxSize: '10MB',
            accept: '',
            help: 'File yang diupload akan disimpan secara aman.'
        },
        photo: {
            hasAttachment: true,
            label: 'Foto',
            maxSize: '10MB',
            accept: 'image/*',
            help: 'Format: JPG, PNG, GIF, WEBP (maks. 10MB)'
        },
        voice_note: {
            hasAttachment: true,
            label: 'Voice Note',
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
                attachmentInput.setAttribute('required', 'required');
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
    const createPostForm = document.getElementById('createPostForm');
    if (createPostForm) {
        createPostForm.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            const spinner = btn.querySelector('.spinner-border');
            const btnText = btn.querySelector('.btn-text');

            spinner.classList.remove('d-none');
            btnText.textContent = 'Memposting...';
            btn.disabled = true;
        });
    }
});
</script>
@endsection
