@extends('layouts.app')

@section('title', 'Gabung dengan Pasangan')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="text-center mb-4">
                <h2 class="fw-bold">Terhubung dengan Pasangan</h2>
                <p class="text-muted mb-0">Masukkan kode undangan yang Anda terima</p>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pairing.join') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="invite_code" class="form-label fw-semibold">Kode Undangan</label>
                            <input type="text"
                                   name="invite_code"
                                   id="invite_code"
                                   class="form-control form-control-lg text-center text-uppercase"
                                   placeholder="ABC123"
                                   maxlength="6"
                                   style="font-size: 2rem; letter-spacing: 0.5rem; font-weight: 600;"
                                   required
                                   autofocus
                                   value="{{ old('invite_code') }}">
                            @error('invite_code')
                                <div class="text-danger mt-2 small">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            Hubungkan
                        </button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted mb-2">Belum punya kode?</p>
                <a href="{{ route('pairing.create-invite') }}" class="btn btn-outline-primary">
                    Buat Kode Undangan
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 12px;
    }

    .btn-primary {
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
    }

    .btn-outline-primary {
        border-radius: 10px;
        padding: 0.6rem 1.5rem;
        font-weight: 500;
    }

    #invite_code::placeholder {
        font-size: 1.5rem;
        letter-spacing: 0.3rem;
        opacity: 0.3;
    }
</style>

<script>
document.getElementById('invite_code').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase();
});
</script>
@endsection
