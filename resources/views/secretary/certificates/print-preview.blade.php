<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Certificate - {{ $certificate->certificate_id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            background: #f0f2f5;
            line-height: 1.6;
        }

        /* Print Controls */
        .print-controls {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #667eea;
        }

        .print-controls h2 {
            color: #333;
            font-size: 1.2rem;
        }

        .print-controls h2 span {
            color: #667eea;
            font-family: monospace;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
        }

        .btn-print {
            padding: 0.75rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-print:hover {
            opacity: 0.9;
        }

        .btn-back {
            padding: 0.75rem 2rem;
            background: white;
            color: #667eea;
            border: 1px solid #667eea;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #f8fafc;
        }

        /* Certificate Container */
        .certificate-wrapper {
            max-width: 8.5in;
            margin: 2rem auto;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 0.75in 0.75in;
            position: relative;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header-top {
            font-size: 16px;
            font-weight: normal;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .header-title {
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .header-sub {
            font-size: 18px;
            font-style: italic;
            margin-bottom: 15px;
        }

        .office {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            border-top: 2px solid black;
            border-bottom: 2px solid black;
            padding: 8px 0;
            margin: 10px 0;
            letter-spacing: 1px;
        }

        /* Certificate Title */
        .cert-title {
            font-size: 26px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            text-align: center;
            margin: 30px 0 20px 0;
        }

        /* TO WHOM IT MAY CONCERN */
        .to-whom {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0;
        }

        /* Body Text */
        .body-text {
            font-size: 16px;
            text-align: justify;
            margin: 20px 0;
        }

        .body-text p {
            margin-bottom: 20px;
        }

        /* Underline Styles */
        .underline-space {
            display: inline-block;
            border-bottom: 1px solid black;
            min-width: 150px;
            margin: 0 5px;
            vertical-align: middle;
            text-align: center;
            font-weight: bold;
        }

        .underline-space.name {
            min-width: 250px;
        }

        .underline-space.age {
            min-width: 40px;
        }

        .underline-space.status {
            min-width: 80px;
        }

        .underline-space.gender {
            min-width: 80px;
        }

        .underline-space.purok {
            min-width: 40px;
        }

        .underline-space.purpose {
            min-width: 200px;
        }

        /* Date Section */
        .date-section {
            margin-top: 30px;
            margin-bottom: 40px;
        }

        .date-line {
            text-align: right;
            font-size: 16px;
        }

        .date-underline {
            border-bottom: 1px solid black;
            min-width: 60px;
            display: inline-block;
            margin: 0 5px;
            text-align: center;
            font-weight: bold;
        }

        .date-underline.day {
            min-width: 40px;
        }

        .date-underline.month {
            min-width: 100px;
        }

        .date-underline.year {
            min-width: 60px;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: flex-end;
        }

        .signature-box {
            width: 350px;
        }

        .signature-line {
            border-top: 1px solid black;
            width: 100%;
            margin-bottom: 5px;
        }

        .signature-name {
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            margin-bottom: 2px;
        }

        .signature-title {
            font-size: 14px;
            text-align: center;
            margin-bottom: 25px;
        }

        .signature-detail {
            margin-top: 10px;
        }

        .detail-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .detail-label {
            min-width: 100px;
            font-size: 14px;
        }

        .detail-underline {
            flex: 1;
            border-bottom: 1px solid black;
            margin-left: 10px;
            height: 1px;
        }

        .detail-value {
            margin-left: 10px;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            font-size: 11px;
            text-align: center;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }

        /* Print Styles */
        @media print {
            .print-controls {
                display: none;
            }
            .certificate-wrapper {
                box-shadow: none;
                margin: 0 auto;
                padding: 0.5in;
            }
            body {
                background: white;
            }
        }
    </style>
</head>
<body>
    <!-- Print Controls (hidden when printing) -->
    <div class="print-controls">
        <h2>Certificate Preview: <span>{{ $certificate->certificate_id }}</span></h2>
        <div class="btn-group">
            <a href="javascript:window.print()" class="btn-print">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <path d="M6 9V3h12v6"/>
                    <rect x="6" y="15" width="12" height="6" rx="2"/>
                </svg>
                Print Certificate
            </a>
            <a href="{{ route('secretary.certificates.show', $certificate) }}" class="btn-back">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back
            </a>
        </div>
    </div>

    <!-- Certificate Content -->
    <div class="certificate-wrapper">
        @php
            $fullName = $resident->first_name . ' ' . $resident->last_name;
            if ($resident->middle_name) {
                $fullName = $resident->first_name . ' ' . $resident->middle_name[0] . '. ' . $resident->last_name;
            }
            $age = $resident->birthdate ? $resident->birthdate->age : '___';
            $genderPrefix = $resident->gender === 'Male' ? 'Filipino' : 'Filipina';
            $title = $resident->gender === 'Male' ? 'Mr.' : 'Ms.';
            $today = now();
        @endphp

        <!-- Header -->
        <div class="header">
            <div class="header-top">REPUBLIC OF THE PHILIPPINES</div>
            <div class="header-title">BARANGAY LIBERTAD</div>
            <div class="header-sub">Isabel, Leyte</div>
            <div class="office">OFFICE OF THE BARANGAY CAPTAIN</div>
        </div>

        <!-- Certificate Title -->
        <div class="cert-title">BARANGAY CLEARANCE</div>

        <!-- Certificate Number -->
        <div style="text-align: right; margin-bottom: 20px;">
            Certificate No.: <strong>{{ $certificate->certificate_id }}</strong>
        </div>

        <!-- TO WHOM IT MAY CONCERN -->
        <div class="to-whom">TO WHOM IT MAY CONCERN:</div>

        <!-- Body Text -->
        <div class="body-text">
            <p>
                This is to certify that
                <span class="underline-space name">{{ $fullName }}</span>,
                <span class="underline-space age">{{ $age }}</span> years old,
                <span class="underline-space status">{{ $resident->civil_status ?? 'Single' }}</span>,
                <span class="underline-space gender">{{ $genderPrefix }}</span> and a resident of
                Purok <span class="underline-space purok">{{ $resident->purok }}</span>,
                Libertad, Isabel, Leyte is known to me of good moral character.
            </p>

            <p>
                This certifies further that according to the records available in this office, said
                <span class="underline-space name">{{ $title }} {{ $resident->last_name }}</span>
                was never been accused of any crime nor penalized under Barangay ordinance and he/she is
                not a member of any subversive organization in our community.
            </p>

            <p>
                This certificate is issued upon the request of the interested party for
                <span class="underline-space purpose">{{ $certificate->purpose }}</span>.
            </p>
        </div>

        <!-- Date Line -->
        <div class="date-section">
            <div class="date-line">
                Done this <span class="date-underline day">{{ $today->format('jS') }}</span> day of
                <span class="date-underline month">{{ $today->format('F') }}</span>,
                <span class="date-underline year">{{ $today->year }}</span> at Barangay
                Libertad, Isabel, Leyte, Philippines.
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">HON. REYNALDO M. ROCHE</div>
                <div class="signature-title">Punong Barangay</div>

                <div class="signature-detail">
                    <div class="detail-row">
                        <span class="detail-label">Signature:</span>
                        <span class="detail-underline"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Res. Cert. No.:</span>
                        <span class="detail-underline"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Issued Date:</span>
                        <span class="detail-value">{{ $today->format('F d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            This certificate is valid only if presented within six (6) months from the date of issue.
        </div>
    </div>
</body>
</html>
