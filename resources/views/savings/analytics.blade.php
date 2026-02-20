@extends('layouts.app')

@section('title', 'Savings Analytics')

@section('content')
<div class="container-fluid">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 Savings Analytics</h2>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary period-btn" data-period="all">All Time</button>
            <button type="button" class="btn btn-outline-primary period-btn" data-period="year">This Year</button>
            <button type="button" class="btn btn-outline-primary period-btn" data-period="quarter">This Quarter</button>
            <button type="button" class="btn btn-outline-primary period-btn active" data-period="month">This Month</button>
        </div>
    </div>

    {{-- Overview Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Savings</h6>
                    <h3>Rp {{ number_format($analytics['overview']['total_savings'], 0, ',', '.') }}</h3>
                    <small>{{ $analytics['overview']['overall_progress'] }} of target</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Completed Goals</h6>
                    <h3>{{ $analytics['overview']['completed_savings'] }} / {{ $analytics['overview']['total_savings_count'] }}</h3>
                    <small>{{ $analytics['overview']['completion_rate'] }}% completion rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Deposits</h6>
                    <h3>Rp {{ number_format($analytics['overview']['total_deposits'], 0, ',', '.') }}</h3>
                    <small>Net: Rp {{ number_format($analytics['overview']['net_savings'], 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Avg. Progress</h6>
                    <h3>{{ $analytics['overview']['average_progress'] }}%</h3>
                    <small>Across all savings</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Savings Growth Chart --}}
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <strong>📈 Savings Growth</strong>
                </div>
                <div class="card-body">
                    <canvas id="growthChart" height="300"></canvas>
                </div>
            </div>
        </div>

        {{-- Category Distribution --}}
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <strong>🏷️ By Category</strong>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Goals Progress --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <strong>🎯 Goals Progress</strong>
                </div>
                <div class="card-body">
                    @if(empty($analytics['goals_progress']))
                        <p class="text-muted">No savings goals yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Goal</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytics['goals_progress'] as $goal)
                                    <tr>
                                        <td>
                                            @if($goal['category'])
                                                <span style="color: {{ $goal['category']['color'] }}">
                                                    {{ $goal['category']['icon'] }}
                                                </span>
                                            @endif
                                            {{ $goal['name'] }}
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar
                                                    {{ $goal['progress'] >= 100 ? 'bg-success' :
                                                       ($goal['status'] === 'urgent' ? 'bg-danger' : 'bg-primary') }}"
                                                    style="width: {{ min(100, $goal['progress']) }}%">
                                                    {{ round($goal['progress'], 1) }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($goal['is_completed'])
                                                <span class="badge bg-success">Done</span>
                                            @elseif($goal['is_overdue'])
                                                <span class="badge bg-danger">Overdue</span>
                                            @elseif($goal['status'] === 'urgent')
                                                <span class="badge bg-warning">Urgent</span>
                                            @else
                                                <span class="badge bg-info">On Track</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Monthly Summary --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <strong>📅 Monthly Summary</strong>
                </div>
                <div class="card-body">
                    @if(empty($analytics['monthly_summary']))
                        <p class="text-muted">No data available.</p>
                    @else
                        <canvas id="monthlyChart" height="250"></canvas>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Upcoming Targets --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <strong>📆 Upcoming Targets</strong>
                </div>
                <div class="card-body">
                    @if($upcoming->isEmpty())
                        <p class="text-muted">No upcoming targets.</p>
                    @else
                        <div class="row">
                            @foreach($upcoming as $target)
                            <div class="col-md-4 mb-3">
                                <div class="card {{ $target['is_overdue'] ? 'border-danger' : '' }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">
                                                @if($target['category'])
                                                    <span style="color: {{ $target['category']['color'] }}">
                                                        {{ $target['category']['icon'] }}
                                                    </span>
                                                @endif
                                                {{ $target['name'] }}
                                            </h6>
                                            @if($target['is_overdue'])
                                                <span class="badge bg-danger">Overdue</span>
                                            @elseif($target['days_remaining'] <= 7)
                                                <span class="badge bg-warning">{{ $target['days_remaining'] }} days</span>
                                            @else
                                                <span class="badge bg-info">{{ $target['days_remaining'] }} days</span>
                                            @endif
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">Target: {{ $target['target_date'] }}</small>
                                        </div>
                                        <div class="progress mb-2" style="height: 20px;">
                                            <div class="progress-bar
                                                {{ $target['progress'] >= 100 ? 'bg-success' :
                                                   ($target['is_overdue'] ? 'bg-danger' : 'bg-primary') }}"
                                                style="width: {{ min(100, $target['progress']) }}%">
                                                {{ round($target['progress'], 1) }}%
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted small">
                                            <span>Rp {{ number_format($target['current_amount'], 0, ',', '.') }}</span>
                                            <span>Rp {{ number_format($target['target_amount'], 0, ',', '.') }}</span>
                                        </div>
                                        <div class="text-center mt-2">
                                            <small class="text-info">Remaining: Rp {{ number_format($target['remaining'], 0, ',', '.') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <strong>🕐 Recent Activity</strong>
                </div>
                <div class="card-body">
                    @if(empty($analytics['recent_activity']))
                        <p class="text-muted">No recent activity.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Saving</th>
                                        <th>Amount</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytics['recent_activity'] as $activity)
                                    <tr>
                                        <td>{{ $activity['date'] }}</td>
                                        <td>
                                            <span class="badge
                                                {{ $activity['type'] === 'deposit' ? 'bg-success' : 'bg-danger' }}">
                                                {{ ucfirst($activity['type']) }}
                                            </span>
                                        </td>
                                        <td>{{ $activity['saving_name'] }}</td>
                                        <td class="{{ $activity['type'] === 'deposit' ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($activity['amount'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-muted">{{ $activity['note'] ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('savings.index') }}" class="btn btn-secondary">⬅️ Back to Savings</a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Growth Chart
const growthData = @json($growth['data']);
const growthCtx = document.getElementById('growthChart').getContext('2d');
new Chart(growthCtx, {
    type: 'line',
    data: {
        labels: growthData.map(d => d.date),
        datasets: [{
            label: 'Total Savings',
            data: growthData.map(d => d.amount),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

// Category Distribution Chart
const categoryData = @json($categoryDistribution);
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: categoryData.map(c => c.category.name),
        datasets: [{
            data: categoryData.map(c => c.amount),
            backgroundColor: categoryData.map(c => c.category.color),
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const percentage = categoryData[context.dataIndex].percentage;
                        return label + ': ' + percentage + '% (Rp ' + value.toLocaleString('id-ID') + ')';
                    }
                }
            }
        }
    }
});

// Monthly Summary Chart
const monthlyData = @json($analytics['monthly_summary']);
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: monthlyData.map(m => m.month_formatted),
        datasets: [
            {
                label: 'Deposits',
                data: monthlyData.map(m => m.deposit_total),
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
            },
            {
                label: 'Withdrawals',
                data: monthlyData.map(m => m.withdrawal_total),
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: { stacked: true },
            y: {
                stacked: true,
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                    }
                }
            }
        }
    }
});

// Period filter functionality
document.querySelectorAll('.period-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const period = this.dataset.period;
        fetch(`{{ route('savings-analytics.index') }}?period=${period}`)
            .then(response => response.text())
            .then(html => {
                document.documentElement.innerHTML = html;
                history.pushState({}, '', `?period=${period}`);
            });
    });
});
</script>
@endpush
@endsection
