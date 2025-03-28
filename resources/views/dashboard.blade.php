@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container py-5">
    <h1 class="fw-bold display-5 text-center mb-2">
        Selamat Datang, <span class="text-primary">{{ Auth::user()->name }}</span>! 🎉
    </h1>
    <p class="text-center text-muted mb-5">
        Yuk, atur rencana dan impian kalian bersama di sini ✨
    </p>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <!-- Meeting Planner -->
        <div class="col">
            <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                <div class="card-body text-center">
                    <h5 class="card-title">📅 Meeting Planner</h5>
                    <p class="card-text text-muted">Atur jadwal pertemuan.</p>
                    <a href="{{ route('meetings.index') }}" class="btn btn-primary w-100">Lihat Planner</a>
                </div>
            </div>
        </div>

        <!-- Travel Planner -->
        <div class="col">
            <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                <div class="card-body text-center">
                    <h5 class="card-title">✈️ Travel Planner</h5>
                    <p class="card-text text-muted">Rencanakan perjalanan bersama.</p>
                    <a href="{{ route('travels.index') }}" class="btn btn-primary w-100">Lihat Planner</a>
                </div>
            </div>
        </div>

        <!-- Savings Tracker -->
        <div class="col">
            <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                <div class="card-body text-center">
                    <h5 class="card-title">💰 Savings Tracker</h5>
                    <p class="card-text text-muted">Pantau tabungan bersama.</p>
                    <a href="{{ route('savings.index') }}" class="btn btn-primary w-100">Lihat Tracker</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        transform: translateY(-3px);
    }
    .transition {
        transition: all 0.2s ease-in-out;
    }
</style>
@endsection
