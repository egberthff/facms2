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
        .header p.period { font-weight: bold; font-style: normal; }
        .header h1 { font-weight: normal; }

        td.no-border { border: none !important; }
        .text-left { text-align: left !important; }
        .adjustment-note {
            background-color: #f4cccc;
            border: 1px solid #000;
            font-size: 6pt;
            font-weight: bold;
            padding: 2px;
            margin-top: 2px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* --- Comprehensive Master Ledger Matrix Grid --- */
        table {
            table-layout: fixed; 
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt; /* Extremely tight tracking density to match raw document mapping scale */
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px 2px;
            vertical-align: middle;
            text-align: center;
            word-wrap: break-word; 
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
            /* Every fiscal-year month is always printed, so tighten the grid to fit one page width. */
            table { font-size: 6.5pt; }
            th, td { padding: 2px 1px; }
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
        <div class="no-print-zone">
            <p><strong>Screen View:</strong> Only the document container below will be printed.</p>
            <button onclick="window.print()">Print Report</button>
        </div>

        <!-- START OF MAIN LEDGER PRINT AREA -->
        <div class="print">
            <!-- Center Headings (same three lines as the manual report) -->
            <div class="header">
                <h1>{{ str_replace(' - ', ' ', $campus ?? '') }}</h1>
                <h2>Faculty Association - Faculty Deductions</h2>
                <p class="period">{{ $period ?? '' }}</p>
            </div>

            <!-- Master Data Matrix Grid -->
            @php
                /*
                 * Column order follows the fiscal year:
                 * Oct-Dec FY-1 Medical Assistance | January CNA Share | January Annual Dues
                 * | January-October Medical Assistance | November Unpaid Deductions | Total
                 */
                $allMonths     = $table_data['header_month_label'] ?? [];
                $settlementKey = end($allMonths) ?: null;

                // Every month of the fiscal year is printed, including the early months
                // (Oct 15 - Dec 31 of FY-1), even when they carry no amount.
                $preMonths  = array_values($table_data['pre_january_month_label'] ?? []);
                $postMonths = array_values(array_diff($allMonths, $preMonths));
                $shownMonths = array_merge($preMonths, $postMonths);

                $cnaYears    = collect($table_data['total_cna_share_collection_target'] ?? [])->except('total')->keys();
                $annualYears = collect($table_data['total_annual_due_collection_target'] ?? [])->except('total')->keys();
                $columnCount = 3 + count($shownMonths) + $cnaYears->count() + $annualYears->count();

                $hasAdjustments = (float) ($table_data['collection_adjustments']['total'] ?? 0) != 0
                    || collect($table_data['collection_adjustment_details'] ?? [])->flatten(1)->isNotEmpty();

                // Whole pesos print like the manual (12500); centavos are never rounded away.
                $fmt = function ($value) {
                    $value = round((float) $value, 2);
                    return floor($value) == $value
                        ? number_format($value, 0, '.', '')
                        : number_format($value, 2, '.', '');
                };

                // "June" for months in the fiscal year's own year, "October 2025" for the early months.
                $monthName = fn ($label) => in_array($label, $preMonths, true)
                    ? ucwords($label)
                    : ucfirst(strtok($label, ' '));

                // Footer cells for one summary row, in the same order as the columns.
                $footerCells = function (string $monthlyKey, string $cnaKey, string $annualKey) use ($table_data, $preMonths, $postMonths) {
                    $cells = [];
                    foreach ($preMonths as $label) {
                        $cells[] = $table_data[$monthlyKey][$label] ?? 0;
                    }
                    foreach (collect($table_data[$cnaKey] ?? [])->except('total') as $data) {
                        $cells[] = $data['total'] ?? 0;
                    }
                    foreach (collect($table_data[$annualKey] ?? [])->except('total') as $data) {
                        $cells[] = $data['total'] ?? 0;
                    }
                    foreach ($postMonths as $label) {
                        $cells[] = $table_data[$monthlyKey][$label] ?? 0;
                    }
                    return $cells;
                };

                $balanceCells = [];
                foreach ($preMonths as $label) {
                    $balanceCells[] = $table_data['balance_collectible'][$label] ?? 0;
                }
                foreach (collect($table_data['balance_collectible']['cna_share'] ?? [])->except('total') as $data) {
                    $balanceCells[] = $data['total'] ?? 0;
                }
                foreach (collect($table_data['balance_collectible']['annual_due'] ?? [])->except('total') as $data) {
                    $balanceCells[] = $data['total'] ?? 0;
                }
                foreach ($postMonths as $label) {
                    $balanceCells[] = $table_data['balance_collectible'][$label] ?? 0;
                }
            @endphp

            <table>
                <thead>
                    <!-- Row 1: Month names -->
                    <tr>
                        <th rowspan="3" style="width: 3%;">No.</th>
                        <th rowspan="3" style="width: 16%;">Faculty Association Member</th>
                       
                        @foreach ($preMonths as $label)
                            <th>{{ $monthName($label) }}</th>
                        @endforeach

                        @foreach ($cnaYears as $year)
                            <th>January</th>
                        @endforeach
                        @foreach ($annualYears as $year)
                            <th>January</th>
                        @endforeach

                        @foreach ($postMonths as $label)
                            <th>{{ $monthName($label) }}</th>
                        @endforeach

                        <th style="width: 7%;">Total</th>
                    </tr>

                    <!-- Row 2: Deduction type -->
                    <tr>
                        @foreach ($preMonths as $label)
                            <th><span class="sub-header-title">Medical Assistance</span></th>
                        @endforeach

                        @foreach ($cnaYears as $year)
                            <th><span class="sub-header-title">CNA Share</span></th>
                        @endforeach
                        @foreach ($annualYears as $year)
                            <th><span class="sub-header-title">Annual Dues</span></th>
                        @endforeach

                        @foreach ($postMonths as $label)
                            <th>
                                <span class="sub-header-title">
                                    {{ $label === $settlementKey ? 'Unpaid Deductions' : 'Medical Assistance' }}
                                </span>
                            </th>
                        @endforeach

                        <th><span class="sub-header-title">Deductions per Member</span></th>
                    </tr>

                    <!-- Row 3: Scheduled amount per member (yellow band) -->
                    <tr class="font-bold text-center">
                        @foreach ($preMonths as $label)
                            <td class="bg-yellow">{{ $fmt($table_data['header_months_amount'][$label] ?? 0) }}</td>
                        @endforeach

                        @foreach ($cnaYears as $year)
                            <td class="bg-yellow">{{ $fmt($table_data['cna_share']['amount'] ?? 0) }}</td>
                        @endforeach
                        @foreach ($annualYears as $year)
                            <td class="bg-yellow">{{ $fmt($table_data['annual_due']['amount'] ?? 0) }}</td>
                        @endforeach

                        @foreach ($postMonths as $label)
                            <td class="bg-yellow">{{ $label === $settlementKey ? '' : $fmt($table_data['header_months_amount'][$label] ?? 0) }}</td>
                        @endforeach

                        <td>{{ $fmt($table_data['header_months_amount']['total'] ?? 0) }}</td>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($table_data['members'] as $index => $member)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td style="text-align: left;">{{ ucwords($member['name']) }}</td>

                            @foreach ($preMonths as $label)
                                <td class="text-right">{{ $fmt($member['months'][$label] ?? 0) }}</td>
                            @endforeach

                            @foreach ($cnaYears as $year)
                                <td class="text-right">{{ $fmt($member['cna_share'][$year] ?? 0) }}</td>
                            @endforeach
                            @foreach ($annualYears as $year)
                                <td class="text-right">{{ $fmt($member['annual_due'][$year] ?? 0) }}</td>
                            @endforeach

                            @foreach ($postMonths as $label)
                                @php $amount = $member['months'][$label] ?? 0; @endphp
                                <td class="text-right {{ $label === $settlementKey && $amount > 0 ? 'bg-orange' : '' }}">{{ $fmt($amount) }}</td>
                            @endforeach

                            <td class="text-right">{{ $fmt($member['total_contributed']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $columnCount }}" class="text-center">No records found!</td>
                        </tr>
                    @endforelse

                    {{--
                        Footer rows cross-foot: each row's Total equals the sum of its cells, and
                        BALANCE COLLECTIBLE = TOTAL MONTHLY COLLECTION - ACTUAL MONTHLY COLLECTION (- ADJUSTMENTS).
                        November has no new target; its actual collection settles earlier balances,
                        so it shows as a negative November balance.
                    --}}
                    <tr class="font-bold">
                        <td colspan="2" class="text-left">TOTAL MONTHLY COLLECTION</td>
                        @foreach ($footerCells('total_monthly_collection_target', 'total_cna_share_collection_target', 'total_annual_due_collection_target') as $amount)
                            <td class="text-right">{{ $fmt($amount) }}</td>
                        @endforeach
                        <td class="text-right">{{ $fmt($table_data['total_monthly_collection_target']['total'] ?? 0) }}</td>
                    </tr>

                    <tr class="font-bold">
                        <td colspan="2" class="text-left">ACTUAL MONTHLY COLLECTION</td>
                        @foreach ($footerCells('actual_monthly_collection', 'actual_cna_share_collection', 'actual_annual_due_collection') as $amount)
                            <td class="text-right">{{ $fmt($amount) }}</td>
                        @endforeach
                        <td class="text-right">{{ $fmt($table_data['actual_monthly_collection']['total'] ?? 0) }}</td>
                    </tr>

                    @if ($hasAdjustments)
                        <tr class="font-bold">
                            <td colspan="2" class="text-left">COLLECTION ADJUSTMENTS</td>
                            @foreach ($preMonths as $label)
                                <td class="text-right">{{ $fmt($table_data['collection_adjustments'][$label] ?? 0) }}</td>
                            @endforeach
                            @foreach ($cnaYears as $year)
                                <td class="text-right">0</td>
                            @endforeach
                            @foreach ($annualYears as $year)
                                <td class="text-right">0</td>
                            @endforeach
                            @foreach ($postMonths as $label)
                                <td class="text-right">{{ $fmt($table_data['collection_adjustments'][$label] ?? 0) }}</td>
                            @endforeach
                            <td class="text-right">{{ $fmt($table_data['collection_adjustments']['total'] ?? 0) }}</td>
                        </tr>
                    @endif

                    <tr class="font-bold">
                        <td colspan="2" class="text-left">BALANCE COLLECTIBLE:</td>
                        @foreach ($balanceCells as $amount)
                            <td class="text-right">{{ $fmt($amount) }}</td>
                        @endforeach
                        <td class="text-right">{{ $fmt($table_data['balance_collectible']['total'] ?? 0) }}</td>
                    </tr>

                    @if ($hasAdjustments)
                        <!-- Adjustment notes under their month (e.g. "Overdeduction P100") -->
                        <tr>
                            <td colspan="2" class="no-border"></td>
                            @foreach ($preMonths as $label)
                                @include('reports.partials.faculty-deduction-adjustment-note', ['adjustments' => $table_data['collection_adjustment_details'][$label] ?? []])
                            @endforeach
                            <td colspan="{{ $cnaYears->count() + $annualYears->count() }}" class="no-border"></td>
                            @foreach ($postMonths as $label)
                                @include('reports.partials.faculty-deduction-adjustment-note', ['adjustments' => $table_data['collection_adjustment_details'][$label] ?? []])
                            @endforeach
                            <td class="no-border"></td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- Document Notes and Adjustments Block -->
            {{-- <div
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
            </div> --}}

            <!-- Layout Corporate Executive Sign-Off Matrix -->
            <div class="signatures-row">

                <!-- Prepared By Block -->
                <div class="sig-block">
                    <span class="sig-meta-title">Prepared by:</span>
                    <div class="sig-underline-container">
                        <div class="sig-name"> {{ $signatories['campusTreasurer'] ?? '' }}</div>
                    </div>
                    <span class="sig-assignment">Campus FA Treasurer</span>
                    <span class="sig-date">Date: ________________________</span>
                </div>

                <!-- Audited By Block -->
                <div class="sig-block">
                    <span class="sig-meta-title">Audited by:</span>
                    <div class="sig-underline-container">
                        <div class="sig-name">{{ $signatories['campus_auditor'] ?? '' }}</div>
                    </div>
                    <span class="sig-assignment">Campus FA Auditor</span>
                    <span class="sig-date">Date: ________________________</span>
                </div>

                <!-- Approved By Block -->
                <div class="sig-block">
                    <span class="sig-meta-title">Approved by:</span>
                    <div class="sig-underline-container">
                        <div class="sig-name">{{ $signatories['campus_president'] ?? '' }}</div>
                    </div>
                    <span class="sig-assignment">FA President</span>
                    <span class="sig-date">Date: ________________________</span>
                </div>

            </div>

        </div>
        <!-- END OF MAIN LEDGER PRINT AREA -->

    </body>
</html>
