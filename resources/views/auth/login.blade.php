<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>SBIMS-PRO - Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-y: auto; /* Make body scrollable */
        }

        /* Animated background shapes - adjusted for mobile */
        body::before {
            content: '';
            position: fixed; /* Changed to fixed to stay in place while scrolling */
            width: 1500px;
            height: 1500px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            top: -800px;
            right: -400px;
            animation: float 20s infinite ease-in-out;
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            width: 1200px;
            height: 1200px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.03) 100%);
            bottom: -600px;
            left: -300px;
            animation: float 25s infinite ease-in-out reverse;
            pointer-events: none;
            z-index: 0;
        }

        @media (max-width: 768px) {
            body::before {
                width: 1000px;
                height: 1000px;
                top: -500px;
                right: -300px;
            }

            body::after {
                width: 800px;
                height: 800px;
                bottom: -400px;
                left: -200px;
            }
        }

        @media (max-width: 480px) {
            body::before {
                width: 600px;
                height: 600px;
                top: -300px;
                right: -200px;
            }

            body::after {
                width: 500px;
                height: 500px;
                bottom: -250px;
                left: -150px;
            }
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(20px, -20px) rotate(120deg); }
            66% { transform: translate(-15px, 15px) rotate(240deg); }
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            margin: auto;
            position: relative;
            z-index: 1;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            padding: 40px;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            margin-bottom: 20px;
            position: relative;
        }

        .logo-img {
            height: 120px;
            width: auto;
            max-width: 100%;
            margin: 0 auto;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
            transition: transform 0.3s;
        }

        .logo-img:hover {
            transform: scale(1.05);
        }

        .login-header h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 8px;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            word-break: break-word;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            word-break: break-word;
        }

        .login-header p::before,
        .login-header p::after {
            content: '•';
            margin: 0 8px;
            color: #667eea;
        }

        .alert-error {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: #991b1b;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid #fecaca;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.5s ease-in-out;
            word-break: break-word;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .alert-error i {
            font-size: 18px;
            color: #dc2626;
            flex-shrink: 0;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #4b5563;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .form-group .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-group input {
            width: 100%;
            padding: 15px 18px 15px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            background: #f9fafb;
            -webkit-appearance: none;
            appearance: none;
        }

        /* Special padding for password field */
        #password {
            padding-right: 45px !important;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(102, 126, 234, 0.2);
        }

        .form-group input::placeholder {
            color: #9ca3af;
            font-size: 14px;
        }

        /* Left icon */
        .input-icon-left {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
            transition: color 0.3s;
            z-index: 1;
            pointer-events: none;
        }

        /* Right icon (eye) */
        .input-icon-right {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
            cursor: pointer;
            z-index: 2;
            transition: color 0.3s;
        }

        .input-icon-right:hover {
            color: #667eea;
        }

        /* Touch-friendly target for eye icon on mobile */
        @media (max-width: 768px) {
            .input-icon-right {
                padding: 10px;
                right: 5px;
            }
        }

        .form-group input:focus ~ .input-icon-left {
            color: #667eea;
        }
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-login i {
            font-size: 18px;
            transition: transform 0.3s;
        }

        .btn-login:hover i {
            transform: translateX(5px);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.5);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .btn-login:hover {
                transform: none;
                box-shadow: none;
            }

            .btn-login:active {
                opacity: 0.8;
            }
        }

        /* Forgot password link */
        .forgot-password {
            text-align: center;
            margin: 20px 0;
        }

        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.3s;
            padding: 8px;
        }

        .forgot-password a:hover {
            color: #5a67d8;
            text-decoration: underline;
        }

        .forgot-password a i {
            font-size: 14px;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #f3f4f6;
            color: #9ca3af;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .login-footer p {
            line-height: 1.6;
            word-break: break-word;
        }

        .login-footer i {
            margin: 0 4px;
            color: #ef4444;
            font-size: 12px;
        }

        /* Loading state for button */
        .btn-login.loading {
            opacity: 0.7;
            cursor: not-allowed;
            gap: 0;
        }

        .btn-login.loading span {
            display: none;
        }

        .btn-login.loading i {
            animation: spin 1s linear infinite;
            transform: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Small screen optimizations */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .logo-img {
                height: 100px;
            }

            .login-header h1 {
                font-size: 28px;
            }

            .login-header p::before,
            .login-header p::after {
                margin: 0 4px;
            }
        }

        @media (max-width: 360px) {
            .login-container {
                padding: 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }

            .form-group input {
                font-size: 14px;
            }

            .btn-login {
                font-size: 14px;
            }
        }

        /* Landscape mode on mobile */
        @media (max-height: 600px) and (orientation: landscape) {
            body {
                padding: 10px;
                align-items: flex-start;
            }

            .login-wrapper {
                margin: 20px auto;
            }

            .login-container {
                padding: 20px;
            }

            .logo-img {
                height: 70px;
            }

            .login-header {
                margin-bottom: 15px;
            }

            .form-group {
                margin-bottom: 12px;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .login-container {
                background: rgba(31, 41, 55, 0.95);
            }

            .login-header h1 {
                background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .login-header p {
                color: #9ca3af;
            }

            .form-group label {
                color: #e5e7eb;
            }

            .form-group input {
                background: #1f2937;
                border-color: #374151;
                color: #e5e7eb;
            }

            .form-group input:focus {
                background: #111827;
            }

            .input-icon-left {
                color: #6b7280;
            }

            .input-icon-right {
                color: #6b7280;
            }
            .forgot-password a {
                color: #818cf8;
            }

            .forgot-password a:hover {
                color: #a78bfa;
            }

            .login-footer {
                border-top-color: #374151;
                color: #9ca3af;
            }
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .login-container {
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .btn-login,
            .forgot-password {
                display: none;
            }
        }

        /* Accessibility - focus visible */
        .btn-login:focus-visible,
        .forgot-password a:focus-visible,
        input:focus-visible {
            outline: 2px solid #667eea;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <div class="logo">
                    <img src="{{ asset('assets/img/logo..png') }}" alt="Barangay Libertad Logo" class="logo-img">
                </div>
                <h1>SBIMS-PRO</h1>
                <p>Brgy. Libertad, Isabel, Leyte</p>
            </div>

            @if ($errors->any())
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Enter your username" required autofocus>
                        <i class="fas fa-user input-icon-left"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <i class="fas fa-lock input-icon-left"></i>
                        <i class="fas fa-eye input-icon-right" id="togglePassword" onclick="togglePasswordVisibility()" role="button" tabindex="0" aria-label="Toggle password visibility"></i>
                    </div>
                </div>
                <button type="submit" class="btn-login" id="loginButton">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </button>
            </form>

           <div class="forgot-password" style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin: 20px 0;">
    <a href="{{ route('username.recover') }}" style="color: #667eea; text-decoration: none; font-size: 14px;">
        <i class="fas fa-user"></i> Forgot username?
    </a>
    <a href="{{ route('password.request') }}" style="color: #667eea; text-decoration: none; font-size: 14px;">
        <i class="fas fa-key"></i> Forgot password?
    </a>
</div>
            {{-- enable if need additional ui --}}
            {{-- <div class="login-footer">
                <p>
                    <i class="fas fa-copyright"></i>
                    2026 Barangay Libertad. All rights reserved.
                    <i class="fas fa-heart" style="color: #ef4444;"></i>
                </p>
            </div> --}}
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Add loading state to button on form submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const button = document.getElementById('loginButton');

            button.classList.add('loading');
            button.querySelector('i').classList.remove('fa-sign-in-alt');
            button.querySelector('i').classList.add('fa-spinner');

            // Disable button to prevent double submission
            button.disabled = true;
        });

        // Allow keyboard activation of eye icon
        document.getElementById('togglePassword').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePasswordVisibility();
            }
        });
    </script>
</body>
</html>
