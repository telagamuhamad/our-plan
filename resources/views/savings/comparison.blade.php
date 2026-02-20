@extends('layouts.app')

@section('title', 'Savings Comparison with Partner')

@section('content')
<div class="container-fluid">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>💑 <strong>Savings Comparison</strong></h2>
        <a href="{{ route('savings.index') }}" class="btn btn-secondary">⬅️ Back to Savings</a>
    </div>

    {{-- Users Header --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    @if($comparison['users']['user']['avatar_url'])
                        <img src="{{ $comparison['users']['user']['avatar_url'] }}" alt="{{ $comparison['users']['user']['name'] }}" class="rounded-circle mb-2" width="80" height="80">
                    @else
                        <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-2" style="width: 80px; height: 80px;">
                            <span class="text-white fs-3">{{ substr($comparison['users']['user']['name'], 0, 1) }}</span>
                        </div>
                    @endif
                    <h5>{{ $comparison['users']['user']['name'] }}</h5>
                    <span class="badge bg-primary fs-6">You</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    @if($comparison['users']['partner']['avatar_url'])
                        <img src="{{ $comparison['users']['partner']['avatar_url'] }}" alt="{{ $comparison['users']['partner']['name'] }}" class="rounded-circle mb-2" width="80" height="80">
                    @else
                        <div class="rounded-circle bg-info d-inline-flex align-items-center justify-content-center mb-2" style="width: 80px; height: 80px;">
                            <span class="text-white fs-3">{{ substr($comparison['users']['partner']['name'], 0, 1) }}</span>
                        </div>
                    @endif
                    <h5>{{ $comparison['users']['partner']['name'] }}</h5>
                    <span class="badge bg-info fs-6">Partner</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Overview Comparison --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>📊 Overview Comparison</strong>
            @if($comparison['overview']['leader'] === 'user')
                <span class="badge bg-success float-end mt-1">🏆 You\'re Leading!</span>
            @elseif($comparison['overview']['leader'] === 'partner')
                <span class="badge bg-info float-end mt-1">Partner is Leading</span>
            @else
                <span class="badge bg-secondary float-end mt-1">🤝 It\'s a Tie!</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                {{-- Total Savings --}}
                <div class="col-md-4 mb-3">
                    <h6 class="text-muted">Total Savings</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-center flex-grow-1">
                            <small>You</small>
                            <h4 class="text-primary">Rp {{ number_format($comparison['overview']['user']['total_savings'], 0, ',', '.') }}</h4>
                        </div>
                        <div class="text-center flex-grow-1">
                            <small>Partner</small>
                            <h4 class="text-info">Rp {{ number_format($comparison['overview']['partner']['total_savings'], 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    @if($comparison['overview']['combined']['total_savings'] > 0)
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-primary" style="width: {{ $comparison['overview']['combined']['user_contribution'] }}%">
                                {{ $comparison['overview']['combined']['user_contribution'] }}%
                            </div>
                            <div class="progress-bar bg-info" style="width: {{ $comparison['overview']['combined']['partner_contribution'] }}%">
                                {{ $comparison['overview']['combined']['partner_contribution'] }}%
                            </div>
                        </div>
                        <div class="text-center mt-1">
                            <strong>Combined: Rp {{ number_format($comparison['overview']['combined']['total_savings'], 0, ',', '.') }}</strong>
                        </div>
                    @endif
                </div>

                {{-- Completion Rate --}}
                <div class="col-md-4 mb-3">
                    <h6 class="text-muted">Completion Rate</h6>
                    <div class="d-flex justify-content-between">
                        <div class="text-center">
                            <small>You</small>
                            <h3 class="{{ $comparison['overview']['user']['completion_rate'] >= 50 ? 'text-success' : 'text-warning' }}">
                                {{ $comparison['overview']['user']['completion_rate'] }}%
                            </h3>
                            <small class="text-muted">{{ $comparison['overview']['user']['completed_count'] }}/{{ $comparison['overview']['user']['active_count'] + $comparison['overview']['user']['completed_count'] }}</small>
                        </div>
                        <div class="text-center">
                            <small>Partner</small>
                            <h3 class="{{ $comparison['overview']['partner']['completion_rate'] >= 50 ? 'text-success' : 'text-warning' }}">
                                {{ $comparison['overview']['partner']['completion_rate'] }}%
                            </h3>
                            <small class="text-muted">{{ $comparison['overview']['partner']['completed_count'] }}/{{ $comparison['overview']['partner']['active_count'] + $comparison['overview']['partner']['completed_count'] }}</small>
                        </div>
                    </div>
                </div>

                {{-- Total Deposits --}}
                <div class="col-md-4 mb-3">
                    <h6 class="text-muted">Total Deposits</h6>
                    <div class="d-flex justify-content-between">
                        <div class="text-center">
                            <small>You</small>
                            <h4 class="text-success">Rp {{ number_format($comparison['overview']['user']['total_deposits'], 0, ',', '.') }}</h4>
                        </div>
                        <div class="text-center">
                            <small>Partner</small>
                            <h4 class="text-success">Rp {{ number_format($comparison['overview']['partner']['total_deposits'], 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Savings by Category --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <strong>🏷️ By Category</strong>
                </div>
                <div class="card-body">
                    @if($comparison['savings_list']->isEmpty())
                        <p class="text-muted text-center">No category data available.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th class="text-end">You</th>
                                        <th class="text-end">Partner</th>
                                        <th class="text-center">Leader</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comparison['savings_list'] as $item)
                                    <tr>
                                        <td>
                                            <span style="color: {{ $item['category']['color'] }}">
                                                {{ $item['category']['icon'] }}
                                            </span>
                                            {{ $item['category']['name'] }}
                                        </td>
                                        <td class="text-end">
                                            Rp {{ number_format($item['user']['amount'], 0, ',', '.') }}
                                            <small class="text-muted">({{ $item['user']['count'] }})</small>
                                        </td>
                                        <td class="text-end">
                                            Rp {{ number_format($item['partner']['amount'], 0, ',', '.') }}
                                            <small class="text-muted">({{ $item['partner']['count'] }})</small>
                                        </td>
                                        <td class="text-center">
                                            @if($item['leader'] === 'user')
                                                <span class="badge bg-primary">You</span>
                                            @elseif($item['leader'] === 'partner')
                                                <span class="badge bg-info">Partner</span>
                                            @else
                                                <span class="badge bg-secondary">Tie</span>
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

        {{-- Monthly Contributions Chart --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <strong>📅 Monthly Contributions</strong>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Your Goals --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <strong>🎯 Your Goals Progress</strong>
                </div>
                <div class="card-body">
                    @if($comparison['goals_progress']['user']->isEmpty())
                        <p class="text-muted text-center">No active goals.</p>
                    @else
                        @foreach($comparison['goals_progress']['user'] as $goal)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small>
                                    @if($goal['category'])
                                        <span style="color: {{ $goal['category']['color'] }}">
                                            {{ $goal['category']['icon'] }}
                                        </span>
                                    @endif
                                    {{ $goal['name'] }}
                                </small>
                                <small>{{ $goal['progress'] }}%</small>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $goal['progress'] >= 100 ? 'bg-success' : 'bg-primary' }}"
                                    style="width: {{ min(100, $goal['progress']) }}%">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span>Rp {{ number_format($goal['current_amount'], 0, ',', '.') }}</span>
                                <span>Rp {{ number_format($goal['target_amount'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- Partner's Goals --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <strong>🎯 Partner's Goals Progress</strong>
                </div>
                <div class="card-body">
                    @if($comparison['goals_progress']['partner']->isEmpty())
                        <p class="text-muted text-center">No active goals.</p>
                    @else
                        @foreach($comparison['goals_progress']['partner'] as $goal)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small>
                                    @if($goal['category'])
                                        <span style="color: {{ $goal['category']['color'] }}">
                                            {{ $goal['category']['icon'] }}
                                        </span>
                                    @endif
                                    {{ $goal['name'] }}
                                </small>
                                <small>{{ $goal['progress'] }}%</small>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $goal['progress'] >= 100 ? 'bg-success' : 'bg-info' }}"
                                    style="width: {{ min(100, $goal['progress']) }}%">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span>Rp {{ number_format($goal['current_amount'], 0, ',', '.') }}</span>
                                <span>Rp {{ number_format($goal['target_amount'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Achievements Comparison --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <strong>🏆 Achievements</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Your Badges ({{ $comparison['achievements']['total_badges']['user'] }})</h6>
                            @if(empty($comparison['achievements']['user']))
                                <p class="text-muted">No achievements yet. Start saving to earn badges!</p>
                            @else
                                <div class="row">
                                    @foreach($comparison['achievements']['user'] as $achievement)
                                    <div class="col-md-6 mb-2">
                                        <div class="card bg-light">
                                            <div class="card-body p-2">
                                                <div class="d-flex align-items-center">
                                                    <span class="fs-3 me-2">{{ $achievement['icon'] }}</span>
                                                    <div>
                                                        <strong class="d-block">{{ $achievement['name'] }}</strong>
                                                        <small class="text-muted">{{ $achievement['description'] }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Partner's Badges ({{ $comparison['achievements']['total_badges']['partner'] }})</h6>
                            @if(empty($comparison['achievements']['partner']))
                                <p class="text-muted">No achievements yet.</p>
                            @else
                                <div class="row">
                                    @foreach($comparison['achievements']['partner'] as $achievement)
                                    <div class="col-md-6 mb-2">
                                        <div class="card bg-light">
                                            <div class="card-body p-2">
                                                <div class="d-flex align-items-center">
                                                    <span class="fs-3 me-2">{{ $achievement['icon'] }}</span>
                                                    <div>
                                                        <strong class="d-block">{{ $achievement['name'] }}</strong>
                                                        <small class="text-muted">{{ $achievement['description'] }}</small>
                                                    </div>
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
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('savings.index') }}" class="btn btn-secondary">⬅️ Back to Savings Tracker</a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const monthlyData = @json($comparison['monthly_contributions']);
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: monthlyData.map(d => d.month_formatted),
        datasets: [
            {
                label: 'You',
                data: monthlyData.map(d => d.user),
                backgroundColor: 'rgba(13, 110, 253, 0.7)',
            },
            {
                label: 'Partner',
                data: monthlyData.map(d => d.partner),
                backgroundColor: 'rgba(23, 162, 184, 0.7)',
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
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
</script>
@endpush
@endsection
