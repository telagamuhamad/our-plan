@extends('layouts.app')

@section('title', 'Detail Tabungan')

@section('content')
<div class="container">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Saving Header --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                @if($saving->category)
                    <span class="badge mb-2" style="background-color: {{ $saving->category->color }}; font-size: 14px;">
                        {{ $saving->category->icon }} {{ $saving->category->name }}
                    </span>
                @endif
                <h2 class="mt-1">💰 <strong>{{ $saving->name }}</strong></h2>
                @if($saving->is_completed)
                    <span class="badge bg-success fs-6">✓ Selesai pada {{ $saving->completed_at->format('j F Y') }}</span>
                @elseif($saving->is_overdue)
                    <span class="badge bg-danger fs-6">⚠️ Terlewat</span>
                @elseif($saving->status === 'urgent')
                    <span class="badge bg-warning fs-6">⏰ Urgent - {{ $saving->days_remaining }} hari lagi</span>
                @endif
            </div>
            @if(!$saving->is_shared && !$saving->is_completed && $saving->progress >= 100)
                <form action="{{ route('savings.mark-completed', $saving->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Tandai sebagai selesai?')">✓ Tandai Selesai</button>
                </form>
            @endif
        </div>

        <div class="row mt-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">🎯 Target</h6>
                        <h5>
                            @if ($saving->is_shared)
                                <span class="text-muted">Tabungan Umum</span>
                            @else
                                Rp {{ number_format($saving->target_amount, 0, ',', '.') }}
                            @endif
                        </h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">💵 Saldo Saat Ini</h6>
                        <h5 class="text-primary">Rp {{ number_format($saving->current_amount, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
            @if(!$saving->is_shared)
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">📊 Progress</h6>
                        <h5 class="{{ $saving->progress >= 100 ? 'text-success' : 'text-info' }}">
                            {{ round($saving->progress, 1) }}%
                        </h5>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Progress Bar with Milestones --}}
    @if(!$saving->is_shared)
    <div class="card mb-4">
        <div class="card-body">
            <label class="form-label">Progress Bar</label>
            <div class="progress" style="height: 30px; position: relative;">
                <div class="progress-bar
                    {{ $saving->progress >= 100 ? 'bg-success' :
                       ($saving->is_overdue ? 'bg-danger' :
                       ($saving->status === 'urgent' ? 'bg-warning' : 'bg-info')) }}"
                    role="progressbar"
                    style="width: {{ min(100, $saving->progress) }}%;"
                    aria-valuenow="{{ $saving->progress }}"
                    aria-valuemin="0"
                    aria-valuemax="100">
                    {{ round($saving->progress, 1) }}%
                </div>
                {{-- Milestone markers --}}
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center;">
                    <div style="width: 25%; border-right: 2px solid rgba(255,255,255,0.5); height: 100%;" title="25%"></div>
                    <div style="width: 25%; border-right: 2px solid rgba(255,255,255,0.5); height: 100%;" title="50%"></div>
                    <div style="width: 25%; border-right: 2px solid rgba(255,255,255,0.5); height: 100%;" title="75%"></div>
                    <div style="width: 25%; height: 100%;" title="100%"></div>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="{{ $saving->last_notified_milestone >= 25 ? 'text-success' : 'text-muted' }}">🌱 25%</small>
                <small class="{{ $saving->last_notified_milestone >= 50 ? 'text-success' : 'text-muted' }}">📈 50%</small>
                <small class="{{ $saving->last_notified_milestone >= 75 ? 'text-success' : 'text-muted' }}">🔥 75%</small>
                <small class="{{ $saving->last_notified_milestone >= 100 ? 'text-success' : 'text-muted' }}">🎉 100%</small>
            </div>
        </div>
    </div>
    @endif

    {{-- Deadline Countdown --}}
    @if($saving->target_date && !$saving->is_shared && !$saving->is_completed)
    <div class="card mb-4 {{ $saving->is_overdue ? 'border-danger' : 'border-primary' }}">
        <div class="card-header {{ $saving->is_overdue ? 'bg-danger text-white' : 'bg-primary text-white' }}">
            <strong>📅 Target Deadline</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>{{ $saving->formatted_target_date }}</h5>
                    @if($saving->countdown)
                        <p class="mb-0 {{ $saving->is_overdue ? 'text-danger' : 'text-muted' }}">
                            {{ $saving->countdown['message'] }}
                        </p>
                    @endif
                </div>
                @if($saving->daily_saving_needed !== null && $saving->daily_saving_needed > 0)
                <div class="col-md-6">
                    <h6 class="text-muted">💡 Tabungan Bulanan Disarankan:</h6>
                    <h4 class="text-info">
                        Rp {{ number_format($saving->daily_saving_needed, 0, ',', '.') }} / bulan
                    </h4>
                    <small class="text-muted">
                        untuk mencapai target tepat waktu
                    </small>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Add Transaction Form --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <strong>➕ Tambah Transaksi</strong>
        </div>
        <div class="card-body">
            <form action="{{ route('savings.transactions.store', $saving->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label for="type" class="form-label">Jenis Transaksi:</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="deposit">✅ Deposit</option>
                            <option value="withdrawal">❌ Withdrawal</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="amount" class="form-label">Jumlah:</label>
                        <input type="number" name="amount" id="amount" class="form-control" placeholder="Masukkan jumlah" required>
                    </div>
                    <div class="col-md-4">
                        <label for="note" class="form-label">Catatan:</label>
                        <input type="text" name="note" id="note" class="form-control" placeholder="Misal: Gaji bulan ini">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">💾 Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Transaction History --}}
    <h4 class="mb-3">📜 Riwayat Transaksi</h4>
    @if ($savingTransactions->isEmpty())
        <div class="alert alert-warning">Belum ada transaksi.</div>
    @else
        <ul class="list-group">
            @foreach ($savingTransactions as $transaction)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge
                            {{ $transaction->type === 'deposit' ? 'bg-success' : ($transaction->type === 'withdrawal' ? 'bg-danger' : 'bg-secondary') }}">
                            {{ $transaction->type === 'deposit' ? '✅' : ($transaction->type === 'withdrawal' ? '❌' : '🔄') }}
                            {{ ucfirst($transaction->type) }}
                        </span>
                        <strong>{{ $transaction->user->name ?? 'Unknown' }}</strong>
                        <span class="fw-bold {{ $transaction->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </span>
                        @if ($transaction->note)
                            – <em class="text-muted">{{ $transaction->note }}</em>
                        @endif
                    </div>
                    <small class="text-muted">{{ $transaction->created_at->format('d M Y H:i') }}</small>
                </li>
            @endforeach
        </ul>
    @endif

    <a href="{{ route('savings.index') }}" class="btn btn-secondary mt-4">⬅️ Kembali ke Savings Tracker</a>
</div>
@endsection
