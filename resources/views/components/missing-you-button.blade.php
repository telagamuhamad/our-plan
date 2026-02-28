@props([
    'size' => 'md', // sm, md, lg
    'showText' => true,
    'class' => '',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'btn-sm',
        'lg' => 'btn-lg',
        default => '',
    };

    $buttonId = 'missingYouBtn_' . uniqid();
@endphp

<div class="missing-you-button-wrapper {{ $class }}">
    <button
        id="{{ $buttonId }}"
        type="button"
        class="btn btn-danger {{ $sizeClasses }} hover-shadow position-relative missing-you-btn"
        data-bs-toggle="modal"
        data-bs-target="#missingYouQuickModal_{{ $buttonId }}"
    >
        @if($showText)
            <i class="bi bi-heart-fill"></i> Missing You
        @else
            <i class="bi bi-heart-fill"></i>
        @endif
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning" id="quotaBadge_{{ $buttonId }}" style="display: none;">
            <span id="quotaCount_{{ $buttonId }}">?</span>
        </span>
    </button>

    <!-- Quick Modal -->
    <div class="modal fade" id="missingYouQuickModal_{{ $buttonId }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('missing-you.send') }}" method="POST" class="missing-you-form">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">💕 Kirim Missing You</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Status message -->
                        <div class="alert alert-info small py-2 mb-3">
                            <i class="bi bi-info-circle"></i>
                            <span id="statusMessage_{{ $buttonId }}">Memeriksa kuota...</span>
                        </div>

                        <!-- Message input -->
                        <div class="mb-3">
                            <label class="form-label small">
                                <a href="#" class="text-decoration-none quick-template-link" data-button-id="{{ $buttonId }}">
                                    <i class="bi bi-chat-heart"></i> Pilih template
                                </a>
                            </label>
                            <textarea
                                name="message"
                                class="form-control message-input"
                                rows="3"
                                maxlength="200"
                                placeholder="Kosongkan untuk pesan random..."></textarea>
                            <div class="form-text">
                                <span class="char-count">0</span>/200 karakter
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger send-btn">
                            <i class="bi bi-send-fill"></i> Kirim!
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            const buttonId = '{{ $buttonId }}';
            const form = document.querySelector('#missingYouQuickModal_' + buttonId + ' form');
            const messageInput = form.querySelector('.message-input');
            const charCount = form.querySelector('.char-count');
            const statusMessage = document.getElementById('statusMessage_' + buttonId);
            const sendBtn = form.querySelector('.send-btn');
            const quotaBadge = document.getElementById('quotaBadge_' + buttonId);
            const quotaCount = document.getElementById('quotaCount_' + buttonId);

            // Character counter
            messageInput.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });

            // Check quota when modal opens
            const modalElement = document.getElementById('missingYouQuickModal_' + buttonId);
            modalElement.addEventListener('show.bs.modal', function() {
                fetch('{{ route('missing-you.status') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const { can_send, remaining_quota, seconds_remaining } = data.data;

                            if (can_send) {
                                statusMessage.innerHTML = '<i class="bi bi-check-circle text-success"></i> Siap kirim! Kuota: ' + remaining_quota + '/3';
                                sendBtn.disabled = false;
                                quotaBadge.style.display = 'none';
                            } else {
                                const minutes = Math.ceil(seconds_remaining / 60);
                                statusMessage.innerHTML = '<i class="bi bi-clock-history text-warning"></i> Tunggu ' + minutes + ' menit lagi';
                                sendBtn.disabled = true;
                                quotaBadge.style.display = 'none';
                            }

                            quotaCount.textContent = remaining_quota;
                        }
                    })
                    .catch(error => {
                        statusMessage.innerHTML = '<i class="bi bi-exclamation-triangle text-danger"></i> Gagal memeriksa kuota';
                        console.error('Error:', error);
                    });
            });

            // Form submission with AJAX
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                sendBtn.disabled = true;
                sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Check if it's a redirect (traditional form submission)
                    if (!data.success) {
                        statusMessage.innerHTML = '<i class="bi bi-exclamation-triangle text-danger"></i> ' + data.message;
                        sendBtn.disabled = false;
                        sendBtn.innerHTML = '<i class="bi bi-send-fill"></i> Kirim!';
                    } else {
                        // Success - reload page or redirect
                        window.location.href = '{{ route('missing-you.index') }}';
                    }
                })
                .catch(error => {
                    // If not JSON response, it might be a redirect - let it proceed
                    form.submit();
                });
            });

            // Quick template selection
            const quickLink = form.querySelector('.quick-template-link');
            quickLink.addEventListener('click', function(e) {
                e.preventDefault();

                fetch('{{ route('missing-you.templates') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.templates) {
                            const templates = data.data.templates;
                            const keys = Object.keys(templates);
                            const randomKey = keys[Math.floor(Math.random() * keys.length)];
                            const userName = '{{ Auth::user()->name }}';
                            const message = templates[randomKey].replace(/{name}/g, userName);

                            messageInput.value = message;
                            charCount.textContent = message.length;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        })();
    </script>
    @endpush

    <style>
        .missing-you-btn {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .missing-you-btn:hover {
            animation: none;
        }
    </style>
</div>
