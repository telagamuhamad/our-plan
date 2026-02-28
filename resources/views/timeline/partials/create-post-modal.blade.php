<!-- Create Post Modal -->
<div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('timeline.store') }}" method="POST" enctype="multipart/form-data" id="createPostForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createPostModalLabel">Buat Postingan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Post Type Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipe Postingan</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="post_type" id="typeText" value="text" checked>
                            <label class="btn btn-outline-primary" for="typeText">
                                📝 Teks
                            </label>

                            <input type="radio" class="btn-check" name="post_type" id="typePhoto" value="photo">
                            <label class="btn btn-outline-primary" for="typePhoto">
                                📷 Foto
                            </label>

                            <input type="radio" class="btn-check" name="post_type" id="typeVoice" value="voice_note">
                            <label class="btn btn-outline-primary" for="typeVoice">
                                🎤 Voice Note
                            </label>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="mb-3">
                        <label for="content" class="form-label fw-semibold">Konten</label>
                        <textarea class="form-control" name="content" id="postContent" rows="4"
                                  maxlength="5000" placeholder="Apa yang ingin kamu bagikan?"></textarea>
                        <div class="form-text">
                            <span id="contentCount">0</span>/5000 karakter
                        </div>
                    </div>

                    <!-- Attachment (for photo/voice note) -->
                    <div class="mb-3 d-none" id="attachmentContainer">
                        <label for="attachment" class="form-label fw-semibold">
                            <span id="attachmentLabel">Lampiran</span>
                            <span class="text-muted">(maks. <span id="maxSize">10MB</span>)</span>
                        </label>
                        <input type="file" class="form-control" name="attachment" id="attachment"
                               accept="" aria-describedby="attachmentHelp">
                        <div id="attachmentHelp" class="form-text">
                            File yang diupload akan disimpan secara aman.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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
    const contentInput = document.getElementById('postContent');
    const contentCount = document.getElementById('contentCount');

    const typeConfig = {
        text: {
            hasAttachment: false,
            label: 'Lampiran',
            maxSize: '10MB',
            accept: ''
        },
        photo: {
            hasAttachment: true,
            label: 'Foto',
            maxSize: '10MB',
            accept: 'image/*'
        },
        voice_note: {
            hasAttachment: true,
            label: 'Voice Note',
            maxSize: '5MB',
            accept: 'audio/*'
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
});
</script>
