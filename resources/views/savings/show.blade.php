@extends('layouts.app')

@section('title', 'Detail Tabungan')

@section('content')
<div class="container">
    <h2>💰 {{ $saving->name }}</h2>

    <p><strong>Target:</strong> Rp {{ number_format($saving->target_amount, 0, ',', '.') }}</p>
    <p><strong>Saldo Saat Ini:</strong> Rp {{ number_format($saving->current_amount, 0, ',', '.') }}</p>

    <h3>➕ Tambah Transaksi</h3>
    <form action="{{ route('savings.transactions.store', $saving->id) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="type">Jenis Transaksi:</label>
            <select name="type" id="type" class="form-control" required>
                <option value="deposit">Deposit</option>
                <option value="withdrawal">Withdrawal</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="amount">Jumlah:</label>
            <input type="number" name="amount" id="amount" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="note">Catatan:</label>
            <input type="text" name="note" id="note" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
    </form>

    <h3>📜 Riwayat Transaksi</h3>
    <ul>
        @if (empty($saving->transactions))
            <li>Tidak ada transaksi.</li>
        @else
            @foreach ($saving->transactions as $transaction)
                <li>{{ $transaction->type }} Rp {{ number_format($transaction->amount, 0, ',', '.') }} - {{ $transaction->note }}</li>
            @endforeach
        @endif
    </ul>
</div>
@endsection
