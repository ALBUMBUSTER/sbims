<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBIMS-PRO - Security Question</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            max-width: 450px;
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

        .step.completed {
            background: #10b981;
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

        .question-box {
            background: #f0f3ff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid #667eea;
        }

        .question-box i {
            color: #667eea;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .question-box p {
            font-size: 16px;
            font-weight: 500;
            color: #333;
            line-height: 1.5;
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

        .alert-error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
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
            <h1>🔐 Security Question</h1>
            <p>Step 2 of 2: Answer to verify your identity</p>
        </div>

        <div class="step-indicator">
            <div class="step completed">✓</div>
            <div class="step active">2</div>
        </div>

        @if(session('error'))
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="question-box">
            <i class="fas fa-shield-alt"></i>
            <p>{{ $question }}</p>
        </div>

        <form method="POST" action="{{ route('username.recover.verify') }}">
            @csrf

            <div class="form-group">
                <label for="answer">Your Answer</label>
                <input type="text" id="answer" name="answer" placeholder="Enter your answer" required>
            </div>

            <button type="submit" class="btn-primary">Verify Answer</button>
        </form>

        <div class="footer-links">
            <a href="{{ route('username.recover.cancel') }}">
                <i class="fas fa-times"></i> Cancel Recovery
            </a>
        </div>
    </div>
</body>
</html>
