@php
    $routeName = Route::currentRouteName();
    $backgrounds = [
        'pairing.*' => 'bg-pairing.jpg',
        'timeline.*' => 'bg-meetings.jpg', // using meetings as fallback
        'mood.*' => 'bg-meetings.jpg',
        'notifications.*' => 'bg-meetings.jpg',
        'missing-you.*' => 'bg-meetings.jpg',
        'questions.*' => 'bg-meetings.jpg',
        'goals.*' => 'bg-savings.jpg', // using savings as fallback
        'tasks.*' => 'bg-savings.jpg',
        'meetings.*' => 'bg-meetings.jpg',
        'travels.*' => 'bg-travels.jpg',
        'savings.*' => 'bg-savings.jpg',
        'profile.*' => 'bg-meetings.jpg',
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
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            position: relative;
            z-index: 1;
        }

        /* Fix modal z-index issue */
        .modal {
            z-index: 1060;
        }

        .modal-backdrop {
            z-index: 1055;
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

        /* Timeline post images */
        .post-content img {
            max-width: 100%;
            max-height: 500px;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
        }

        /* Fix reaction dropdown z-index */
        .reaction-dropdown {
            z-index: 1050 !important;
        }

        .post-card {
            position: relative;
            z-index: 1;
        }

        .post-card:hover {
            z-index: 2;
        }

        /* Fix italic text in comment count */
        .post-card small {
            font-style: normal;
        }

        /* Ensure modals can escape glass-container */
        main {
            overflow: visible;
        }

        .glass-container {
            overflow: visible;
        }

        /* Notification bell */
        .notification-bell {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 10px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Center badge text */
        .badge.rounded-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Compact navbar buttons */
        .navbar .btn {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .navbar .greeting-text {
            display: none;
        }

        @media (min-width: 992px) {
            .navbar .greeting-text {
                display: inline;
            }
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
                    $userHasActiveCouple = Auth::user()->hasActiveCouple();
                @endphp

                <div class="d-flex flex-wrap gap-2 align-items-center ms-auto">
                    <!-- Profile Dropdown -->
                    <div class="dropdown">
                        <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            @if(Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.875rem; color: white;">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                            @endif
                            <span class="fw-semibold text-primary d-none d-lg-block">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @php
                                $displayUsername = Auth::user()->username;
                            @endphp
                            <li><h6 class="dropdown-header"><span>@</span>{{ $displayUsername }}</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person"></i> Edit Profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>

                    @if($userHasActiveCouple)
                        <!-- Notification Bell -->
                        <a class="btn btn-outline-primary notification-bell position-relative" href="{{ route('notifications.index') }}">
                            🔔
                            @php
                                $unreadCount = Auth::user()->unreadNotificationsCount();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="badge bg-danger notification-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </a>

                        <a class="btn btn-primary" href="{{ route('dashboard') }}">
                            <i class="bi bi-grid"></i> Dashboard
                        </a>

                        <a href="#" class="btn btn-outline-warning leave-pairing-btn" data-url="{{ route('pairing.leave') }}">
                            <i class="bi bi-heartbreak"></i> Unpair
                        </a>
                    @else
                        <a class="btn btn-outline-primary" href="{{ route('pairing.status') }}">
                            <i class="bi bi-link-45deg"></i> Pairing
                        </a>
                    @endif
                </div>
            @endauth
        </div>
    </nav>

    <!-- Hidden logout form -->
    @auth
        <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
            @csrf
        </form>

        <!-- Hidden leave pairing form -->
        <form id="leave-pairing-form" method="POST" action="{{ route('pairing.leave') }}" class="d-none">
            @csrf
        </form>
    @endauth    

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

    <!-- Modals Stack (outside glass-container) -->
    @stack('modals')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Leave pairing handler -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const leavePairingBtn = document.querySelector('.leave-pairing-btn');
        const leavePairingForm = document.getElementById('leave-pairing-form');
        if (leavePairingBtn && leavePairingForm) {
            leavePairingBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Apakah Anda yakin ingin keluar dari pasangan?')) {
                    leavePairingForm.submit();
                }
            });
        }
    });
    </script>

    @stack('scripts')

    @auth
        @if(Auth::user()->hasActiveCouple())
    <script>
    // Check for new notifications every 30 seconds
    let lastUnreadCount = {{ Auth::user()->unreadNotificationsCount() }};

    function checkNotifications() {
        fetch('{{ route('notifications.unread-count') }}', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const bell = document.querySelector('.notification-bell');
                if (bell) {
                    let badge = bell.querySelector('.notification-badge');

                    if (data.count > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'badge bg-danger notification-badge';
                            bell.appendChild(badge);
                        }
                        badge.textContent = data.count > 9 ? '9+' : data.count;
                    } else if (badge) {
                        badge.remove();
                    }
                }

                // If count increased, show a subtle indication
                if (data.count > lastUnreadCount) {
                    lastUnreadCount = data.count;
                }
            }
        })
        .catch(error => console.error('Notification check failed:', error));
    }

    // Initial check after page load
    setTimeout(checkNotifications, 2000);

    // Check every 30 seconds
    setInterval(checkNotifications, 30000);
    </script>
        @endif
    @endauth

</body>
</html>
