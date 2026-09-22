<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $context['title'] ?? 'Summary Report' }}</title>
    <style>
        /* --- Screen Styling (For browser viewing) --- */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
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
        
        /* Document Container */
        .print {
            background-color: white;
            max-width: 8.5in;
            margin: 0 auto;
            padding: 0.5in 0.75in;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }

        /* Letterhead & Meta Block Layout */
        .header {
            text-align: center;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        .header h1 { font-size: 14pt; margin: 0; font-weight: bold; text-transform: uppercase; }
        .header h2 { font-size: 12pt; margin: 5px 0; font-weight: bold; text-transform: uppercase; }
        .header p { font-size: 10pt; margin: 0; }
        
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 25px;
            font-size: 10pt;
            background-color: #fafafa;
        }
        .meta-item { margin-bottom: 4px; }

        /* KPI Metric Blocks Grid */
        .metrics-summary-title {
            font-weight: bold;
            font-size: 11pt;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 1.5px solid #000;
            padding-bottom: 3px;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 25px;
        }
        .metric-card {
            border: 1px solid #000;
            padding: 8px 12px;
            text-align: center;
        }
        .metric-label {
            font-size: 9pt;
            text-transform: uppercase;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }
        .metric-value {
            font-size: 16pt;
            font-weight: bold;
        }
        
        /* Table Layout Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
        }
        th, td {
            border: 1px solid black;
            padding: 6px 6px;
        }
        th {
            text-align: center;
            font-weight: bold;
            background-color: #f2f2f2;
            text-transform: uppercase;
            font-size: 9pt;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .highlight-row { background-color: #f9f9f9; font-weight: bold; }
        
        /* Document Footers */
        .system-footer {
            margin-top: 40px;
            font-size: 8.5pt;
            font-style: italic;
            color: #555;
            display: flex;
            justify-content: space-between;
            border-top: 1px dashed #aaa;
            padding-top: 5px;
        }

        /* --- PRINT ONLY STYLING --- */
        @media print {
            body, html {
                background: none !important;
                background-color: transparent !important;
                margin: 0;
                padding: 0;
            }
            .no-print-zone {
                display: none !important;
            }
            .print {
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            @page {
                size: letter;
                margin: 0.75in;
            }
            tr { page-break-inside: avoid; }
            thead { display: table-header-group; }
            th { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="no-print-zone">
        <p><strong>Screen View:</strong> Use the action button below to trigger printer layout controls.</p>
        <button onclick="window.print()">Print Report</button>
    </div>

    <!-- START OF PRINT CONTAINER -->
    <div class="print">
        
        <!-- Header Section -->
        <div class="header">
            <p>Tagbilaran City, 6300, Bohol, Philippines</p>
            <h1>Office of the Faculty Association</h1>
            <h2>{{ $context['title'] }}</h2>
        </div>

        <!-- Meta Filters Metadata Block -->
        <div class="meta-grid">
            <div>
                @foreach(array_slice($context['filters'], 0, 2) as $filter)
                    <div class="meta-item"><strong>{{ $filter['label'] }}:</strong> {{ $filter['value'] }}</div>
                @endforeach
            </div>
            <div style="text-align: right;">
                <div class="meta-item"><strong>Generated On:</strong> {{ $context['generated_at'] }}</div>
                @foreach(array_slice($context['filters'], 2) as $filter)
                    <div class="meta-item"><strong>{{ $filter['label'] }}:</strong> {{ $filter['value'] }}</div>
                @endforeach
            </div>
        </div>

        <!-- Metric KPI Cards Dashboard Breakdown -->
        <div class="metrics-summary-title">{{ $context['description'] }}</div>
        <div class="metrics-grid">
            @foreach($cards as $card)
                <div class="metric-card">
                    <div class="metric-label">{{ $card['label'] }}</div>
                    <div class="metric-value">{{ $card['value'] }}</div>
                </div>
            @endforeach
        </div>

        <!-- Main Claims Statistics Table -->
        <div class="metrics-summary-title" style="margin-top: 15px;">Monthly Breakdowns</div>
        <table>
            <thead>
                <tr>
                    @foreach($columns as $column)
                        <th>{{ $column['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                <tr class="{{ ($row['claims'] ?? 0) > 0 ? 'highlight-row' : '' }}">
                        @foreach($columns as $column)
                            <td class="{{ $column['type'] === 'number' ? 'text-center' : 'text-left' }}">
                                {{ $row[$column['key']] }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                
                <!-- Summary Totals Row -->
                <tr class="font-bold" style="background-color: #f2f2f2;">
                    <td>TOTALS</td>
                    @foreach(array_slice($columns, 1) as $column)
                        @php
                            // Match column metric values using the cards array config
                            $cardMatch = collect($cards)->firstWhere('key', $column['key']);
                        @endphp
                        <td class="text-center">
                            {{ $cardMatch ? $cardMatch['value'] : collect($rows)->sum(fn($row) => (float) ($row[$column['key']] ?? 0)) }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>

        <!-- Signatures Layout Block -->
        <div class="signatures-grid" style="margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 50px; page-break-inside: avoid;">
            <div class="sig-block">
                <div class="sig-label" style="margin-bottom: 40px;">Prepared by:</div>
                <div class="sig-line" style="border-bottom: 1px solid black; margin-bottom: 3px;"></div>
                <div class="sig-name" style="font-weight: bold; text-transform: uppercase; font-size: 11pt;">{{ $preparedByName ?? 'Association Secretary' }}</div>
                <div class="sig-title" style="font-size: 10pt; color: #333;">Faculty Association Secretary / Representative</div>
            </div>

            <div class="sig-block">
                <div class="sig-label" style="margin-bottom: 40px;">Noted & Approved by:</div>
                <div class="sig-line" style="border-bottom: 1px solid black; margin-bottom: 3px;"></div>
                <div class="sig-name" style="font-weight: bold; text-transform: uppercase; font-size: 11pt;">{{ $approvedByName ?? 'Association President' }}</div>
                <div class="sig-title" style="font-size: 10pt; color: #333;">Faculty Association President</div>
            </div>
        </div>

        <!-- Document Footer Information Block -->
        <div class="system-footer">
            <div>Generated using Faculty Association Claims Monitoring System</div>
            <div>Report Year Range: {{ collect($context['filters'])->firstWhere('label', 'Reporting Year')['value'] ?? 'All' }}</div>
        </div>

    </div>
</body>
</html>
