@php
    $routeName = Route::currentRouteName();
    $backgrounds = [
        'meetings.*' => 'bg-meetings.jpg',
        'travels.*' => 'bg-travels.jpg',
        'savings.*' => 'bg-savings.jpg',
    ];

    $bgImage = 'default.jpg'; // fallback default
    foreach ($backgrounds as $pattern => $image) {
        if (Str::is($pattern, $routeName)) {
            $bgImage = $image;
            break;
        } else {
            $bgImage = 'default.jpg';
        }
    }
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Planner')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
    body {
        background: url('{{ asset('images/' . $bgImage) }}') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Poppins', sans-serif;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

        .content {
            flex: 1;
        }

        .glass-container {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(3px);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid #dee2e6;
        }

        .navbar-brand {
            font-weight: 600;
        }

        .footer {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 16px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
        }

        .card {
            border-radius: 12px;
        }

        .btn {
            border-radius: 8px;
        }

        .hover-shadow:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
            transform: translateY(-4px);
        }

        .transition {
            transition: all 0.2s ease-in-out;
        }

        .table-hover tbody tr:hover {
            background-color: #f9f9f9;
        }

        .btn-sm {
            min-width: 75px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Planner</a>
            
            @auth
                @php
                    $routeName = Route::currentRouteName();
                @endphp
    
                <div class="d-flex flex-wrap gap-2 align-items-center ms-auto">
                    @if (str_contains($routeName, 'meetings') || str_contains($routeName, 'travels') || str_contains($routeName, 'savings'))
                        <span class="me-2 fw-semibold text-primary">
                            👋 Hai, {{ Auth::user()->name }}
                        </span>
                    @endif
    
                    <a class="btn btn-outline-primary" href="{{ route('dashboard') }}">Dashboard</a>
    
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-outline-danger">Logout</button>
                    </form>
                </div>
            @endauth
        </div>
    </nav>    

    <!-- Content -->
    <main class="container py-4 content">
        <div class="glass-container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer mt-auto">
        &copy; {{ date('Y') }} Planner | Dibuat untuk kita❤️
    </footer>

</body>
</html>
