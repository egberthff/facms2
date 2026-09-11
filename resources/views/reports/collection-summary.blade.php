<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Report Template</title>
    <style>
        :root {
            --font-family: 'Arial', sans-serif;
            --table-border: #000000;
            --header-bg: #f2d7d5; /* Light peach/orange tint matching the original header row */
        }

        body {
            font-family: var(--font-family);
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #000;
        }

        /* Printable area wrapper */
        .print {
            background-color: #fff;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }

        /* Header Layout Styling */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .header-logo-left, .header-logo-right {
            width: 80px;
            height: 80px;
            border: 1px dashed #ccc; /* Placeholder box border */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #777;
            text-align: center;
        }

        .header-text {
            text-align: center;
            flex-grow: 1;
            padding: 0 15px;
        }

        .header-text h1 {
            font-size: 14px;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-text h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }

        .header-text p {
            font-size: 12px;
            margin: 0 0 2px 0;
            line-height: 1.3;
        }

        .header-text .motto {
            font-style: italic;
            font-size: 11px;
            margin-top: 5px;
        }

        /* Title Area */
        .report-title {
            text-align: center;
            margin-bottom: 25px;
        }

        .report-title h3 {
            font-size: 15px;
            font-weight: bold;
            margin: 0 0 3px 0;
        }

        .report-title p {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
        }

        /* Table Styling */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 35px;
        }

        .report-table th, .report-table td {
            border: 1px solid var(--table-border);
            padding: 5px 8px;
            vertical-align: middle;
        }

        /* Section header styling */
        .table-section-head {
            background-color: var(--header-bg);
            font-weight: bold;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        /* Signatures Section Layout */
        .signatures-section {
            margin-top: 50px;
            font-size: 13px;
        }

        .sig-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 45px;
        }

        .sig-block {
            width: 45%;
        }

        .sig-line {
            width: 100%;
            border-bottom: 1px solid #000;
            margin-bottom: 4px;
            position: relative;
            height: 35px; /* Leaves space for physical or digital signatures */
        }

        .sig-name {
            font-weight: bold;
            text-transform: uppercase;
        }

        .sig-title {
            font-size: 12px;
        }

        /* Print Button Utility for Screen Viewing */
        .print-btn-container {
            max-width: 800px;
            margin: 20px auto;
            text-align: right;
        }

        .print-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
        }

        .print-btn:hover {
            background-color: #0056b3;
        }

        /* Window Print Media Query Overrides */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }

            .print-btn-container {
                display: none; /* Hide the action button when printing */
            }

            .print {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            
            /* Ensure crisp text and borders when printing black and white */
            .report-table th, .report-table td {
                border-color: #000 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Print trigger button for testing -->
    <div class="print-btn-container">
        <button class="print-btn" onclick="window.print()">Print Report</button>
    </div>

    <!-- Main printable document wrapper tagged with class 'print' -->
    <div class="print">
        
        <!-- Header -->
        <header class="report-header">
            <div class="header-logo-left">
                <!-- Replace with <img src="logo1.png" alt="Logo"> -->
                Logo Left
            </div>
            <div class="header-text">
                <p>Republic of the Philippines</p>
                <h2>Bohol Island State University</h2>
                <p>Magsija, Balilihan 6342, Bohol, Philippines</p>
                <p>Faculty Association - Balilihan Campus</p>
                <p class="motto">Balance | Integrity | Stewardship | Uprightness</p>
            </div>
            <div class="header-logo-right">
                <!-- Replace with <img src="logo2.png" alt="Logo"> -->
                Logo Right
            </div>
        </header>

        <!-- Document Sub-Title -->
        <div class="report-title">
            <h3>BISU BALILIHAN FACULTY ASSOCIATION</h3>
            <p>NOVEMBER 2024 - SEPT 2025</p>
        </div>

        <!-- Financial Statement Table -->
        <table class="report-table">
            <thead>
                <!-- Columns configuration matching image structure -->
                <colgroup>
                    <col style="width: 75%;">
                    <col style="width: 25%;">
                </colgroup>
            </thead>
            <tbody>
                <tr>
                    <td class="table-section-head">Collections from the Campus Remitted to the FFA</td>
                    <td class="table-section-head text-center">Amount</td>
                </tr>

                @forelse($collections as $item)
                    <tr>
                        <td>{{ $item->label }}</td>
                        <td class="text-right">{{ number_format((float) $item->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>No collection items recorded.</td>
                        <td class="text-right">0.00</td>
                    </tr>
                @endforelse

                <tr class="font-bold">
                    <td class="text-right">Total</td>
                    <td class="text-right">P {{ number_format((float) $collection_total, 2) }}</td>
                </tr>

                <tr>
                    <td class="table-section-head">Funds Disbursed by FFA to FA Balilihan Campus</td>
                    <td class="table-section-head"></td>
                </tr>

                @forelse($disbursements as $item)
                    <tr>
                        <td>{{ $item->label }}</td>
                        <td class="text-right">{{ number_format((float) $item->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>No disbursement items recorded.</td>
                        <td class="text-right">0.00</td>
                    </tr>
                @endforelse

                <tr class="font-bold">
                    <td class="text-right">Total</td>
                    <td class="text-right">P {{ number_format((float) $disbursement_total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Signatures Footer -->
        <!-- Signatures Footer -->
        <footer class="signatures-section">
            <div class="sig-row">
                <div class="sig-block">
                    <p>Prepared by:</p>
                    <div class="sig-line"></div>
                    <div class="sig-name">ESTRELLA MARIE V. BUNGABONG</div>
                    <div class="sig-title">Campus FA Treasurer</div>
                </div>
                <div class="sig-block">
                    <p>Checked by:</p>
                    <div class="sig-line"></div>
                    <div class="sig-name">GHENA ASUCAN</div>
                    <div class="sig-title">Campus FA Auditor</div>
                </div>
            </div>

            <div class="sig-row">
                <div class="sig-block">
                    <p>Approved by:</p>
                    <div class="sig-line"></div>
                    <div class="sig-name">MARC BENIGNO C. OLAGUIR</div>
                    <div class="sig-title">FA President</div>
                </div>
            </div>
        </footer>

    </div>

</body>
</html>
