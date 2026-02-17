@extends('layouts.app')

@section('title', 'Missing You - Our Plan')

@section('content')
<div class="missing-you-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">💕 Missing You</h4>
            <p class="text-muted mb-0 small">Kirim kabar rindu ke pasanganmu</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Quick Action Card -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Kirim Missing You</h5>

                    <!-- Quota Indicator -->
                    <div class="mb-3">
                        <p class="text-muted small mb-1">
                            Sisa kuota hari ini: <strong>{{ $remainingQuota }}</strong>/3
                        </p>
                        @if($remainingQuota === 0)
                            <div class="alert alert-warning small py-2">
                                <i class="bi bi-clock-history"></i>
                                @if($secondsRemaining)
                                    Tunggu {{ ceil($secondsRemaining / 60) }} menit lagi
                                @else
                                    Kuota habis untuk sementara
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Quick Send Button -->
                    <form action="{{ route('missing-you.send') }}" method="POST" id="missingYouForm">
                        @csrf

                        @if($remainingQuota > 0)
                            <!-- Message Templates (Optional) -->
                            <div class="mb-3">
                                <label class="form-label small">
                            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#templatesModal">
                                <i class="bi bi-chat-heart"></i> Pilih template pesan
                            </a>
                                </label>
                                <textarea
                                    name="message"
                                    class="form-control"
                                    rows="3"
                                    maxlength="200"
                                    placeholder="Kosongkan untuk pesan random yang cute..."></textarea>
                                <div class="form-text">
                                    <span id="charCount">0</span>/200 karakter
                                </div>
                            </div>

                            <button type="submit" class="btn btn-danger w-100 btn-lg hover-shadow">
                                <i class="bi bi-heart-fill"></i> Kirim Missing You!
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary w-100" disabled>
                                <i class="bi bi-hourglass-split"></i> Tunggu Sebentar...
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- History Card -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">📜 Riwayat Missing You</h5>

                    @if($history->count() > 0)
                        <div class="timeline">
                            @foreach($history as $item)
                                <div class="timeline-item mb-3 pb-3 {{ $loop->last ? '' : 'border-bottom' }}">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            @if($item->actor)
                                                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center"
                                                     style="width: 40px; height: 40px; font-size: 1.2rem;">
                                                    💕
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        @if($item->actor)
                                                            {{ $item->actor->name }}
                                                        @else
                                                            Seseorang
                                                        @endif
                                                        mengirim Missing You!
                                                    </h6>
                                                    <p class="mb-1">{{ e($item->message) }}</p>
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock"></i>
                                                        {{ $item->created_at?->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="display-1 mb-3">💭</div>
                            <p class="text-muted">Belum ada "Missing You" yang terkirim</p>
                            <p class="text-muted small">Kirim sekarang untuk bikin pasangan tersenyum!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Templates Modal -->
<div class="modal fade" id="templatesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">💬 Pilih Template Pesan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2" id="templateButtons">
                    <button type="button" class="btn btn-outline text-start template-btn"
                            data-template="default">
                        🥺 <strong>{name} merindukanmu!</strong>
                    </button>
                    <button type="button" class="btn btn-outline text-start template-btn"
                            data-template="hug">
                        🤗 <strong>{name} mengirimkan peluk virtual!</strong>
                    </button>
                    <button type="button" class="btn btn-outline text-start template-btn"
                            data-template="thinking">
                        🥰 <strong>{name} lagi mikirin kamu sekarang!</strong>
                    </button>
                    <button type="button" class="btn btn-outline text-start template-btn"
                            data-template="love">
                        ❤️ <strong>Dari {name}: Aku kangen kamu!</strong>
                    </button>
                    <button type="button" class="btn btn-outline text-start template-btn"
                            data-template="hug_now">
                        🫂 <strong>{name} mau peluk kamu sekarang!</strong>
                    </button>
                    <button type="button" class="btn btn-outline text-start template-btn"
                            data-template="hayang">
                        👋 <strong>Hayang ah (hayang jumpa) - {name}</strong>
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    // Character counter
    const messageTextarea = document.querySelector('textarea[name="message"]');
    const charCount = document.getElementById('charCount');

    if (messageTextarea && charCount) {
        messageTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Template selection
    const templateButtons = document.querySelectorAll('.template-btn');
    templateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const template = this.getAttribute('data-template');

            // Fetch templates from API
            fetch('{{ route('missing-you.templates') }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.templates) {
                        const selectedTemplate = data.data.templates[template] || data.data.templates['default'];
                        const userName = '{{ Auth::user()->name }}';
                        const message = selectedTemplate.replace(/{name}/g, userName);

                        messageTextarea.value = message;
                        charCount.textContent = message.length;

                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('templatesModal'));
                        modal.hide();
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });

    // Auto-refresh quota every 30 seconds
    setInterval(() => {
        fetch('{{ route('missing-you.status') }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Quota updated:', data.data.remaining_quota);
                    // Optionally update UI without full page reload
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
    }, 30000);
</script>
@endpush

<style>
    .timeline-item {
        transition: background-color 0.2s;
    }

    .timeline-item:hover {
        background-color: rgba(220, 53, 69, 0.05);
        border-radius: 8px;
        padding: 8px;
        margin: -8px;
    }

    .timeline-item:last-child {
        border-bottom: none !important;
    }

    .template-btn {
        padding: 12px 16px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        transition: all 0.2s;
    }

    .template-btn:hover {
        background-color: #fff5f5;
        border-color: #dc3545;
        transform: translateX(4px);
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
    }
</style>
