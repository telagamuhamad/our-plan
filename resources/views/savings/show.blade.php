@extends('layouts.app')

@section('title', 'Detail Tabungan')

@section('content')
<div class="container">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-4">
        <h2>💰 <strong>{{ $saving->name }}</strong></h2>
        <p><strong>🎯 Target:</strong>
            @if ($saving->is_shared)
                <span class="text-muted">-</span>
            @else
                Rp {{ number_format($saving->target_amount, 0, ',', '.') }}
            @endif
        </p>
        <p><strong>💵 Saldo Saat Ini:</strong> Rp {{ number_format($saving->current_amount, 0, ',', '.') }}</p>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <strong>➕ Tambah Transaksi</strong>
        </div>
        <div class="card-body">
            <form action="{{ route('savings.transactions.store', $saving->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="type" class="form-label">Jenis Transaksi:</label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="deposit">Deposit</option>
                        <option value="withdrawal">Withdrawal</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="amount" class="form-label">Jumlah:</label>
                    <input type="number" name="amount" id="amount" class="form-control" placeholder="Masukkan jumlah" required>
                </div>
                <div class="mb-3">
                    <label for="note" class="form-label">Catatan:</label>
                    <input type="text" name="note" id="note" class="form-control" placeholder="Misal: Gaji bulan ini">
                </div>
                <button type="submit" class="btn btn-success">💾 Simpan</button>
            </form>
        </div>
    </div>

    <h4 class="mb-3">📜 Riwayat Transaksi</h4>
    @if ($saving->transactions->isEmpty())
        <div class="alert alert-warning">Belum ada transaksi.</div>
    @else
        <ul class="list-group">
            @foreach ($saving->transactions as $transaction)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge 
                            {{ $transaction->type === 'deposit' ? 'bg-success' : ($transaction->type === 'withdrawal' ? 'bg-danger' : 'bg-secondary') }}">
                            {{ ucfirst($transaction->type) }}
                        </span>
                        Rp {{ number_format($transaction->amount, 0, ',', '.') }} 
                        @if ($transaction->note) 
                            – <em>{{ $transaction->note }}</em>
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
