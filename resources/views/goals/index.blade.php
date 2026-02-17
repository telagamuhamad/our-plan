@extends('layouts.app')

@section('title', 'Goals - Our Plan')

@section('content')
<div class="goals-page">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">🎯 Couple Goals</h4>
            <p class="text-muted mb-0 small">Wujudkan impian bersama pasangan</p>
        </div>
        <a href="{{ route('goals.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Goal Baru
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
        <!-- Stats Cards -->
        <div class="col-lg-8">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="display-6">{{ $stats['total_goals'] }}</div>
                            <small class="text-muted">Total Goals</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="display-6">{{ $stats['active_goals'] }}</div>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="display-6">{{ $stats['completed_goals'] }}</div>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="display-6">{{ $stats['overall_progress'] }}%</div>
                            <small class="text-muted">Progress</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <select class="form-select" id="statusFilter" onchange="filterGoals()">
                                <option value="all">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="categoryFilter" onchange="filterGoals()">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Goals List -->
            <div class="row g-3">
                @forelse($goals as $goal)
                    <div class="col-12">
                        <div class="card goal-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <h5 class="card-title mb-0">{{ e($goal->title) }}</h5>
                                            @if($goal->status === 'completed')
                                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Selesai</span>
                                            @elseif($goal->status === 'in_progress')
                                                <span class="badge bg-primary">In Progress</span>
                                            @else
                                                <span class="badge bg-secondary">Pending</span>
                                            @endif
                                        </div>

                                        @if($goal->description)
                                            <p class="text-muted small mb-2">{{ e(Str::limit($goal->description, 150)) }}</p>
                                        @endif

                                        <div class="d-flex gap-3 mb-2">
                                            @if($goal->category)
                                                <small class="text-muted">
                                                    <i class="bi bi-folder"></i> {{ $categories[$goal->category] ?? $goal->category }}
                                                </small>
                                            @endif
                                            @if($goal->target_date)
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar"></i> {{ $goal->target_date->format('M d, Y') }}
                                                </small>
                                            @endif
                                            <small class="text-muted">
                                                <i class="bi bi-list-check"></i> {{ $goal->completed_tasks }}/{{ $goal->total_tasks }} Tasks
                                            </small>
                                        </div>

                                        <!-- Progress Bar -->
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar @if($goal->progress_percentage == 100) bg-success @elseif($goal->progress_percentage >= 50) bg-primary @else bg-warning @endif"
                                                 role="progressbar"
                                                 style="width: {{ $goal->progress_percentage }}%"
                                                 aria-valuenow="{{ $goal->progress_percentage }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100"></div>
                                        </div>
                                        <small class="text-muted">{{ number_format($goal->progress_percentage, 1) }}% Complete</small>
                                    </div>

                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i> Action
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('goals.show', $goal->id) }}">
                                                    <i class="bi bi-eye"></i> Lihat Detail
                                                </a>
                                            </li>
                                            @if($goal->status !== 'completed')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('goals.edit', $goal->id) }}">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('goals.mark-completed', $goal->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-check-circle"></i> Mark Selesai
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('goals.destroy', $goal->id) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Yakin ingin menghapus goal ini?')">
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
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-target display-4 text-muted mb-3"></i>
                                <h5>Belum Ada Goal</h5>
                                <p class="text-muted">Mulai buat goal pertama untuk bersama pasangan!</p>
                                <a href="{{ route('goals.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i> Buat Goal Baru
                                </a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">📋 Kategori</h5>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach($categories as $key => $label)
                            <a href="?category={{ $key }}" class="btn btn-sm btn-outline-secondary">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">📊 Status</h5>
                    <div class="d-flex flex-column gap-2 mt-2">
                        <a href="?status=in_progress" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-clock"></i> In Progress ({{ $stats['active_goals'] }})
                        </a>
                        <a href="?status=completed" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-check-circle"></i> Completed ({{ $stats['completed_goals'] }})
                        </a>
                        <a href="?status=pending" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-hourglass"></i> Pending
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function filterGoals() {
        const status = document.getElementById('statusFilter').value;
        const category = document.getElementById('categoryFilter').value;
        const url = new URL(window.location);
        url.searchParams.set('status', status);
        if (category) {
            url.searchParams.set('category', category);
        } else {
            url.searchParams.delete('category');
        }
        window.location = url.toString();
    }

    function resetFilters() {
        const url = new URL(window.location);
        url.searchParams.delete('status');
        url.searchParams.delete('category');
        window.location = url.toString();
    }
</script>
@endpush

<style>
    .goal-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .goal-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
