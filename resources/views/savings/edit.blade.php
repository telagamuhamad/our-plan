@extends('layouts.app')

@section('title', 'Edit Tabungan')

@section('content')
<div class="container">
    <h2>✏️ Edit Tabungan</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('savings.update', $saving->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nama Tabungan</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $saving->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="target_amount" class="form-label">Target Tabungan (Rp)</label>
            <input type="number" name="target_amount" id="target_amount" class="form-control @error('target_amount') is-invalid @enderror" value="{{ old('target_amount', $saving->target_amount) }}" required>
            @error('target_amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="is_shared" class="form-label">Tabungan Umum</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_shared" id="is_shared" value="1" 
                    {{ old('is_shared', $saving->is_shared) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_shared">
                    Ini adalah tabungan bersama tanpa tujuan spesifik
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-warning">Update</button>
    </form>

    <a href="{{ route('savings.index') }}" class="btn btn-secondary mt-3">⬅️ Kembali</a>
</div>
@endsection
