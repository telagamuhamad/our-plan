@extends('layouts.app')

@section('title', 'Edit Goal - Our Plan')

@section('content')
<div class="goals-edit">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">✏️ Edit Goal</h4>
            <p class="text-muted mb-0 small">Update informasi goal</p>
        </div>
        <a href="{{ route('goals.show', $goal->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('goals.update', $goal->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Judul Goal <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $goal->title) }}"
                                required
                                maxlength="255">
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
                                maxlength="1000">{{ old('description', $goal->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ old('category', $goal->category) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', $goal->status) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Target Date</label>
                                <input
                                    type="date"
                                    name="target_date"
                                    class="form-control @error('target_date') is-invalid @enderror"
                                    value="{{ old('target_date', $goal->target_date ? $goal->target_date->format('Y-m-d') : '') }}"
                                    min="{{ now()->addDay()->format('Y-m-d') }}">
                                @error('target_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Progress Info -->
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Progress</span>
                                <strong>{{ number_format($goal->progress_percentage, 1) }}%</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary"
                                     role="progressbar"
                                     style="width: {{ $goal->progress_percentage }}%"></div>
                            </div>
                            <small class="text-muted">{{ $goal->completed_tasks }} dari {{ $goal->total_tasks }} tasks selesai</small>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route('goals.show', $goal->id) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">📋 Tasks</h5>
                    <p class="text-muted small">Untuk mengelola tasks, klik tombol di bawah:</p>
                    <a href="{{ route('goals.show', $goal->id) }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-list-check"></i> Lihat & Kelola Tasks
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
