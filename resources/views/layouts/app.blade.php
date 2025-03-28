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
        /* Font & Layout */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fc;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .content {
            flex: 1;
        }

        /* Navbar */
        .navbar {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
        }

        .navbar-brand {
            font-weight: 600;
        }

        /* Footer */
        .footer {
            background-color: #f8f9fa;
            padding: 16px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
        }

        /* Card improvement (optional global) */
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
            <div class="d-flex flex-wrap gap-2">
                @auth
                    <a class="btn btn-outline-primary" href="{{ route('dashboard') }}">Dashboard</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-danger">Logout</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main class="container py-4 content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer mt-auto">
        {{-- &copy; {{ date('Y') }} Planner | Dibuat dengan ❤️ oleh Kamu & Pasangan --}}
        &copy; {{ date('Y') }} Planner | Dibuat dengan tangan kamu
    </footer>

</body>
</html>
