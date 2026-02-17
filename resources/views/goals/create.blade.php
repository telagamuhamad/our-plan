@extends('layouts.app')

@section('title', 'Create Goal - Our Plan')

@section('content')
<div class="goals-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">🎯 Buat Goal Baru</h4>
            <p class="text-muted mb-0 small">Tentukan tujuan bersama untuk dicapai bersama pasangan</p>
        </div>
        <a href="{{ route('goals.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('goals.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Judul Goal <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title') }}"
                                required
                                maxlength="255"
                                placeholder="Contoh: Liburan ke Bali">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="4"
                                maxlength="1000"
                                placeholder="Deskripsikan goal ini lebih detail...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Target Date</label>
                                <input
                                    type="date"
                                    name="target_date"
                                    class="form-control @error('target_date') is-invalid @enderror"
                                    value="{{ old('target_date') }}"
                                    min="{{ now()->addDay()->format('Y-m-d') }}">
                                @error('target_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tasks Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Tasks (Opsional)</label>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTask()">
                                    <i class="bi bi-plus"></i> Tambah Task
                                </button>
                            </div>

                            <div id="tasksContainer">
                                <!-- Tasks will be added here dynamically -->
                            </div>

                            <small class="text-muted">Tambahkan tasks untuk memecah goal menjadi langkah-langkah kecil.</small>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route('goals.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Simpan Goal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">💡 Tips</h5>
                    <ul class="small text-muted mb-0">
                        <li class="mb-2">Buat goal spesifik dan measurable</li>
                        <li class="mb-2">Set target date yang realistis</li>
                        <li class="mb-2">Pecah goal menjadi tasks kecil</li>
                        <li class="mb-0">Pilih kategori yang sesuai</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">📋 Kategori</h5>
                    <div class="small text-muted">
                        @foreach($categories as $key => $label)
                            <div class="mb-1">
                                <strong>{{ $label }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let taskCount = 0;

    function addTask() {
        taskCount++;
        const container = document.getElementById('tasksContainer');
        const taskHtml = `
            <div class="card mb-2 task-item" id="task-${taskCount}">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <input
                                type="text"
                                name="tasks[${taskCount}][title]"
                                class="form-control"
                                placeholder="Task title"
                                required>
                        </div>
                        <div class="col-12">
                            <textarea
                                name="tasks[${taskCount}][description]"
                                class="form-control"
                                rows="2"
                                placeholder="Deskripsi task (opsional)"></textarea>
                        </div>
                        <div class="col-md-4">
                            <select name="tasks[${taskCount}][priority]" class="form-select">
                                <option value="low">Low Priority</option>
                                <option value="medium" selected>Medium Priority</option>
                                <option value="high">High Priority</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input
                                type="date"
                                name="tasks[${taskCount}][due_date]"
                                class="form-control"
                                min="{{ now()->addDay()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <select name="tasks[${taskCount}][assigned_to]" class="form-select">
                                <option value="both">Berdua</option>
                                <option value="user_one">Saya Sendiri</option>
                                <option value="user_two">Pasangan</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTask(${taskCount})">
                                <i class="bi bi-trash"></i> Hapus Task
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', taskHtml);
    }

    function removeTask(id) {
        const task = document.getElementById(`task-${id}`);
        if (task) {
            task.remove();
        }
    }
</script>
@endpush
