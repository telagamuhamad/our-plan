@extends('layouts.app')

@section('title', 'Transfer Saldo')

@section('content')
<div class="container">
    <h2>🔄 Transfer Saldo Antar Tabungan</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('savings.transfer') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="source_saving_id" class="form-label">Tabungan Sumber</label>
            <select name="source_saving_id" id="source_saving_id" class="form-select" required>
                <option value="">Pilih tabungan sumber</option>
                @foreach ($savings as $saving)
                    <option value="{{ $saving->id }}">{{ $saving->name }} (Rp {{ number_format($saving->current_amount, 0, ',', '.') }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="target_saving_id" class="form-label">Tabungan Tujuan</label>
            <select name="target_saving_id" id="target_saving_id" class="form-select" required>
                <option value="">Pilih tabungan tujuan</option>
                @foreach ($savings as $saving)
                    <option value="{{ $saving->id }}">{{ $saving->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Jumlah Saldo yang Ditransfer (Rp)</label>
            <input type="number" name="amount" id="amount" class="form-control" min="1" required>
        </div>

        <button type="submit" class="btn btn-success">🔄 Transfer</button>
    </form>

    <a href="{{ route('savings.index') }}" class="btn btn-secondary mt-3">⬅️ Kembali</a>
</div>
@endsection
