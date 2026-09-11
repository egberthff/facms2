<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CVSCAFT Faculty Association - Payroll Deduction Request</title>
    <style>
        /* --- Screen Styling (For browser viewing) --- */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        .no-print-zone {
            max-width: 8.5in;
            margin: 0 auto 20px auto;
            background: #fff3cd;
            padding: 15px;
            border: 1px solid #ffeeba;
            text-align: center;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
        }
        
        /* A4 or Letter size simulation container on screen */
        .print {
            background-color: white;
            max-width: 8.5in; /* Standard Letter width */
            margin: 0 auto;
            padding: 0.5in 0.75in;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }

        /* Report Specific Layout Formatting */
        .header {
            text-align: center;
            margin-bottom: 25px;
            line-height: 1.2;
        }
        .header h1 { font-size: 14pt; margin: 0; font-weight: bold; }
        .header h2 { font-size: 12pt; margin: 5px 0; font-weight: bold; }
        .header p { font-size: 10pt; margin: 0; }
        
        .month-indicator {
            text-align: right;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .salutation {
            margin-bottom: 15px;
        }
        .subject-block {
            margin: 15px 0;
            padding-left: 40px;
        }
        .subject-line {
            font-weight: bold;
            text-indent: -40px;
        }
        
        .body-text {
            text-indent: 0.5in;
            text-align: justify;
            margin-bottom: 15px;
        }
        
        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10.5pt;
        }
        th, td {
            border: 1px solid black;
            padding: 6px 4px;
            text-align: left;
        }
        th {
            text-align: center;
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        /* Signatures Layout */
        .signatures-container {
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        .sig-block {
            width: 60%;
            margin-left: 0;
        }
        .sig-line {
            border-bottom: 1px solid black;
            margin-bottom: 3px;
            height: 40px; /* Space for physical signature */
        }
        .sig-name {
            font-weight: bold;
            text-transform: uppercase;
        }

        /* --- PRINT ONLY STYLING --- */
        @media print {
            /* Hide EVERYTHING by default */
            body, html, * {
                visibility: hidden;
                background: none !important;
                box-shadow: none !important;
            }
            
            /* Show ONLY the container with the .print class and its children */
            .print, .print * {
                visibility: visible;
            }
            
            /* Position the print area perfectly on the physical page */
            .print {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0; /* Let page margins handle margins */
            }

            /* Adjust page margin defaults */
            @page {
                size: letter; /* or 'A4' depending on region preference */
                margin: 0.75in;
            }
            
            /* Prevent rows splitting midway across pages if table is long */
            tr { page-break-inside: avoid; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>

    <!-- This section will be completely hidden during print -->
    <div class="no-print-zone">
        <p><strong>Screen View:</strong> Only the document container below will be printed.</p>
        <button onclick="window.print()">Print Report</button>
    </div>

    <!-- START OF PRINT CONTAINER -->
    <div class="print">
        
        <!-- Header Section -->
        <div class="header">
            <p>Tagbilaran City, 6300, Bohol, Philippines</p>
            <h1>Office of the CVSCAFT FACULTY ASSOCIATION (CFA)</h1>
            <h2>PAYROLL DEDUCTION REQUEST FORM</h2>
        </div>

        <!-- Month Indicator -->
        <div class="month-indicator">
            For the month of <span style="border-bottom: 1px solid #000; padding-bottom: 2px;">{{ strtoupper($payrollMonth) }}</span>
        </div>

        <!-- Addressing -->
        <div class="salutation">
            <p><strong>To: Campus FA Treasurers and Payroll Makers</strong><br>
            Bohol Island State University</p>
        </div>

        <!-- Subject -->
        <div class="subject-block">
            <p class="subject-line">Subject: REQUEST FOR PAYROLL DEDUCTION TO FACULTY ASSOCIATION MEMBER'S MEDICAL ASSISTANCE AMOUNTING TO P {{ number_format($totalDeduction, 2) }} FOR {{ strtoupper($reportPeriod) }} CLAIMANTS</p>
        </div>

        <div class="salutation">
            <p>Dear Campus FA Treasurers and Payroll Makers:</p>
        </div>

        <!-- Content Body -->
        <p class="body-text">
            This letter serves as an official request to process the payroll deductions amounting to P {{ number_format($totalDeduction, 2) }} to Faculty Association (FA) members for the medical assistance of {{ $reportPeriod }} claimants. This deduction is authorized by all FA members and will be allocated to the listed claimants below:
        </p>

        <!-- Main Breakdown Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">A Member-Claimant</th>
                    <th style="width: 12%;">Campus</th>
                    <th style="width: 23%;">Type of Claim</th>
                    <th style="width: 15%;">Approved Claim</th>
                    <th style="width: 10%;">Deduction</th>
                    <th style="width: 15%;">Medical Aid Deduction Coverage</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row['member'] }}</td>
                        <td class="text-center">{{ $row['campus'] }}</td>
                        <td>{{ $row['claim_type'] }}</td>
                        <td class="text-right">₱ {{ number_format($row['approved'], 2) }}</td>
                        <td class="text-right">₱ {{ number_format($row['deduction'], 2) }}</td>
                        <td class="text-center">{{ $row['coverage'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No claimants matched the selected claim submission month.</td>
                    </tr>
                @endforelse
                <tr class="font-bold">
                    <td colspan="4" class="text-right">TOTAL</td>
                    <td class="text-right">₱ {{ number_format($totalDeduction, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <!-- Closing Text -->
        <p class="body-text" style="text-indent: 0; text-align: justify; margin-bottom: 20px;">
            We sincerely appreciate your efforts in ensuring that this deduction is accurately processed and, if possible, effected in the {{ $payrollMonth }} payroll. If this timeline cannot be accommodated, we kindly request that it be included in the subsequent payroll cycle.
        </p>
        
        <p style="margin-bottom: 40px;">Please sign below to authorize the deduction.</p>

        <!-- Signatures Layout -->
        <div class="signatures-container" style="display: flex; flex-direction: column; gap: 40px;">
            
            <!-- Treasurer Signature Block -->
            <div class="sig-block" style="width: 70%; margin-left: 0;">
                <p style="margin: 0 0 45px 0;">Requested by:</p>
                <div class="sig-name" style="font-weight: bold; text-transform: uppercase; border-bottom: 1px solid black; display: inline-block; min-width: 300px; padding-bottom: 2px; text-align: center;">
                    {{ strtoupper($federatedTreasurerName) }}
                </div>
                <div style="font-size: 11pt; margin-top: 3px;">Signature Over Printed Name of Federated CVSCAFT FA Treasurer</div>
                <div style="font-size: 11pt; margin-top: 3px;">Date: <span style="text-decoration: underline;">{{ $generatedDate }}</span></div>
            </div>
            
            <!-- President Signature Block -->
            <div class="sig-block" style="width: 70%; margin-left: 0;">
                <p style="margin: 0 0 45px 0;">Attested by:</p>
                <div class="sig-name" style="font-weight: bold; text-transform: uppercase; border-bottom: 1px solid black; display: inline-block; min-width: 300px; padding-bottom: 2px; text-align: center;">
                    {{ strtoupper($federatedPresidentName) }}
                </div>
                <div style="font-size: 11pt; margin-top: 3px;">Signature Over Printed Name of Federated CVSCAFT FA President</div>
                <div style="font-size: 11pt; margin-top: 3px;">Date: <span style="text-decoration: underline;">{{ $generatedDate }}</span></div>
            </div>

        </div>

    </div>
    <!-- END OF PRINT CONTAINER -->

</body>
</html>

