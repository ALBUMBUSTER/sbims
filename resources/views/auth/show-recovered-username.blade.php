<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBIMS-PRO - Your Username</title>
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

        .result-container {
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

        .success-icon {
            width: 80px;
            height: 80px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .success-icon i {
            font-size: 40px;
            color: #10b981;
        }

        .username-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .username-box .label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .username-box .username {
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 1px;
            font-family: monospace;
        }

        .user-info {
            text-align: center;
            color: #666;
            margin: 15px 0;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
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
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            width: 100%;
            background: white;
            color: #667eea;
            border: 1px solid #667eea;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-top: 10px;
        }

        .btn-secondary:hover {
            background: #eef2ff;
        }

        .print-btn {
            background: #f3f4f6;
            color: #4b5563;
            border: 1px solid #d1d5db;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }

        .print-btn:hover {
            background: #e5e7eb;
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
    </style>
</head>
<body>
    <div class="result-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <div class="header">
            <h1>Username Found!</h1>
            <p>Your identity has been verified</p>
        </div>

        <div class="user-info">
            <i class="fas fa-user-circle" style="font-size: 40px; color: #667eea; margin-bottom: 10px;"></i>
            <p style="font-weight: 500;">{{ $full_name }}</p>
        </div>

        <div class="username-box">
            <div class="label">Your Username</div>
            <div class="username">{{ $username }}</div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <a href="{{ route('login') }}" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login Now
            </a>
        </div>

        <a href="{{ route('password.request') }}" class="btn-secondary">
            <i class="fas fa-key"></i> Forgot Password?
        </a>

        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print this page
        </button>
    </div>
</body>
</html>
