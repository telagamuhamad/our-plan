@extends('layouts.app')

@php
    use App\Models\DailyQuestion;
    $myAnswerMode = $todayQuestion->getAnswerModeForUser(Auth::user());
    $canAnswerViaApp = $todayQuestion->canUserAnswerViaApp(Auth::user());
    $prefersCall = $todayQuestion->doesUserPreferCall(Auth::user());
@endphp

@section('title', 'Question of the Day - Our Plan')

@section('content')
<div class="questions-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">📝 Question of the Day</h4>
            <p class="text-muted mb-0 small">Jaga percakapan tetap fresh dengan pertanyaan harian</p>
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
        <!-- Today's Question -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">Pertanyaan Hari Ini</h5>
                        @if($todayQuestion->category)
                            <span class="badge bg-primary">
                                {{ DailyQuestion::getCategories()[$todayQuestion->category] ?? $todayQuestion->category }}
                            </span>
                        @endif
                    </div>

                    <p class="text-muted small">{{ $todayQuestion->formatted_date }}</p>

                    <!-- Question Display -->
                    <div class="question-box bg-light p-4 rounded-3 mb-4">
                        <h4 class="mb-0 text-center">{{ e($todayQuestion->question) }}</h4>
                    </div>

                    <!-- Answer Mode Selector -->
                    <div class="answer-mode-selector mb-4 p-3 bg-light rounded-3">
                        <h6 class="mb-3 small fw-semibold">
                            <i class="bi bi-gear-fill"></i> Mau jawab bagaimana?
                        </h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button"
                                        class="answer-mode-btn w-100 py-2 rounded-2 border {{ $myAnswerMode === 'app' ? 'border-primary bg-primary text-white' : 'border-secondary bg-white' }}"
                                        data-mode="app"
                                        data-question-id="{{ $todayQuestion->id }}">
                                    <i class="bi bi-keyboard-fill d-block mb-1"></i>
                                    <small class="fw-semibold">Jawab Sekarang</small>
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button"
                                        class="answer-mode-btn w-100 py-2 rounded-2 border {{ $myAnswerMode === 'call' ? 'border-warning bg-warning text-dark' : 'border-secondary bg-white' }}"
                                        data-mode="call"
                                        data-question-id="{{ $todayQuestion->id }}">
                                    <i class="bi bi-telephone-fill d-block mb-1"></i>
                                    <small class="fw-semibold">Nanti Call</small>
                                </button>
                            </div>
                        </div>
                        @if($prefersCall)
                            <p class="text-muted small mb-0 mt-2 text-center">
                                <i class="bi bi-info-circle"></i>
                                Jawaban akan dibahas pas call malam ya!
                            </p>
                        @endif
                    </div>

                    <!-- Answer Form -->
                    @if($todayQuestion->hasUserAnswered(Auth::user()))
                        <!-- Already Answered -->
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill"></i>
                            <strong>Kamu sudah menjawab!</strong>
                        </div>

                        <div class="my-answer-box bg-white border p-3 rounded-3 mb-3">
                            <h6 class="text-muted small mb-2">Jawaban kamu:</h6>
                            <p class="mb-0">{{ e($todayQuestion->getAnswerForUser(Auth::user())) }}</p>
                        </div>

                        <!-- Update Button -->
                        @if($canAnswerViaApp)
                            <button type="button" class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#updateAnswerModal">
                                <i class="bi bi-pencil"></i> Update Jawaban
                            </button>
                        @else
                            <div class="alert alert-info small">
                                <i class="bi bi-info-circle"></i>
                                Kamu memilih jawab saat call. Ubah mode dulu untuk update jawaban.
                            </div>
                        @endif

                        <!-- Partner's Answer -->
                        @if($todayQuestion->bothAnswered())
                            @php
                                $partnerAnswer = $couple->isUserOne(Auth::user())
                                    ? $todayQuestion->answer_two
                                    : $todayQuestion->answer_one;
                            @endphp
                            @if($partnerAnswer)
                                <div class="mt-4 pt-4 border-top">
                                    <h6 class="text-muted small mb-2">
                                        <i class="bi bi-person-fill"></i> Jawaban Pasangan:
                                    </h6>
                                    <p class="mb-0">{{ e($partnerAnswer) }}</p>
                                </div>
                            @endif
                        @else
                            <div class="mt-4 pt-4 border-top">
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-hourglass-split"></i>
                                    Pasangan belum menjawab
                                </p>
                            </div>
                        @endif
                    @else
                        @if($canAnswerViaApp)
                            <!-- Answer Form -->
                            <form action="{{ route('questions.answer') }}" method="POST" id="answerForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Jawaban kamu:</label>
                                    <textarea
                                        name="answer"
                                        class="form-control"
                                        rows="5"
                                        maxlength="1000"
                                        required
                                        placeholder="Tulis jawaban kamu di sini..."></textarea>
                                    <div class="form-text">
                                        <span id="charCount">0</span>/1000 karakter
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-send-fill"></i> Kirim Jawaban
                                </button>
                            </form>
                        @else
                            <!-- Disabled Form Info -->
                            <div class="alert alert-warning">
                                <i class="bi bi-telephone-fill"></i>
                                <strong>Kamu memilih jawab saat call!</strong>
                                <p class="mb-0 small mt-2">
                                    Ubah pilihan di atas ke "Jawab Sekarang" jika mau jawab lewat aplikasi.
                                </p>
                            </div>

                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-chat-square-text display-4 d-block mb-2 opacity-50"></i>
                                <small>Form jawaban dinonaktifkan karena kamu memilih menjawab saat call.</small>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats & Info -->
        <div class="col-lg-6">
            <div class="row g-4">
                <!-- Stats Card -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">📊 Statistik</h5>

                            <div class="row text-center mt-3">
                                <div class="col-4">
                                    <div class="display-6">{{ $stats['total_questions'] }}</div>
                                    <small class="text-muted">Total Pertanyaan</small>
                                </div>
                                <div class="col-4">
                                    <div class="display-6">{{ $stats['both_answered'] }}</div>
                                    <small class="text-muted">Dijawab Berdua</small>
                                </div>
                                <div class="col-4">
                                    <div class="display-6">{{ $stats['completion_rate'] }}%</div>
                                    <small class="text-muted">Completion</small>
                                </div>
                            </div>

                            @if(!empty($stats['categories']))
                                <div class="mt-4">
                                    <h6 class="small text-muted mb-2">Kategori Terbanyak:</h6>
                                    @foreach($stats['categories'] as $cat => $count)
                                        @php
                                            $catLabel = DailyQuestion::getCategories()[$cat] ?? $cat;
                                        @endphp
                                        <span class="badge bg-secondary me-1 mb-1">
                                            {{ $catLabel }}: {{ $count }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent History -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">📜 Riwayat Terbaru</h5>

                            @if($history->count() > 0)
                                <div class="timeline-compact">
                                    @foreach($history as $q)
                                        @if($q->id !== $todayQuestion->id)
                                            <div class="timeline-item-compact mb-3 pb-3 {{ $loop->last ? '' : 'border-bottom' }}">
                                                <div class="d-flex justify-content-between align-start">
                                                    <div class="flex-grow-1">
                                                        <p class="mb-1 fw-semibold">{{ e(Str::limit($q->question, 80)) }}</p>
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar"></i> {{ $q->formatted_date }}
                                                            @if($q->bothAnswered())
                                                                <span class="badge bg-success ms-1">
                                                                    <i class="bi bi-check"></i> Berdua
                                                                </span>
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted text-center small mb-0">
                                    Belum ada riwayat pertanyaan
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
@if($todayQuestion->hasUserAnswered(Auth::user()) && $canAnswerViaApp)
<div class="modal fade" id="updateAnswerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('questions.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="question_id" value="{{ $todayQuestion->id }}">

                <div class="modal-header">
                    <h5 class="modal-title">Update Jawaban</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="question-box bg-light p-3 rounded-3 mb-3">
                        <small class="text-muted">Pertanyaan:</small>
                        <p class="mb-0">{{ e($todayQuestion->question) }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jawaban kamu:</label>
                        <textarea
                            name="answer"
                            class="form-control"
                            rows="6"
                            maxlength="1000"
                            required>{{ e($todayQuestion->getAnswerForUser(Auth::user())) }}</textarea>
                        <div class="form-text">
                            <span class="update-char-count">{{ strlen($todayQuestion->getAnswerForUser(Auth::user())) }}</span>/1000 karakter
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endpush

@push('scripts')
<script>
    // Character counter for answer form
    const answerTextarea = document.querySelector('textarea[name="answer"]');
    const charCount = document.getElementById('charCount');

    if (answerTextarea && charCount) {
        answerTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Character counter for update modal
    const updateTextarea = document.querySelector('#updateAnswerModal textarea[name="answer"]');
    const updateCharCount = document.querySelector('.update-char-count');

    if (updateTextarea && updateCharCount) {
        updateTextarea.addEventListener('input', function() {
            updateCharCount.textContent = this.value.length;
        });
    }

    // Answer Mode Selector
    const answerModeBtns = document.querySelectorAll('.answer-mode-btn');

    answerModeBtns.forEach(btn => {
        btn.addEventListener('click', async function() {
            const mode = this.dataset.mode;
            const questionId = this.dataset.questionId;
            const container = this.closest('.answer-mode-selector');
            const otherBtn = container.querySelector(`.answer-mode-btn[data-mode="${mode === 'app' ? 'call' : 'app'}"]`);

            // Show loading state
            this.disabled = true;
            const originalContent = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            try {
                const response = await fetch('{{ route('questions.set-answer-mode') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        mode: mode
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Reload page to reflect changes
                    window.location.reload();
                } else {
                    // Show error
                    alert(data.message || 'Gagal mengubah mode jawab');
                    this.innerHTML = originalContent;
                    this.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
                this.innerHTML = originalContent;
                this.disabled = false;
            }
        });
    });
</script>
@endpush

<style>
    .question-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .my-answer-box {
        background-color: #f8f9fa;
        border-left: 4px solid #198754 !important;
    }

    .answer-mode-btn {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .answer-mode-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .answer-mode-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .timeline-item-compact:last-child {
        border-bottom: none !important;
        padding-bottom: 0 !important;
    }

    .timeline-compact .timeline-item-compact {
        transition: background-color 0.2s;
    }

    .timeline-compact .timeline-item-compact:hover {
        background-color: rgba(0, 0, 0, 0.02);
        border-radius: 8px;
        padding: 8px;
        margin: -8px;
    }
</style>
