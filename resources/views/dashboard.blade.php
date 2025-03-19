@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <h2 class="mb-4">Selamat Datang, {{ Auth::user()->name }}! 🎉</h2>

    <div class="row">
        <!-- Meeting Planner -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5 class="card-title">Meeting Planner 📅</h5>
                    <p class="card-text">Atur jadwal pertemuan.</p>
                    <a href="{{ route('meetings.index') }}" class="btn btn-primary">Lihat Planner</a>
                </div>
            </div>
        </div>

        <!-- Travel Planner -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5 class="card-title">Travel Planner ✈️</h5>
                    <p class="card-text">Rencanakan perjalanan bersama.</p>
                    <a href="{{ route('travels.index') }}" class="btn btn-primary">Lihat Planner</a>
                </div>
            </div>
        </div>

        <!-- Savings Tracker -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5 class="card-title">Savings Tracker 💰</h5>
                    <p class="card-text">Pantau tabungan bersama.</p>
                    <a href="{{  route('savings.index') }}" class="btn btn-primary">Lihat Tracker</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
