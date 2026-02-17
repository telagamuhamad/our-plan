@extends('layouts.app')

@section('title', "{$goal->title} - Our Plan")

@section('content')
<div class="goal-show">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('goals.index') }}" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left"></i> Kembali ke Goals
            </a>
            <h4 class="mb-1 mt-2">🎯 {{ e($goal->title) }}</h4>
            <p class="text-muted mb-0 small">
                @if($goal->category)
                    {{ $categories[$goal->category] ?? $goal->category }} •
                @endif
                Created {{ $goal->created_at->diffForHumans() }}
            </p>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-gear"></i> Actions
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('goals.edit', $goal->id) }}">
                        <i class="bi bi-pencil"></i> Edit Goal
                    </a>
                </li>
                @if($goal->status !== 'completed')
                    <li>
                        <form action="{{ route('goals.mark-completed', $goal->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-check-circle"></i> Mark Selesai
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('goals.mark-in-progress', $goal->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-play-circle"></i> Mark In Progress
                            </button>
                        </form>
                    </li>
                @endif
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('goals.destroy', $goal->id) }}" method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus goal ini? Semua tasks akan terhapus.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-trash"></i> Hapus Goal
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Goal Details -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted">Status</small>
                            <div>
                                @if($goal->status === 'completed')
                                    <span class="badge bg-success fs-6">Completed</span>
                                @elseif($goal->status === 'in_progress')
                                    <span class="badge bg-primary fs-6">In Progress</span>
                                @elseif($goal->status === 'cancelled')
                                    <span class="badge bg-danger fs-6">Cancelled</span>
                                @else
                                    <span class="badge bg-secondary fs-6">Pending</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Target Date</small>
                            <div>
                                @if($goal->target_date)
                                    @if($goal->target_date->isPast())
                                        <span class="text-danger"><i class="bi bi-exclamation-triangle"></i> {{ $goal->target_date->format('M d, Y') }}</span>
                                    @else
                                        <span>{{ $goal->target_date->format('M d, Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($goal->description)
                        <hr>
                        <h6 class="mb-2">Deskripsi</h6>
                        <p class="text-muted">{{ e($goal->description) }}</p>
                    @endif

                    <!-- Progress -->
                    <hr>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Progress</small>
                            <small class="fw-semibold">{{ number_format($goal->progress_percentage, 1) }}%</small>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar @if($goal->progress_percentage == 100) bg-success @elseif($goal->progress_percentage >= 50) bg-primary @else bg-warning @endif"
                                 role="progressbar"
                                 style="width: {{ $goal->progress_percentage }}%"></div>
                        </div>
                        <small class="text-muted">{{ $goal->completed_tasks }} dari {{ $goal->total_tasks }} tasks selesai</small>
                    </div>
                </div>
            </div>

            <!-- Tasks List -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-check"></i> Tasks
                            <span class="badge bg-secondary ms-1">{{ $goal->tasks->count() }}</span>
                        </h5>
                        <a href="{{ route('tasks.create') }}?goal_id={{ $goal->id }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> Add Task
                        </a>
                    </div>

                    @if($goal->tasks->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($goal->tasks as $task)
                                <div class="list-group-item @if($task->is_completed) bg-light @endif">
                                    <div class="d-flex justify-content-between align-start">
                                        <div class="flex-grow-1" onclick="window.location='{{ route('tasks.show', $task->id) }}'" style="cursor: pointer;">
                                            <div class="d-flex align-items-center gap-2">
                                                @if($task->is_completed)
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                @else
                                                    <i class="bi bi-circle text-muted"></i>
                                                @endif
                                                <h6 class="mb-0 @if($task->is_completed) text-decoration-line-through text-muted @endif">{{ e($task->title) }}</h6>
                                            </div>

                                            <div class="d-flex gap-3 mt-2 ms-4">
                                                @if($task->due_date)
                                                    <small class="text-muted @if($task->isOverdue()) text-danger @endif">
                                                        <i class="bi bi-calendar"></i> {{ $task->due_date->format('M d') }}
                                                    </small>
                                                @endif
                                                <small class="text-muted">
                                                    <i class="bi bi-flag"></i> {{ $task->priority_label }}
                                                </small>
                                                <small class="text-muted">
                                                    <i class="bi bi-person"></i> {{ $task->assigned_to_label }}
                                                </small>
                                            </div>

                                            @if($task->description)
                                                <p class="small text-muted mt-2 ms-4">{{ e(Str::limit($task->description, 100)) }}</p>
                                            @endif
                                        </div>

                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i> Action
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('tasks.show', $task->id) }}">
                                                        <i class="bi bi-eye"></i> Lihat Detail
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('tasks.toggle', $task->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-{{ $task->is_completed ? 'arrow-counterclockwise' : 'check-circle' }}"></i>
                                                            {{ $task->is_completed ? 'Mark Incomplete' : 'Mark Complete' }}
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('tasks.edit', $task->id) }}">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST"
                                                          onsubmit="return confirm('Hapus task ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard display-4 text-muted mb-3"></i>
                            <p class="text-muted">Belum ada tasks</p>
                            <a href="{{ route('tasks.create') }}?goal_id={{ $goal->id }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-lg"></i> Add Task
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Creator Info -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title mb-2">Created By</h6>
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            {{ strtoupper(substr($goal->creator->name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $goal->creator->name ?? 'Unknown' }}</div>
                            <small class="text-muted">{{ $goal->created_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Date -->
            @if($goal->completed_at)
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title mb-2">🎉 Completed!</h6>
                        <p class="text-muted small mb-0">Goal diselesaikan pada {{ $goal->completed_at->format('M d, Y \a\t H:i') }}</p>
                    </div>
                </div>
            @endif

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3">📊 Stats</h6>
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="display-6">{{ $goal->total_tasks }}</div>
                            <small class="text-muted">Total Tasks</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="display-6">{{ $goal->completed_tasks }}</div>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">
                                {{ $goal->total_tasks - $goal->completed_tasks }} tasks remaining
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Fix dropdown z-index in goals page */
    .list-group-item .dropdown-menu {
        z-index: 1050 !important;
    }

    .list-group {
        overflow: visible !important;
    }

    .list-group-item {
        overflow: visible !important;
    }

    /* Ensure dropdown appears above other elements */
    .card-body .dropdown {
        position: relative;
        z-index: 10;
    }
</style>
@endpush
