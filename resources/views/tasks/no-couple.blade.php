@extends('layouts.app')

@section('title', 'Tasks - Our Plan')

@section('content')
<div class="tasks-no-couple">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center py-5">
            <i class="bi bi-people display-1 text-muted mb-4"></i>
            <h3>Belum Terhubung dengan Pasangan</h3>
            <p class="text-muted mb-4">Anda perlu terhubung dengan pasangan terlebih dahulu untuk mengakses fitur Tasks.</p>
            <a href="{{ route('pairing.status') }}" class="btn btn-primary">
                <i class="bi bi-link-45deg"></i> Hubungkan Sekarang
            </a>
        </div>
    </div>
</div>
@endsection
