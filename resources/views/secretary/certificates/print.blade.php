<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $certificate->certificate_type }} - {{ $certificate->certificate_number }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .certificate-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            border: 2px solid #333;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 28px;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header h2 {
            font-size: 24px;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .header h3 {
            font-size: 20px;
            margin: 5px 0;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .title {
            text-align: center;
            margin: 40px 0;
        }
        .title h1 {
            font-size: 32px;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 0;
        }
        .content {
            margin: 30px 0;
            line-height: 2;
            font-size: 16px;
            text-align: justify;
        }
        .content p {
            margin: 15px 0;
            text-indent: 40px;
        }
        .content strong {
            text-transform: uppercase;
        }
        .details {
            margin: 30px 0;
            font-size: 16px;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details td {
            padding: 8px;
            vertical-align: top;
        }
        .details td.label {
            width: 150px;
            font-weight: bold;
        }
        .footer {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            text-align: center;
            width: 250px;
        }
        .signature .line {
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 10px;
        }
        .seal {
            position: absolute;
            bottom: 100px;
            right: 100px;
            opacity: 0.3;
            font-size: 100px;
            transform: rotate(-15deg);
        }
        @media print {
            body {
                padding: 0;
            }
            .certificate-container {
                border: none;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <!-- Barangay Seal (text-based for now) -->
        <div class="seal">⚖️</div>

        <!-- Header -->
        <div class="header">
            <h1>REPUBLIC OF THE PHILIPPINES</h1>
            <h2>PROVINCE OF __________</h2>
            <h3>MUNICIPALITY OF __________</h3>
            <p>BARANGAY __________</p>
            <p>OFFICE OF THE BARANGAY CAPTAIN</p>
        </div>

        <!-- Title -->
        <div class="title">
            <h1>{{ $certificate->certificate_type }}</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p>
                <strong>TO WHOM IT MAY CONCERN:</strong>
            </p>
            <p>
                This is to certify that <strong>{{ $certificate->resident->first_name }} {{ $certificate->resident->last_name }}</strong>,
                of legal age, {{ $certificate->resident->gender ?? 'resident' }}, and a resident of
                <strong>{{ $certificate->resident->address }}</strong>, Barangay __________,
                Municipality of __________, Province of __________, is known to be a person of good moral character
                and a law-abiding citizen of this barangay.
            </p>
            <p>
                This certification is issued upon the request of the above-named person for
                <strong>{{ $certificate->purpose }}</strong>.
            </p>
            @if($certificate->remarks)
            <p>
                <em>Remarks: {{ $certificate->remarks }}</em>
            </p>
            @endif
            <p>
                Issued this <strong>{{ now()->format('jS') }}</strong> day of
                <strong>{{ now()->format('F Y') }}</strong> at Barangay __________,
                Municipality of __________, Province of __________.
            </p>
        </div>

        <!-- Additional Details -->
        <div class="details">
            <table>
                <tr>
                    <td class="label">Certificate No.:</td>
                    <td>{{ $certificate->certificate_id }}</td>
                </tr>
                <tr>
                    <td class="label">Request Date:</td>
                    <td>{{ $certificate->request_date->format('F d, Y') }}</td>
                </tr>
                @if($certificate->or_number)
                <tr>
                    <td class="label">OR No.:</td>
                    <td>{{ $certificate->or_number }}</td>
                </tr>
                @endif
                @if($certificate->amount)
                <tr>
                    <td class="label">Amount Paid:</td>
                    <td>₱ {{ number_format($certificate->amount, 2) }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Footer Signatures -->
        <div class="footer">
            <div class="signature">
                <div>_________________________</div>
                <div>BARANGAY CAPTAIN</div>
            </div>
            <div class="signature">
                <div>_________________________</div>
                <div>BARANGAY SECRETARY</div>
            </div>
        </div>

        <!-- Not valid without seal -->
        <p style="text-align: center; margin-top: 40px; font-size: 12px;">
            <em>(Not valid without official seal)</em>
        </p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
