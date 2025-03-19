@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="container text-center">
    <h1>Selamat Datang di Planner ❤️</h1>
    <p>Gunakan planner ini untuk mengatur jadwal pertemuan dan perjalanan bersama.</p>
    <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
</div>
@endsection
