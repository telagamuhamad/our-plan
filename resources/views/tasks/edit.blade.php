@extends('layouts.app')

@section('title', 'Edit Task - Our Plan')

@section('content')
<div class="tasks-edit">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">✏️ Edit Task</h4>
            <p class="text-muted mb-0 small">Update informasi task</p>
        </div>
        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Judul Task <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="title"
                                class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $task->title) }}"
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
                                maxlength="1000">{{ old('description', $task->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Goal</label>
                                <select name="goal_id" class="form-select @error('goal_id') is-invalid @enderror">
                                    <option value="">Tanpa Goal (Standalone)</option>
                                    @foreach($goals as $goal)
                                        <option value="{{ $goal->id }}" {{ old('goal_id', $task->goal_id) == $goal->id ? 'selected' : '' }}>
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
                                    <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>🟢 Low</option>
                                    <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>🟡 Medium</option>
                                    <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>🔴 High</option>
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
                                    value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d') : '') }}"
                                    min="{{ now()->addDay()->format('Y-m-d') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assigned To</label>
                                <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                    @foreach($assignedOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('assigned_to', $task->assigned_to) == $key ? 'selected' : '' }}>
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
                                    value="1"
                                    {{ old('reminder_enabled', $task->reminder_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label" for="reminderEnabled">
                                    <i class="bi bi-bell"></i> Enable Reminder
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-outline-secondary">
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
    </div>
</div>
@endsection
