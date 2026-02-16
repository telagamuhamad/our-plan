@extends('layouts.app')

@section('title', 'Buat Kode Undangan')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="text-center mb-4">
                <h2 class="fw-bold">Mulai Perjalanan Bersama</h2>
                <p class="text-muted mb-0">Buat kode undangan untuk dibagikan ke pasangan</p>
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

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pairing.store-invite') }}">
                        @csrf

                        <div class="text-center mb-4">
                            <div class="display-1 mb-3">💌</div>
                            <p class="text-muted">
                                Setelah membuat kode undangan, bagikan kode tersebut ke pasangan Anda.
                                Mereka bisa memasukkan kode tersebut untuk terhubung dengan Anda.
                            </p>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            Generate Kode Undangan
                        </button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted mb-2">Sudah punya kode dari pasangan?</p>
                <a href="{{ route('pairing.join') }}" class="btn btn-outline-primary">
                    Masukkan Kode Undangan
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
</style>
@endsection
