@extends('layouts.app')

@section('title', 'Tambah Pertemuan')

@section('content')
<div class="container">
    <h2>Tambah Pertemuan</h2>
    
    <!-- Notifikasi Error -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('meetings.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="travelling_user_id" class="form-label">Siapa yang Berangkat?</label>
            <input type="text" id="travelling_user_name" class="form-control" value="{{ auth()->user()->name }}" disabled>
            <input type="hidden" name="travelling_user_id" value="{{ auth()->user()->id }}">
        </div>

        <div class="mb-3">
            <label for="meeting_date" class="form-label">Tanggal Pertemuan</label>
            <input type="date" name="meeting_date" id="meeting_date" class="form-control @error('meeting_date') is-invalid @enderror" value="{{ old('meeting_date') }}" required>
            @error('meeting_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Lokasi</label>
            <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}">
            @error('location')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="note" class="form-label">Catatan</label>
            <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror">{{ old('note') }}</textarea>
            @error('note')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Checkbox untuk Status Kesiapan Perjalanan -->
        <div class="mb-3">
            <label class="form-label">Status Kesiapan Perjalanan</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_departure_transport_ready" id="departure_ready" value="1" {{ old('is_departure_transport_ready') ? 'checked' : '' }}>
                <label class="form-check-label" for="departure_ready">
                    Transportasi Keberangkatan Siap
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_return_transport_ready" id="return_ready" value="1" {{ old('is_return_transport_ready') ? 'checked' : '' }}>
                <label class="form-check-label" for="return_ready">
                    Transportasi Kembali Siap
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_rest_place_ready" id="rest_place_ready" value="1" {{ old('is_rest_place_ready') ? 'checked' : '' }}>
                <label class="form-check-label" for="rest_place_ready">
                    Tempat Istirahat Siap
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
