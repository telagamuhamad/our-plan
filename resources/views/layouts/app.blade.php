<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Planner')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* Agar footer tetap di bawah */
        html, body {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .content {
            flex: 1;
        }
        .footer {
            background: #f8f9fa;
            padding: 15px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Planner</a>
            <div class="d-flex">
                @auth
                    <a class="btn btn-outline-primary me-2" href="{{ route('dashboard') }}">Dashboard</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-danger">Logout</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Konten -->
    <div class="container mt-4 content">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="footer">
        {{-- &copy; {{ date('Y') }} Planner | Dibuat dengan ❤️ oleh Kamu & Pasangan --}}
        &copy; {{ date('Y') }} Planner | Dibuat dengan tangan oleh Kamu
    </footer>
</body>
</html>
