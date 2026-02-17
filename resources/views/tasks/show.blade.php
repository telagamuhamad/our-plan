@extends('layouts.app')

@section('title', "{$task->title} - Our Plan")

@section('content')
<div class="task-show">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('tasks.index') }}" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left"></i> Kembali ke Tasks
            </a>
            <h4 class="mb-1 mt-2">
                @if($task->is_completed)
                    <i class="bi bi-check-circle-fill text-success"></i>
                @else
                    <i class="bi bi-circle text-muted"></i>
                @endif
                {{ e($task->title) }}
            </h4>
            <p class="text-muted mb-0 small">
                Created {{ $task->created_at->diffForHumans() }}
                @if($task->completed_at)
                    • Completed {{ $task->completed_at->diffForHumans() }}
                @endif
            </p>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-gear"></i> Actions
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
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
                        <i class="bi bi-pencil"></i> Edit Task
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus task ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-trash"></i> Hapus Task
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Task Details -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <small class="text-muted">Priority</small>
                            <div>
                                @if($task->priority === 'high')
                                    <span class="badge bg-danger fs-6">🔴 High</span>
                                @elseif($task->priority === 'medium')
                                    <span class="badge bg-warning text-dark fs-6">🟡 Medium</span>
                                @else
                                    <span class="badge bg-success fs-6">🟢 Low</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Status</small>
                            <div>
                                @if($task->is_completed)
                                    <span class="badge bg-success fs-6">Completed</span>
                                @else
                                    <span class="badge bg-secondary fs-6">Pending</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Assigned To</small>
                            <div>{{ $task->assigned_to_label }}</div>
                        </div>
                    </div>

                    @if($task->description)
                        <hr>
                        <h6 class="mb-2">Deskripsi</h6>
                        <p class="text-muted">{{ e($task->description) }}</p>
                    @endif

                    @if($task->due_date)
                        <hr>
                        <div>
                            <small class="text-muted">Due Date</small>
                            <div class="fs-5 @if($task->isOverdue()) text-danger @elseif($task->isDueSoon()) text-warning @endif">
                                {{ $task->due_date->format('l, F d, Y') }}
                                @if($task->isOverdue())
                                    <span class="badge bg-danger ms-2">Overdue</span>
                                @elseif($task->isDueSoon())
                                    <span class="badge bg-warning text-dark ms-2">Due Soon</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Related Goal -->
            @if($task->goal)
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-3">🎯 Related Goal</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ e($task->goal->title) }}</h6>
                                <small class="text-muted">{{ $task->goal->completed_tasks }}/{{ $task->goal->total_tasks }} tasks completed</small>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $task->goal->progress_percentage }}%"></div>
                                </div>
                            </div>
                            <a href="{{ route('goals.show', $task->goal->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View Goal
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Creator Info -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title mb-2">Created By</h6>
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            {{ strtoupper(substr($task->creator->name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $task->creator->name ?? 'Unknown' }}</div>
                            <small class="text-muted">{{ $task->created_at->format('M d, Y \a\t H:i') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completion Info -->
            @if($task->is_completed && $task->completer)
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title mb-2">✅ Completion Info</h6>
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-check"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $task->completer->name }}</div>
                                <small class="text-muted">{{ $task->completed_at->format('M d, Y \a\t H:i') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3">Quick Actions</h6>
                    <div class="d-grid gap-2">
                        @if(!$task->is_completed)
                            <form action="{{ route('tasks.toggle', $task->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Mark Complete
                                </button>
                            </form>
                        @else
                            <form action="{{ route('tasks.toggle', $task->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-arrow-counterclockwise"></i> Mark Incomplete
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Edit Task
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
