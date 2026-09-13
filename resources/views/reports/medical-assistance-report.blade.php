<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CVSCAFT Faculty Association - Medical Assistance Report</title>
    <style>
        /* --- Screen-Only Interface Styling --- */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background-color: #f4f4f6;
            margin: 0;
            padding: 20px;
        }
        .screen-controls {
            max-width: 10.5in;
            margin: 0 auto 20px auto;
            background: #fff;
            padding: 12px 20px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            padding: 8px 18px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            background-color: #0056b3;
            color: white;
            border: none;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .btn-print:hover {
            background-color: #004085;
        }
        
        /* Document Canvas (Simulates Landscape Orientation on Screen) */
        .print {
            background-color: white;
            width: 10.5in; /* Standard Landscape viewport scale */
            min-height: 8in;
            margin: 0 auto;
            padding: 0.5in 0.6in;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            box-sizing: border-box;
            position: relative;
        }

        /* --- Document Typography & Structural Elements --- */
        .header {
            text-align: center;
            margin-bottom: 5px;
            line-height: 1.2;
        }
        .header h1 { 
            font-size: 14pt; 
            margin: 0; 
            font-weight: bold; 
            text-transform: uppercase;
        }
        .header h2 { 
            font-size: 11pt; 
            margin: 2px 0; 
            font-weight: normal; 
        }
        .header h3 { 
            font-size: 12pt; 
            margin: 5px 0 0 0; 
            font-weight: bold; 
            text-decoration: underline;
        }
        .header p { 
            font-size: 10.5pt; 
            margin: 3px 0; 
            font-weight: bold;
        }

        .date-marker {
            text-align: right;
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 15px;
        }
        
        /* Grid-Based Table Structural Layout */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9pt;
        }
        th, td {
            border: 1px solid black;
            padding: 5px 4px;
            vertical-align: middle;
        }
        th {
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            background-color: #f9f9f9;
            line-height: 1.2;
        }
        
        /* Functional Helpers for Table Alignments */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        /* Specific Visual Elements from Original Source Image */
        .row-highlight {
            background-color: #e8f4fd !important; /* Soft sky blue fill */
        }
        .text-indicator-blue {
            color: #002060;
            font-weight: bold;
            font-style: italic;
        }
        
        /* Lower Acknowledgment Bounding Outline Container */
        .receipt-bounding-box {
            border: 1px solid #000;
            padding: 10px 12px;
            margin-top: 15px;
            margin-bottom: 8px;
            font-size: 10.5pt;
            line-height: 1.5;
            text-align: justify;
        }
        .fill-blank {
            border-bottom: 1px solid black;
            padding: 0 8px;
            font-weight: bold;
        }

        .note-disclaimer {
            font-size: 8.5pt;
            font-style: italic;
            color: #444;
            margin-top: 0;
            margin-bottom: 35px;
        }
        
        /* Corporate Authorities Sign-Off Blocks */
        .signatures-container {
            display: flex;
            flex-direction: column;
            gap: 35px;
            width: 100%;
        }
        .sig-block {
            width: 50%;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        .sig-label {
            text-align: left;
            margin: 0 0 45px 0;
            font-size: 10.5pt;
            padding-left: 15%;
        }
        .sig-name {
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid black;
            display: inline-block;
            min-width: 260px;
            padding-bottom: 2px;
            font-size: 11pt;
        }
        .sig-title {
            font-size: 10pt;
            margin-top: 4px;
        }

        /* --- Engine Layout Rules Targeting Hardcopy Printing Media --- */
        @media print {
            /* Purge screen-only interactive dashboard panels */
            body, html, .screen-controls {
                visibility: hidden;
                background: none !important;
                box-shadow: none !important;
                margin: 0;
                padding: 0;
            }
            
            /* Isolate print container target region */
            .print, .print * {
                visibility: visible;
            }
            
            .print {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: 100%;
                box-shadow: none;
                padding: 0; /* Fall back explicitly to native @page viewport edges */
            }

            /* Direct printer engine layout matrix to process layout horizontally */
            @page {
                size: letter landscape;
                margin: 0.5in 0.6in;
            }
            
            /* Anti-fragmentation structural processing specs */
            tr { page-break-inside: avoid; }
            thead { display: table-header-group; }
            
            /* Ensure color parameters persist accurately across printer translation layers */
            .row-highlight {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: #e8f4fd !important;
            }
        }
    </style>
</head>
<body>

    <!-- Browser Workspace Ribbon: Suppressed from rendering layout targets on print -->
    <div class="screen-controls">
        <span style="font-size: 14px; color: #555;">
            <strong>Print Engine Target Layout:</strong> Landscape Mode (Letter Dimensions Recommended).
        </span>
        <button class="btn-print" onclick="window.print()">Execute Document Print</button>
    </div>

    <!-- START OF EDITABLE STANDARD PRINT BLOCKS -->
    <div class="print">
        
        <!-- Document Institutional Header Section -->
        <div class="header">
            <h1>CVSCAFT FACULTY ASSOCIATION</h1>
            <h2>BOHOL ISLAND STATE UNIVERSITY Balilihan Campus</h2>
            <h3>Medical Assistance Report</h3>
            <p>October 2024 - October 2025</p>
        </div>

        <!-- Date Context Node -->
        <div class="date-marker">
            November 2024
        </div>

        <!-- Primary Matrix Ledger Representation -->
        <table>
            <thead>
                <tr>
                    <th style="width: 4.5%;">Seq</th>
                    <th style="width: 5.5%;">Year</th>
                    <th style="width: 9%;">Month</th>
                    <th style="width: 9%;">Campus</th>
                    <th style="width: 18%;">Name of Claimant</th>
                    <th style="width: 13%;">Type of Claim</th>
                    <th style="width: 10%;">Approved Amount for Reimbursement (AC)</th>
                    <th style="width: 11%;">Received From Previous Claim/Actual Amount Received</th>
                    <th style="width: 8%;">Month & Year</th>
                    <th style="width: 8%;">Balance (TC (23k)-AC)</th>
                    <th style="width: 14%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <!-- Row Element Instance: Active Prior Claim Structure Block -->
                <tr class="row-highlight">
                    <td class="text-center">1</td>
                    <td class="text-center">2024</td>
                    <td class="text-center">November</td>
                    <td class="text-center">Balilihan</td>
                    <td class="text-indicator-blue">Marry Joyce Gudmalin-Ale</td>
                    <td>Medical Assistance</td>
                    <td class="text-right">₱ 17,400.00</td>
                    <td class="text-right">₱ 5,600.00</td>
                    <td class="text-center">October 2024</td>
                    <td class="text-right">₱ 17,400.00</td>
                    <td>Can claim by Oct 2026</td>
                </tr>
                
                <!-- Blank Document Matrix Structural Spacer Blocks -->
                <tr>
                    <td class="text-center">&nbsp;</td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                <tr>
                    <td class="text-center">&nbsp;</td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                <tr>
                    <td class="text-center">&nbsp;</td>
                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                <!-- Total Row Block -->
                <tr class="font-bold">
                    <td colspan="6" class="text-right">TOTAL</td>
                    <td class="text-right">₱ 17,400.00</td>
                    <td colspan="4" style="font-size: 8.5pt; font-weight: normal; font-style: italic; padding-left: 8px;">
                        This amount is intended for the medical assistance of the claimants listed above.
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Acknowledgment Receipt Bounding Box -->
        <div class="receipt-bounding-box">
            The amount of <strong>₱ 17,400.00</strong> was received by 
            <span class="fill-blank">ESTRELLA MARIE DUNGABONG</span>, 
            FA Treasurer of BISU Balilihan Campus from Dr. Siricia O. Perenio, dated 
            <span class="fill-blank">01/29/25</span>.
        </div>

        <!-- Footnote Section -->
        <p class="note-disclaimer">
            Note: The blue indicator signifies that the claimant has an existing prior claim and, therefore, does not require inclusion in the Notice of Deduction.
        </p>

        <!-- Signatures Layout -->
        <div class="signatures-container">
            
            <!-- Treasurer Signature Block -->
            <div class="sig-block">
                <p class="sig-label">Reported by:</p>
                <div class="sig-name">SIRICIA O. PERENIO, PhD</div>
                <div class="sig-title">Treasurer, FFA</div>
            </div>
            
            <!-- President Signature Block -->
            <div class="sig-block">
                <p class="sig-label">Noted by:</p>
                <div class="sig-name">MARC BENIGNO C. OLAGUIR, PhD</div>
                <div class="sig-title">President, FFA</div>
            </div>

        </div>

    </div>
    <!-- END OF EDITABLE STANDARD PRINT BLOCKS -->

</body>
</html>

