@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard-page">
    <div class="container py-5">
        <h1 class="fw-bold display-5 text-center mb-2">
            Selamat Datang, <span class="text-primary">{{ Auth::user()->name }}</span>! 🎉
        </h1>
        <p class="text-center text-muted mb-5">
            Yuk, atur rencana dan impian kalian bersama di sini ✨
        </p>

        <!-- Core Features -->
        <h5 class="mb-3 text-muted">💕 Core Features</h5>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
            <!-- Timeline -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="display-6">📰</span>
                        </div>
                        <h5 class="card-title">Timeline</h5>
                        <p class="card-text text-muted small">Bagikan momen bersama.</p>
                        <a href="{{ route('timeline.index') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-image"></i> Timeline
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mood Check-In -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="display-6">😊</span>
                        </div>
                        <h5 class="card-title">Daily Mood</h5>
                        <p class="card-text text-muted small">Cek-in mood harian.</p>
                        <a href="{{ route('mood.index') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-emoji-smile"></i> Mood
                        </a>
                    </div>
                </div>
            </div>

            <!-- Questions -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="display-6">❓</span>
                        </div>
                        <h5 class="card-title">Question of the Day</h5>
                        <p class="card-text text-muted small">Pertanyaan harian.</p>
                        <a href="{{ route('questions.index') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-chat-dots"></i> Questions
                        </a>
                    </div>
                </div>
            </div>

            <!-- Goals & Tasks -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="display-6">🎯</span>
                        </div>
                        <h5 class="card-title">Goals & Tasks</h5>
                        <p class="card-text text-muted small">Wujudkan impian bersama.</p>
                        <a href="{{ route('goals.index') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-list-check"></i> Goals
                        </a>
                    </div>
                </div>
            </div>

            <!-- Missing You -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 hover-shadow transition border-danger">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="display-6">💕</span>
                        </div>
                        <h5 class="card-title">Missing You</h5>
                        <p class="card-text text-muted small">Kirim rindu ke pasangan.</p>
                        <a href="{{ route('missing-you.index') }}" class="btn btn-danger w-100">
                            <i class="bi bi-heart"></i> Send Love
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Planning Features -->
        <h5 class="mb-3 text-muted">📅 Planning Features</h5>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <!-- Meeting Planner -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="display-6">📅</span>
                        </div>
                        <h5 class="card-title">Pertemuan</h5>
                        <p class="card-text text-muted small">Atur jadwal pertemuan.</p>
                        <a href="{{ route('meetings.index') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-calendar-event"></i> Meetings
                        </a>
                    </div>
                </div>
            </div>

            <!-- Travel Planner -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="display-6">✈️</span>
                        </div>
                        <h5 class="card-title">Perjalanan</h5>
                        <p class="card-text text-muted small">Rencanakan kunjungan.</p>
                        <a href="{{ route('travels.index') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-airplane"></i> Travels
                        </a>
                    </div>
                </div>
            </div>

            <!-- Savings Tracker -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <span class="display-6">💰</span>
                        </div>
                        <h5 class="card-title">Tabungan</h5>
                        <p class="card-text text-muted small">Pantau tabungan bersama.</p>
                        <a href="{{ route('savings.index') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-piggy-bank"></i> Savings
                        </a>
                    </div>
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
    .dashboard-page {
        background: transparent !important;
    }
</style>
@endsection
