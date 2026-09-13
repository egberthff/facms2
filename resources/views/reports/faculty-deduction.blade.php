<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bohol Island State University - Faculty Deductions Ledger</title>
        <style>
        /* --- Screen-Only Viewing Styles --- */
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #000;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
        }
        .screen-controls {
            max-width: 11in;
            margin: 0 auto 15px auto;
            background: #fff;
            padding: 10px 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            padding: 6px 14px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
        }
        
        /* Landscape Document Canvas Sheet Simulator */
        .print {
            background-color: white;
            width: 11in; /* Hard Letter landscape baseline bounding dimensions */
            min-height: 8.5in;
            margin: 0 auto;
            padding: 0.4in 0.4in;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            box-sizing: border-box;
        }

        /* --- Header Hierarchy Elements --- */
        .header {
            text-align: center;
            margin-bottom: 12px;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .header h1 { font-size: 11pt; margin: 0; font-weight: bold; color: #111; }
        .header h2 { font-size: 10pt; margin: 2px 0; font-weight: bold; }
        .header p { font-size: 9pt; margin: 2px 0; font-weight: normal; text-transform: none; font-style: italic; }

        /* --- Comprehensive Master Ledger Matrix Grid --- */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt; /* Extremely tight tracking density to match raw document mapping scale */
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px 2px;
            vertical-align: middle;
        }
        th {
            font-weight: bold;
            text-align: center;
            background-color: #f8f9fa;
        }
        
        /* Subhead descriptions layer under month names */
        .sub-header-title {
            font-size: 5.5pt;
            font-weight: normal;
            display: block;
            line-height: 1.1;
        }

        /* --- Specific Document Context Highlight Fills --- */
        .bg-yellow { background-color: #fff2cc !important; } /* Yellow column indicators */
        .bg-orange { background-color: #fce5cd !important; } /* Orange balance markers */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* --- Bottom Layout Corporate Executive Sign-Off Matrix --- */
        .signatures-row {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            width: 100%;
            gap: 40px;
        }
        .sig-block {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .sig-meta-title {
            font-size: 9pt;
            margin-bottom: 45px;
            font-weight: normal;
        }
        .sig-underline-container {
            border-bottom: 1px solid #000;
            text-align: center;
            padding-bottom: 2px;
        }
        .sig-name {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9.5pt;
        }
        .sig-assignment {
            font-size: 8.5pt;
            margin-top: 2px;
            color: #222;
        }
        .sig-date {
            font-size: 8.5pt;
            margin-top: 2px;
        }

        /* --- Media Interception Layers Explicitly Aimed at Printer Core Systems --- */
        @media print {
            body, html, .screen-controls {
                visibility: hidden;
                background: none !important;
                box-shadow: none !important;
                margin: 0;
                padding: 0;
            }
            .print, .print * {
                visibility: visible;
            }
            .print {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: 100%;
                padding: 0;
            }
            @page {
                size: letter landscape;
                margin: 0.4in;
            }
            tr { page-break-inside: avoid; }
            thead { display: table-header-group; }
            
            /* Enforce background processing vectors through the compiler layers */
            .bg-yellow {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: #fff2cc !important;
            }
            .bg-orange {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: #fce5cd !important;
            }
        }
    </style>
    </head>
    <body>

        <!-- Web Interface Controls Container Area -->
        <div class="screen-controls">
            <span style="color: #666;"><strong>Ledger Sheet Rendering
                    Target:</strong> Broad Landscape Paper Format
                Blueprint.</span>
            <button class="btn-print" onclick="window.print()">Print
                Document</button>
        </div>

        <!-- START OF MAIN LEDGER PRINT AREA -->
        <div class="print">

            <!-- Center Headings -->
            <div class="header">
                <h1> {{ $campusName ?? "" }} </h1>
                <h2>Faculty Association - Faculty Deductions</h2>
                <p> {{ $deductionPeriod ?? "" }} </p>s
            </div>

            <!-- Master Data Matrix Grid -->
            <table>
                <thead>
                    <!-- Row 1: High Level Calendar Periods & System Categories -->
                    <tr>
                        <th rowspan="3" style="width: 3%;">No</th>
                        <th rowspan="3" style="width: 15%;">Faculty Association
                            Member</th>
                        <th>January</th>
                        <th>January</th>
                        <th>February</th>
                        <th>March</th>
                        <th>April</th>
                        <th>May</th>
                        <th>June</th>
                        <th>July</th>
                        <th>August</th>
                        <th>September</th>
                        <th>October</th>
                        <th>November</th>
                        <th rowspan="2" style="width: 7%;">Total</th>
                    </tr>
                    <!-- Row 2: Inner Local Purpose Metadata Subheadings -->
                    <tr>
                        <th><span class="sub-header-title">CMA Share</span></th>
                        <th><span class="sub-header-title">Medical
                                Assistance</span></th>
                        <th><span class="sub-header-title">Medical
                                Assistance</span></th>
                        <th><span
                                class="sub-header-title">Medical
                                Assistance</span></th>
                        <th><span
                                class="sub-header-title">Medical
                                Assistance</span></th>
                        <th><span
                                class="sub-header-title">Medical
                                Assistance</span></th>
                        <th><span
                                class="sub-header-title">Medical
                                Assistance</span></th>
                        <th><span class="sub-header-title">Medical
                                Assistance</span></th>
                        <th><span
                                class="sub-header-title">Medical
                                Assistance</span></th>
                        <th><span class="sub-header-title">Medical
                                Assistance</span></th>
                        <th><span class="sub-header-title">Medical
                                Assistance</span></th>
                        <th><span class="sub-header-title">Unpaid
                                Deductions</span></th>
                    </tr>
                    <tr class="bg-yellow font-bold text-center">
                        <td class="border p-1">500</td>
                        <td class="border p-1">200</td>
                        <td class="border p-1">250</td>
                        <td class="border p-1">250</td>
                        <td class="border p-1">250</td>
                        <td class="border p-1">250</td>
                        <td class="border p-1">250</td>
                        <td class="border p-1">250</td>
                        <td class="border p-1">250</td>
                        <td class="border p-1">250</td>
                        <td class="border p-1">250</td>
                        <td class="border p-1">250</td>
                        <td class="border p-1">250</td>
                    </tr>
                </thead>
                <tbody>
                    <!-- Entry row structural layout example based on first ledger item -->
                    <tr>
                        <td class="text-center">1</td>
                        <td>Ale, Marry Joyce</td>
                        <td class="text-right">500</td>
                        <td class="text-right">200</td>
                        <td class="text-right">250</td>
                        <td class="text-right">350</td>
                        <td class="text-right">650</td>
                        <td class="text-right">300</td>
                        <td class="text-right">550</td>
                        <td class="text-right">300</td>
                        <td class="text-right">400</td>
                        <td class="text-right">450</td>
                        <td class="text-right">500</td>
                        <td class="text-right">0</td>
                        <td class="text-right font-bold">4450</td>
                    </tr>
                    <tr>
                        <td class="text-center">2</td>
                        <td>Asucan, Ernesto Jr.</td>
                        <td class="text-right">500</td>
                        <td class="text-right">200</td>
                        <td class="text-right">250</td>
                        <td class="text-right">350</td>
                        <td class="text-right">650</td>
                        <td class="text-right">300</td>
                        <td class="text-right">650</td>
                        <td class="text-right">300</td>
                        <td class="text-right">400</td>
                        <td class="text-right">350</td>
                        <td class="text-right">500</td>
                        <td class="text-right">0</td>
                        <td class="text-right font-bold">4450</td>
                    </tr>
                    <tr>
                        <td class="text-center">3</td>
                        <td>Asucan, Ivy Jane</td>
                        <td class="text-right">500</td>
                        <td class="text-right">200</td>
                        <td class="text-right">250</td>
                        <td class="text-right">350</td>
                        <td class="text-right">650</td>
                        <td class="text-right">300</td>
                        <td class="text-right">650</td>
                        <td class="text-right">300</td>
                        <td class="text-right">400</td>
                        <td class="text-right">350</td>
                        <td class="text-right">500</td>
                        <td class="text-right">1900</td>
                        <td class="text-right font-bold">4450</td>
                    </tr>
                    <tr>
                        <td class="text-center">4</td>
                        <td>Asucan, Ghena</td>
                        <td class="text-right">500</td>
                        <td class="text-right">200</td>
                        <td class="text-right">250</td>
                        <td class="text-right">350</td>
                        <td class="text-right">650</td>
                        <td class="text-right">300</td>
                        <td class="text-right">650</td>
                        <td class="text-right">300</td>
                        <td class="text-right">400</td>
                        <td class="text-right">350</td>
                        <td class="text-right">500</td>
                        <td class="text-right">0</td>
                        <td class="text-right font-bold">4450</td>
                    </tr>
                    
                    <!-- Calculation Matrix Summary Footers -->
                    <tr class="font-bold" style="background-color: #fafafa;">
                        <td colspan="2" class="text-right">TOTAL MONTHLY
                            COLLECTION TARGET</td>
                        <td class="text-right">12500</td>
                        <td class="text-right">5000</td>
                        <td class="text-right">6250</td>
                        <td class="text-right">8750</td>
                        <td class="text-right">16250</td>
                        <td class="text-right">7500</td>
                        <td class="text-right">13750</td>
                        <td class="text-right">7500</td>
                        <td class="text-right">10000</td>
                        <td class="text-right">11250</td>
                        <td class="text-right">12500</td>
                        <td class="text-right">0</td>
                        <td class="text-right">111250</td>
                    </tr>
                    <tr class="font-bold">
                        <td colspan="2" class="text-right">ACTUAL MONTHLY
                            COLLECTION</td>
                        <td class="text-right">12500</td>
                        <td class="text-right">5000</td>
                        <td class="text-right">6250</td>
                        <td class="text-right">8750</td>
                        <td class="text-right">16250</td>
                        <td class="text-right">7500</td>
                        <td class="text-right">14950</td>
                        <td class="text-right">7200</td>
                        <td class="text-right">9200</td>
                        <td class="text-right">8050</td>
                        <td class="text-right">11500</td>
                        <td class="text-right">2200</td>
                        <td class="text-right">111250</td>
                    </tr>
                    <tr class="font-bold">
                        <td colspan="2" class="text-right">BALANCE
                            COLLECTIBLE</td>
                        <td class="text-right">0</td>
                        <td class="text-right">0</td>
                        <td class="text-right">0</td>
                        <td class="text-right">0</td>
                        <td class="text-right">0</td>
                        <td class="text-right">0</td>
                        <td class="text-right">-1200</td>
                        <td class="text-right">300</td>
                        <td class="text-right">800</td>
                        <td class="text-right">3200</td>
                        <td class="text-right">1000</td>
                        <td class="text-right">4100</td>
                        <td class="text-right">111250</td>
                    </tr>
                </tbody>
            </table>

            <!-- Document Notes and Adjustments Block -->
            <div
                style="margin-top: -10px; margin-bottom: 25px; padding-left: 5px; font-size: 8pt; font-family: sans-serif; line-height: 1.4;">
                <div style="margin-bottom: 4px;">
                    <span class="bg-orange"
                        style="display: inline-block; width: 45px; height: 12px; border: 1px solid #000; vertical-align: middle; margin-right: 5px;"></span>
                    <strong>Overdeduction from June:</strong> -1200
                </div>
                <div>
                    <span
                        style="display: inline-block; width: 45px; height: 12px; border: 1px solid #000; background-color: #fff; vertical-align: middle; margin-right: 5px; text-align: center; font-size: 7.5pt; font-weight: bold;">...</span>
                    <strong>Adjust Deduction from June Overdeduction</strong>
                    (Applied to subsequent collection periods)
                </div>
            </div>

            <!-- Layout Corporate Executive Sign-Off Matrix -->
            <div class="signatures-row">

                <!-- Prepared By Block -->
                <div class="sig-block">
                    <span class="sig-meta-title">Prepared by:</span>
                    <div class="sig-underline-container">
                        <div class="sig-name">ESTRELLA MARIE DUNGABONG</div>
                    </div>
                    <span class="sig-assignment">Campus FA Treasurer</span>
                    <span class="sig-date">Date: ________________________</span>
                </div>

                <!-- Audited By Block -->
                <div class="sig-block">
                    <span class="sig-meta-title">Audited by:</span>
                    <div class="sig-underline-container">
                        <div class="sig-name">GHENA L. ASUCAN</div>
                    </div>
                    <span class="sig-assignment">Campus FA Auditor</span>
                    <span class="sig-date">Date: ________________________</span>
                </div>

                <!-- Approved By Block -->
                <div class="sig-block">
                    <span class="sig-meta-title">Approved by:</span>
                    <div class="sig-underline-container">
                        <div class="sig-name">MARC BENIGNO C. OLAGUIR</div>
                    </div>
                    <span class="sig-assignment">FA President</span>
                    <span class="sig-date">Date: ________________________</span>
                </div>

            </div>

        </div>
        <!-- END OF MAIN LEDGER PRINT AREA -->

    </body>
</html>
