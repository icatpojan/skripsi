<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Aplikasi Pelaporan Sampah') }} - @yield('title', 'Dashboard')</title>

    <!-- Favicon and App Icons -->
    <link rel="icon" type="image/png" href="{{ asset('img/waste.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/waste.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/waste.png') }}">
    <meta name="msapplication-TileImage" content="{{ asset('img/waste.png') }}">
    <meta name="msapplication-TileColor" content="#007bff">
    <meta name="theme-color" content="#007bff">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/fontawesome.css') }}" rel="stylesheet">

    @stack('styles')
    <style>
        .modal {
            backdrop-filter: blur(6px);
            background-color: rgba(0, 0, 0, 0.3);
            z-index: 5050 !important;
        }

        /* Alert Container Styling */
        .alert-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            width: 100%;
        }

        .alert-container .alert {
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .alert-container .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: currentColor;
            opacity: 0.3;
        }

        .alert-container .alert .d-flex {
            align-items: center;
        }

        .alert-container .alert i {
            font-size: 16px;
            min-width: 20px;
        }

        .alert-container .alert .close {
            position: absolute;
            top: 8px;
            right: 12px;
            background: none;
            border: none;
            font-size: 18px;
            font-weight: bold;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .alert-container .alert .close:hover {
            opacity: 1;
        }

        /* Auto-dismiss animation */
        .alert.auto-dismiss {
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .alert.fade-out {
            animation: slideOutRight 0.3s ease-in forwards;
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Alert type specific styling */
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .alert-container {
                top: 70px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }

        /* Home page specific */
        .home-page {
            padding-top: 0;
        }

        /* Hanya sembunyikan container langsung di main, bukan di section */
        .home-page > .container {
            display: none;
        }

        .home-page #alertContainer {
            position: fixed;
            top: 100px;
            z-index: 1001;
        }

        /* Auth pages - hide navbar */
        .auth-page #mainNavbar {
            display: none;
        }

        .auth-page main {
            padding-top: 0;
        }

        /* Footer Styles */
        .main-footer {
            background: #1a1a1a;
            color: #ffffff;
            padding: 4rem 0 2rem;
            margin-top: 0;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 4rem;
            margin-bottom: 3rem;
        }

        .footer-brand {
            max-width: 400px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .footer-logo img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .footer-logo span {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .footer-description {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.8;
            font-size: 0.95rem;
            margin: 0;
        }

        .footer-links {
            display: flex;
            gap: 3rem;
        }

        .footer-column h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-column ul li {
            margin-bottom: 0.75rem;
        }

        .footer-column ul li a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 0.95rem;
        }

        .footer-column ul li a:hover {
            color: #ffffff;
            text-decoration: none;
        }

        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .footer-bottom p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            margin: 0;
        }

        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .footer-links {
                flex-direction: column;
                gap: 2rem;
            }

            .main-footer {
                padding: 3rem 0 1.5rem;
            }
        }

    </style>
</head>
<body>
    <div id="app">
        <!-- Navigation -->
        @if(!request()->is('login') && !request()->is('register'))
        <nav class="navbar navbar-expand-lg navbar-dark" id="mainNavbar">
            <div class="container">
                <a class="mx-3 navbar-brand d-flex align-items-center" href="{{ auth()->check() ? route('dashboard') : url('/') }}">
                    <img src="{{ asset('img/waste.png') }}" alt="Waste App Icon" class="mr-2" style="width: 32px; height: 32px;">
                    <strong>{{ config('app.name', 'Aplikasi Pelaporan Sampah') }}</strong>
                </a>

                <button class="mx-3 navbar-toggler bg-secondary" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto text-center">
                        <!-- Authentication Links -->
                        @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt mr-1"></i> {{ __('Login') }}
                            </a>
                        </li>
                        @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus mr-1"></i> {{ __('Register') }}
                            </a>
                        </li>
                        @endif
                        @else
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#" onclick="showLogoutConfirmation()">
                                <i class="fas fa-sign-out-alt mr-1"></i> {{ __('Logout') }}
                            </a>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        @else
        <nav class="d-none" id="mainNavbar"></nav>
        @endif

        <!-- Main Content -->
        <main class="{{ request()->is('/') ? 'home-page' : '' }}">
            <!-- Dynamic Alert Container -->
            <div id="alertContainer" class="alert-container">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show auto-dismiss" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                
                @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show auto-dismiss" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                
                @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show auto-dismiss" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
            </div>
            
            @yield('content')
        </main>

        <!-- Footer -->
        @if(!request()->is('login') && !request()->is('register'))
        <footer class="main-footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-brand">
                        <div class="footer-logo">
                            <img src="{{ asset('img/waste.png') }}" alt="Waste App Icon">
                            <span>Aplikasi Pelaporan Sampah</span>
                        </div>
                        <p class="footer-description">Mari bersama-sama menjaga lingkungan kita tetap bersih dan sehat untuk masa depan yang lebih baik.</p>
                    </div>
                    <div class="footer-links">
                        <div class="footer-column">
                            <h4>Navigasi</h4>
                            <ul>
                                <li><a href="{{ url('/') }}">Beranda</a></li>
                                @guest
                                <li><a href="{{ route('login') }}">Masuk</a></li>
                                <li><a href="{{ route('register') }}">Daftar</a></li>
                                @else
                                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li><a href="{{ route('waste-reports.create') }}">Laporkan Sampah</a></li>
                                @endguest
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
        @endif
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-sign-out-alt mr-2"></i>Konfirmasi Logout
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-question-circle fa-3x text-danger mb-3"></i>
                        <h5>Anda yakin ingin keluar?</h5>
                        <p class="text-muted">
                            Anda akan keluar dari aplikasi dan harus login kembali untuk mengakses dashboard.
                        </p>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i>Ya, Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('scripts')

    <script>
        function showLogoutConfirmation() {
            $('#logoutConfirmationModal').modal('show');
        }

        // Auto-dismiss alerts after 3 seconds
        $(document).ready(function() {
            // Auto-dismiss existing alerts
            $('.auto-dismiss').each(function() {
                const alert = $(this);
                setTimeout(function() {
                    alert.addClass('fade-out');
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 3000);
            });

            // Use MutationObserver for dynamically created alerts
            const alertContainer = document.getElementById('alertContainer');
            if (alertContainer) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList') {
                            mutation.addedNodes.forEach(function(node) {
                                if (node.nodeType === 1 && node.classList && node.classList.contains('alert')) {
                                    const alert = $(node);
                                    setTimeout(function() {
                                        alert.addClass('fade-out');
                                        setTimeout(function() {
                                            alert.remove();
                                        }, 300);
                                    }, 3000);
                                }
                            });
                        }
                    });
                });

                observer.observe(alertContainer, {
                    childList: true
                    , subtree: true
                });
            }
        });

        // Enhanced showAlert function for dynamic alerts
        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' :
                type === 'error' ? 'alert-danger' :
                type === 'warning' ? 'alert-warning' : 'alert-info';

            const icon = type === 'success' ? 'fas fa-check-circle' :
                type === 'error' ? 'fas fa-exclamation-circle' :
                type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';

            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show auto-dismiss" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="${icon} mr-2"></i>
                        <span>${message}</span>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;

            // Remove any existing alerts first
            $('#alertContainer').empty();

            // Add new alert
            $('#alertContainer').html(alertHtml);

            // Auto-dismiss after 3 seconds
            setTimeout(function() {
                $('#alertContainer .alert').addClass('fade-out');
                setTimeout(function() {
                    $('#alertContainer .alert').remove();
                }, 300);
            }, 3000);
        }

        // Navbar scroll effect
        $(window).scroll(function() {
            const navbar = $('#mainNavbar');
            if ($(window).scrollTop() > 50) {
                navbar.addClass('navbar-scrolled');
            } else {
                navbar.removeClass('navbar-scrolled');
            }
        });

    </script>
</body>
</html>
