<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBIMS-PRO - Recover Account</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .recovery-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
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

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #9ca3af;
            position: relative;
        }

        .step.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 2px;
            background: #e5e7eb;
            left: 40px;
            top: 50%;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #555;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-link {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-size: 14px;
            margin-top: 15px;
            text-decoration: underline;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .footer-links {
            text-align: center;
            margin-top: 20px;
        }

        .footer-links a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="header">
            <h1>🔐 Account Recovery</h1>
            <p>Step 1 of 3: Enter your username</p>
        </div>

        <div class="step-indicator">
            <div class="step active">1</div>
            <div class="step">2</div>
            <div class="step">3</div>
        </div>

        @if(session('error'))
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.recover.step1') }}">
            @csrf

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
            </div>

            <button type="submit" class="btn-primary">Continue</button>
        </form>

        <div class="footer-links">
            <a href="{{ route('login') }}">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</body>
</html>
