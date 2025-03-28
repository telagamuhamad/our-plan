@extends('layouts.app')

@section('title', 'Savings Tracker')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">💰 <strong>Savings Tracker</strong></h2>
        <div>
            <a href="{{ route('savings.create') }}" class="btn btn-primary me-2">➕ Tambah Tabungan</a>
            <a href="{{ route('savings.transfer.form') }}" class="btn btn-warning">🔄 Transfer Saldo</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Filter Kategori -->
    <form method="GET" action="{{ route('savings.index') }}" class="mb-3">
        <label for="category" class="form-label">🔍 Filter Kategori:</label>
        <select name="category" id="category" class="form-select" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" {{ $selectedCategory == $category ? 'selected' : '' }}>
                    {{ $category }}
                </option>
            @endforeach
        </select>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-light text-center">
            <tr>
                <th>#</th>
                <th>Nama Tabungan</th>
                <th>Target</th>
                <th>Jumlah Saat Ini</th>
                <th>Progress</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($savings as $index => $saving)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $saving->name }}</strong></td>
                <td class="text-end">
                    @if (!$saving->is_shared)
                        Rp {{ number_format($saving->target_amount, 0, ',', '.') }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-end">Rp {{ number_format($saving->current_amount, 0, ',', '.') }}</td>
                <td>
                    @if (!$saving->is_shared)
                        <div class="progress" style="height: 14px;">
                            <div class="progress-bar {{ $saving->progress >= 100 ? 'bg-success' : 'bg-info' }}" 
                                role="progressbar" 
                                style="width: {{ $saving->progress }}%;" 
                                aria-valuenow="{{ $saving->progress }}" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                {{ round($saving->progress, 2) }}%
                            </div>
                        </div>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td class="text-center">
                    <a href="{{ route('savings.show', $saving->id) }}" class="btn btn-info btn-sm">👁️ Detail</a>
                    <a href="{{ route('savings.edit', $saving->id) }}" class="btn btn-warning btn-sm">✏️ Edit</a>
                    <form action="{{ route('savings.destroy', $saving->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">🗑️ Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($savings->isEmpty())
        <div class="alert alert-warning text-center">
            Belum ada tabungan yang dibuat.
        </div>
    @endif

    <h3 class="mt-5 mb-3 text-center">
        📊 <strong>Distribusi Tabungan</strong>
    </h3>

    <div class="d-flex justify-content-center mb-5">
        <div style="width: 400px; position: relative;">
            <canvas id="savingsChart"></canvas>
            <div id="chart-center-text"
                 style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);
                        font-size: 18px; font-weight: bold; text-align: center;">
                Total<br>
                Rp {{ number_format($savings->sum('current_amount'), 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('savingsChart').getContext('2d');

    const savingsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($categoryData->keys()) !!},
            datasets: [{
                label: 'Total Simpanan',
                data: {!! json_encode($categoryData->values()) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function (tooltipItem) {
                            let value = tooltipItem.raw || 0;
                            return ` Rp ${value.toLocaleString('id-ID')}`;
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
