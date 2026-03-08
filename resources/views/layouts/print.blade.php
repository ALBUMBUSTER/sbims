<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - SBIMS-PRO</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: white;
            font-family: 'Times New Roman', Times, serif;
        }

        .print-container {
            max-width: 8.5in;
            margin: 0 auto;
            background: white;
        }

        @media print {
            body {
                background: white;
            }
            .no-print {
                display: none;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="print-container">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
