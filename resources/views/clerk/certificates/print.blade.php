<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $certificate->certificate_type }} - {{ $certificate->certificate_id }}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 2rem;
            background: white;
            color: #000;
        }

        .certificate-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 2rem;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #000;
            padding-bottom: 1rem;
        }

        .header h1 {
            font-size: 2rem;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header h2 {
            font-size: 1.5rem;
            margin: 0.5rem 0;
            text-transform: uppercase;
        }

        .header p {
            margin: 0.25rem 0;
            font-size: 1rem;
        }

        .title {
            text-align: center;
            margin: 2rem 0;
        }

        .title h3 {
            font-size: 1.8rem;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 0;
        }

        .content {
            margin: 2rem 0;
            line-height: 2;
            text-align: justify;
        }

        .content p {
            margin: 1rem 0;
            font-size: 1.1rem;
        }

        .content .resident-name {
            font-weight: bold;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 3rem;
            display: flex;
            justify-content: space-between;
        }

        .signature {
            text-align: center;
            width: 250px;
        }

        .signature .line {
            margin-top: 3rem;
            border-top: 1px solid #000;
            padding-top: 0.5rem;
        }

        .signature .name {
            font-weight: bold;
        }

        .signature .title {
            font-size: 0.9rem;
            margin: 0;
            text-decoration: none;
        }

        .issued-date {
            text-align: right;
            margin-top: 2rem;
            font-style: italic;
        }

        .certificate-number {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 0.8rem;
            color: #666;
        }

        @media print {
            body {
                padding: 0;
            }

            .certificate-container {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate-number">
            {{ $certificate->certificate_id }}
        </div>

        <div class="header">
            <h1>Republic of the Philippines</h1>
            <h2>Province of Leyte</h2>
            <h2>Municipality of Isabel</h2>
            <h2>Barangay Libertad</h2>
            <p>OFFICE OF THE BARANGAY CAPTAIN</p>
        </div>

        <div class="title">
            <h3>
                @if($certificate->certificate_type == 'Clearance')
                    BARANGAY CLEARANCE
                @elseif($certificate->certificate_type == 'Indigency')
                    CERTIFICATE OF INDIGENCY
                @elseif($certificate->certificate_type == 'Residency')
                    CERTIFICATE OF RESIDENCY
                @elseif($certificate->certificate_type == 'Good Moral')
                    CERTIFICATE OF GOOD MORAL
                @else
                    {{ strtoupper($certificate->certificate_type) }}
                @endif
            </h3>
        </div>

        <div class="content">
            <p><strong>TO WHOM IT MAY CONCERN:</strong></p>

            <p>
                This is to certify that <span class="resident-name">{{ $certificate->resident->full_name ?? 'N/A' }}</span>,
                {{ $certificate->resident->age ?? '' }} years of age, {{ $certificate->resident->civil_status ?? '' }},
                and a resident of Purok {{ $certificate->resident->purok ?? 'N/A' }}, Barangay Libertad, Isabel, Leyte,
                is a bonafide resident of this barangay.
            </p>

            @if($certificate->certificate_type == 'Clearance')
            <p>
                This clearance is being issued upon the request of the above-named person for
                <span class="resident-name">{{ $certificate->purpose }}</span> purposes.
            </p>
            <p>
                It is further certified that the above-named person has no derogatory record or pending
                administrative/criminal case in this barangay.
            </p>
            @elseif($certificate->certificate_type == 'Indigency')
            <p>
                This is to further certify that the above-named person belongs to an indigent family
                and is a beneficiary of <span class="resident-name">{{ $certificate->resident->is_4ps ? '4Ps' : 'various social services' }}</span>
                as per records of this barangay.
            </p>
            @elseif($certificate->certificate_type == 'Residency')
            <p>
                This certification is issued upon the request of the above-named person to verify
                his/her residency in this barangay for <span class="resident-name">{{ $certificate->purpose }}</span> purposes.
            </p>
            @elseif($certificate->certificate_type == 'Good Moral')
            <p>
                This is to further certify that the above-named person is known to be of good moral
                character, law-abiding, and an active member of the community.
            </p>
            @endif

            <p>
                Issued this {{ now()->format('jS') }} day of {{ now()->format('F Y') }} at Barangay Libertad,
                Isabel, Leyte, upon request of the interested party for <span class="resident-name">{{ $certificate->purpose }}</span> purposes.
            </p>
        </div>

        <div class="footer">
            <div class="signature">
                <div class="line">
                    <div class="name">HON. JUAN DELA CRUZ</div>
                    <div class="title">Punong Barangay</div>
                </div>
            </div>
            <div class="signature">
                <div class="line">
                    <div class="name">{{ auth()->user()->full_name ?? auth()->user()->name }}</div>
                    <div class="title">Barangay Clerk</div>
                </div>
            </div>
        </div>

        <div class="issued-date">
            <p>Not valid without dry seal</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
