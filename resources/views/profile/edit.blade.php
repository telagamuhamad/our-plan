@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="fw-bold mb-4">👤 Edit Profil</h2>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @error('current_password')
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @enderror

            <!-- Profile Picture Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">📷 Foto Profil</h5>
                    <div class="d-flex align-items-center">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}"
                                 alt="{{ $user->name }}"
                                 class="rounded-circle me-4"
                                 style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center me-4"
                                 style="width: 100px; height: 100px; font-size: 2.5rem; color: white;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif

                        <div class="flex-grow-1">
                            <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="d-inline">
                                @csrf
                                <input type="file" name="avatar" id="avatarInput" accept="image/*" class="d-none" onchange="this.form.submit()">
                                <button type="button" class="btn btn-outline-primary me-2" onclick="document.getElementById('avatarInput').click()">
                                    <i class="bi bi-upload"></i> Upload Foto
                                </button>
                            </form>

                            @if($user->avatar_url)
                                <form method="POST" action="{{ route('profile.avatar.remove') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Hapus foto profil?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">📝 Informasi Profil</h5>
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                   id="username" name="username" value="{{ old('username', $user->username) }}"
                                   pattern="[a-zA-Z0-9_]+" title="Hanya huruf, angka, dan underscore" required>
                            <div class="form-text">Username digunakan untuk login. Hanya huruf, angka, dan underscore.</div>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">🔒 Ganti Password</h5>
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required minlength="6">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                                   id="password_confirmation" name="password_confirmation" required minlength="6">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key"></i> Ganti Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">ℹ️ Info Akun</h5>
                    <div class="row text-muted small">
                        <div class="col-sm-4">Terdaftar sejak:</div>
                        <div class="col-sm-8">{{ $user->created_at->translatedFormat('d F Y') }}</div>
                    </div>
                    @if($user->timezone)
                    <div class="row text-muted small mt-2">
                        <div class="col-sm-4">Timezone:</div>
                        <div class="col-sm-8">{{ $user->timezone }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 12px;
    }

    .rounded-circle.bg-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }

    input[type="file"].d-none:focus {
        outline: none;
    }
</style>
@endsection
