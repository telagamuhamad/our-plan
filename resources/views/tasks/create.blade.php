@extends('layouts.app')

@section('title', 'Create Task - Our Plan')

@section('content')
<div class="tasks-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">✅ Buat Task Baru</h4>
            <p class="text-muted mb-0 small">Tambahkan task baru</p>
        </div>
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('tasks.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Judul Task <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title') }}"
                                required
                                maxlength="255"
                                placeholder="Apa yang perlu dikerjakan?">
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
                                placeholder="Detail tambahan tentang task ini...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Goal (Opsional)</label>
                                <select name="goal_id" class="form-select @error('goal_id') is-invalid @enderror">
                                    <option value="">Tanpa Goal (Standalone)</option>
                                    @foreach($goals as $goal)
                                        <option value="{{ $goal->id }}" {{ old('goal_id', request('goal_id')) == $goal->id ? 'selected' : '' }}>
                                            {{ e($goal->title) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('goal_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select @error('priority') is-invalid @enderror">
                                    <option value="low">🟢 Low</option>
                                    <option value="medium" selected>🟡 Medium</option>
                                    <option value="high">🔴 High</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Due Date</label>
                                <input
                                    type="date"
                                    name="due_date"
                                    class="form-control @error('due_date') is-invalid @enderror"
                                    value="{{ old('due_date') }}"
                                    min="{{ now()->addDay()->format('Y-m-d') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assigned To</label>
                                <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                    @foreach($assignedOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('assigned_to', 'both') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    name="reminder_enabled"
                                    class="form-check-input"
                                    id="reminderEnabled"
                                    value="1">
                                <label class="form-check-label" for="reminderEnabled">
                                    <i class="bi bi-bell"></i> Enable Reminder
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Simpan Task
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
                        <li class="mb-2">Buat judul task yang jelas dan spesifik</li>
                        <li class="mb-2">Set priority yang sesuai dengan urgensi</li>
                        <li class="mb-2">Beri due date yang realistis</li>
                        <li class="mb-2">Tentukan siapa yang bertanggung jawab</li>
                        <li class="mb-0">Hubungkan task ke goal jika relevan</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">🎯 Goals</h5>
                    <p class="text-muted small">Hubungkan task ke goal untuk melacak progress:</p>
                    <div class="list-group list-group-flush">
                        @foreach($goals as $goal)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <small>{{ e(Str::limit($goal->title, 30)) }}</small>
                                    <small class="text-muted">{{ $goal->completed_tasks }}/{{ $goal->total_tasks }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
