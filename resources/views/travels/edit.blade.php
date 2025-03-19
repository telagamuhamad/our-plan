@extends('layouts.app')

@section('title', 'Edit Rencana Perjalanan')

@section('content')
<div class="container">
    <h2>✏️ Edit Rencana Perjalanan</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('travels.update', $travel->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="destination" class="form-label">Destinasi</label>
            <input type="text" name="destination" id="destination" class="form-control" value="{{ $travel->destination }}" required @if($travel->completed) readonly @endif>
        </div>

        <div class="mb-3">
            <label for="visit_date" class="form-label">Tanggal Kunjungan</label>
            <input type="date" name="visit_date" id="visit_date" class="form-control" value="{{ $travel->visit_date }}" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="completed" class="form-control" disabled>
                <option value="0" {{ !$travel->completed ? 'selected' : '' }}>Belum Selesai</option>
                <option value="1" {{ $travel->completed ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>

        <button type="submit" class="btn btn-warning" @if($travel->completed) disabled @endif>Update</button>
    </form>
</div>
@endsection
