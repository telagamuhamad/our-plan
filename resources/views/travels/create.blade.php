@extends('layouts.app')

@section('title', 'Tambah Rencana Perjalanan')

@section('content')
<div class="container">
    <h2>📝 Tambah Rencana Perjalanan</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('travels.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="destination" class="form-label">Destinasi</label>
            <input type="text" name="destination" id="destination" class="form-control" required>
        </div>

        {{-- <div class="mb-3">
            <label for="visit_date" class="form-label">Tanggal Kunjungan</label>
            <input type="date" name="visit_date" id="visit_date" class="form-control">
        </div> --}}

        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
