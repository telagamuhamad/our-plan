@extends('layouts.app')

@section('title', 'Tasks - Our Plan')

@section('content')
<div class="tasks-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">✅ To-Do List</h4>
            <p class="text-muted mb-0 small">Kelola tasks bersama pasangan</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Task Baru
        </a>
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
        <div class="col-lg-8">
            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="display-6">{{ $stats['total_tasks'] }}</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="display-6">{{ $stats['pending_tasks'] }}</div>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="display-6">{{ $stats['completed_tasks'] }}</div>
                            <small class="text-muted">Selesai</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="display-6">{{ $stats['my_pending'] }}</div>
                            <small class="text-muted">My Tasks</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter" onchange="filterTasks()">
                                <option value="all">Semua</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="priorityFilter" onchange="filterTasks()">
                                <option value="">Semua Priority</option>
                                @foreach($priorities as $key => $label)
                                    <option value="{{ $key }}" {{ request('priority') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="goalFilter" onchange="filterTasks()">
                                <option value="">Semua Goals</option>
                                @foreach($goals as $goal)
                                    <option value="{{ $goal->id }}" {{ request('goal_id') == $goal->id ? 'selected' : '' }}>{{ e($goal->title) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks List -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Semua Tasks</h5>

                    @if($tasks->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($tasks as $task)
                                <div class="list-group-item @if($task->is_completed) bg-light @endif task-item">
                                    <div class="d-flex justify-content-between align-start">
                                        <div class="flex-grow-1" onclick="window.location='{{ route('tasks.show', $task->id) }}'" style="cursor: pointer;">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                @if($task->is_completed)
                                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                @else
                                                    <i class="bi bi-circle text-muted fs-5"></i>
                                                @endif
                                                <h6 class="mb-0 @if($task->is_completed) text-decoration-line-through text-muted @endif">{{ e($task->title) }}</h6>

                                                @if($task->priority === 'high')
                                                    <span class="badge bg-danger">High</span>
                                                @elseif($task->priority === 'medium')
                                                    <span class="badge bg-warning text-dark">Medium</span>
                                                @else
                                                    <span class="badge bg-success">Low</span>
                                                @endif
                                            </div>

                                            @if($task->description)
                                                <p class="small text-muted mb-2">{{ e(Str::limit($task->description, 120)) }}</p>
                                            @endif

                                            <div class="d-flex gap-3">
                                                @if($task->due_date)
                                                    <small class="text-muted @if($task->isOverdue()) text-danger @elseif($task->isDueSoon()) text-warning @endif">
                                                        <i class="bi bi-calendar"></i> {{ $task->due_date->format('M d, Y') }}
                                                        @if($task->isOverdue())
                                                            <span class="badge bg-danger ms-1">Overdue</span>
                                                        @elseif($task->isDueSoon())
                                                            <span class="badge bg-warning text-dark ms-1">Due Soon</span>
                                                        @endif
                                                    </small>
                                                @endif
                                                <small class="text-muted">
                                                    <i class="bi bi-person"></i> {{ $task->assigned_to_label }}
                                                </small>
                                                @if($task->goal)
                                                    <small class="text-muted">
                                                        <i class="bi bi-flag"></i>
                                                        <a href="{{ route('goals.show', $task->goal->id) }}" class="text-decoration-none">
                                                            {{ e($task->goal->title) }}
                                                        </a>
                                                    </small>
                                                @else
                                                    <small class="text-muted">
                                                        <i class="bi bi-clipboard"></i> Standalone
                                                    </small>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form action="{{ route('tasks.toggle', $task->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm @if($task->is_completed) btn-outline-warning @else btn-outline-success @endif" title="Toggle Complete">
                                                    <i class="bi bi-{{ $task->is_completed ? 'arrow-counterclockwise' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard display-4 text-muted mb-3"></i>
                            <p class="text-muted">Belum ada tasks</p>
                            <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-lg"></i> Buat Task
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- My Tasks Section -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">👤 Tasks Saya</h5>

                    @if($myTasks->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($myTasks as $task)
                                <div class="list-group-item @if($task->is_completed) bg-light @endif">
                                    <div class="d-flex justify-content-between align-start">
                                        <div class="flex-grow-1" onclick="window.location='{{ route('tasks.show', $task->id) }}'" style="cursor: pointer;">
                                            <div class="d-flex align-items-center gap-2">
                                                @if($task->is_completed)
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                @else
                                                    <i class="bi bi-circle text-muted"></i>
                                                @endif
                                                <span class="@if($task->is_completed) text-decoration-line-through text-muted @endif">{{ e($task->title) }}</span>
                                            </div>

                                            @if($task->due_date)
                                                <small class="text-muted ms-4 @if($task->isOverdue()) text-danger @endif">
                                                    <i class="bi bi-calendar"></i> {{ $task->due_date->format('M d') }}
                                                </small>
                                            @endif
                                        </div>

                                        <div class="d-flex gap-2">
                                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form action="{{ route('tasks.toggle', $task->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm @if($task->is_completed) btn-outline-warning @else btn-outline-success @endif">
                                                    <i class="bi bi-{{ $task->is_completed ? 'arrow-counterclockwise' : 'check' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center small mb-0">Tidak ada tasks untuk kamu</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">📊 Statistik</h5>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Completion Rate</span>
                            <strong>{{ $stats['completion_rate'] }}%</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $stats['completion_rate'] }}%"></div>
                        </div>
                    </div>

                    @if($stats['overdue_count'] > 0)
                        <div class="alert alert-danger mt-3 mb-0">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>{{ $stats['overdue_count'] }}</strong> overdue tasks
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">🎯 Goals</h5>
                    <div class="list-group list-group-flush mt-2">
                        @foreach($goals as $goal)
                            <a href="{{ route('goals.show', $goal->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between">
                                    <span>{{ e(Str::limit($goal->title, 30)) }}</span>
                                    <small class="text-muted">{{ $goal->completed_tasks }}/{{ $goal->total_tasks }}</small>
                                </div>
                            </a>
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
    function filterTasks() {
        const status = document.getElementById('statusFilter').value;
        const priority = document.getElementById('priorityFilter').value;
        const goal = document.getElementById('goalFilter').value;
        const url = new URL(window.location);
        url.searchParams.set('status', status);
        if (priority) url.searchParams.set('priority', priority);
        if (goal) url.searchParams.set('goal_id', goal);
        window.location = url.toString();
    }

    function resetFilters() {
        const url = new URL(window.location);
        url.searchParams.delete('status');
        url.searchParams.delete('priority');
        url.searchParams.delete('goal_id');
        window.location = url.toString();
    }
</script>
@endpush

<style>
    .task-item {
        transition: background-color 0.2s;
    }

    .task-item:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
