<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBIMS-PRO - @yield('title', 'Dashboard')</title>

    <!-- Laravel way to include CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome-free/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            overflow: hidden;
            height: 100vh;
        }

        /* Fixed Topbar */
        .topbar-fixed {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: white;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Main layout container */
        .layout-container {
            display: flex;
            margin-top: 70px;
            height: calc(100vh - 70px);
            position: relative;
        }

        /* Fixed Sidebar */
        .sidebar-fixed {
            position: fixed;
            top: 70px;
            left: 0;
            width: 250px;
            height: calc(100vh - 70px);
            z-index: 999;
            overflow-y: auto;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Scrollable Content */
        .content-scrollable {
            flex: 1;
            margin-left: 250px;
            height: 100%;
            overflow-y: auto;
            background: #f5f5f5;
        }

        /* Content wrapper for padding */
        .content-wrapper {
            padding: 24px;
        }

        /* Update existing sidebar styles */
        .sidebar {
            width: 100%;
            min-height: auto;
            background: transparent;
            padding: 20px 0;
        }

        /* Custom scrollbar for sidebar */
        .sidebar-fixed::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-fixed::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .sidebar-fixed::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
        }

        .sidebar-fixed::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }

        /* Mobile menu toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            align-items: center;
            justify-content: center;
        }

        .mobile-menu-toggle i {
            font-size: 24px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .sidebar-fixed {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 280px;
            }

            .sidebar-fixed.active {
                transform: translateX(0);
            }

            .content-scrollable {
                margin-left: 0;
            }

            .mobile-menu-toggle {
                display: flex;
            }

            .content-wrapper {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Fixed Topbar -->
    <div class="topbar-fixed">
        @include('layouts.topbar')
    </div>

    <div class="layout-container">
        <!-- Fixed Sidebar -->
        <div class="sidebar-fixed">
            @include('layouts.sidebar')
        </div>

        <!-- Scrollable Content -->
        <main class="content-scrollable">
            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    @include('layouts.footer')

    <!-- Laravel way to include JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.querySelector('.sidebar-fixed');

            if (menuToggle && sidebar) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });

                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(event) {
                    if (window.innerWidth <= 768) {
                        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                            sidebar.classList.remove('active');
                        }
                    }
                });
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    document.querySelector('.sidebar-fixed')?.classList.remove('active');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
