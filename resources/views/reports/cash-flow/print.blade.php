@php
    $money = fn ($value) => number_format((float) $value, 2);
    $line = fn ($item) => $item->label . ($item->notes ? ' (' . $item->notes . ')' : '');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Cash Flow</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #000;
        }

        .no-print-zone {
            max-width: 8.5in;
            margin: 0 auto 20px auto;
            background: #fff3cd;
            padding: 15px;
            border: 1px solid #ffeeba;
            text-align: center;
        }

        .no-print-zone button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
        }

        .draft-note {
            margin: 8px 0 0;
            font-size: 13px;
            color: #8a6d3b;
        }

        .print {
            background-color: #fff;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 56px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        /* Header (shared with the other printable reports) */
        .report-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .header-logo-left, .header-logo-right { height: 80px; display: flex; align-items: center; justify-content: center; }
        .header-logo-left { width: 80px; }
        .header-logo-right { width: 200px; }
        .header-text { text-align: center; flex-grow: 1; padding: 0 15px; }
        .header-text h2 { font-size: 16px; font-weight: bold; margin: 0 0 4px 0; text-transform: uppercase; }
        .header-text p { font-size: 12px; margin: 0 0 2px 0; line-height: 1.3; }
        .header-text .motto { font-style: italic; font-size: 11px; margin-top: 5px; }

        .report-title { text-align: center; margin-bottom: 28px; font-size: 15px; font-weight: bold; line-height: 1.35; }

        .cash-flow { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        .cash-flow td { padding: 1px 0; vertical-align: top; }
        .cash-flow .amount { width: 22%; text-align: right; white-space: nowrap; }
        .cash-flow .total-label { text-align: right; padding-right: 24px; font-weight: bold; }
        .cash-flow .total-amount { font-weight: bold; border-top: 1px solid #000; }
        .cash-flow .grand-total td { padding-top: 6px; }
        .cash-flow .cash-on-hand .total-amount { border-bottom: 3px double #000; border-top: none; }
        .section-head td { padding-top: 14px; }
        .indent-1 { padding-left: 28px !important; }
        .indent-2 { padding-left: 56px !important; }
        .indent-3 { padding-left: 92px !important; }
        .muted { font-style: italic; color: #555; }

        .signatures-section { margin-top: 56px; font-size: 13px; display: flex; justify-content: space-between; gap: 48px; }
        .sig-block { width: 45%; }
        .sig-line { border-bottom: 1px solid #000; height: 35px; margin-bottom: 4px; }
        .sig-name { font-weight: bold; text-transform: uppercase; min-height: 16px; }

        @media print {
            body { background-color: #fff; padding: 0; }
            .no-print-zone { display: none; }
            .print { box-shadow: none; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body @if(request()->boolean('auto_print')) onload="window.print()" @endif>

    <div class="no-print-zone">
        <p><strong>Screen View:</strong> Only the document container below will be printed.</p>
        <button onclick="window.print()">Print Report</button>
        @unless($period->isFinalized())
            <p class="draft-note">Draft: figures and signatories may still change until the period is finalized.</p>
        @endunless
    </div>

    <div class="print">
        <x-bisu-header :campusName="$campus?->campus_name ?? ''" :address="$campus?->location ? $campus->location . ', Bohol, Philippines' : ''" />

        <div class="report-title">
            <div>{{ $associationName }}</div>
            <div>CAMPUS CASH FLOW</div>
            <div>(As of {{ $asOf }})</div>
        </div>

        <table class="cash-flow">
            <tbody>
                <tr>
                    <td><strong>Forwarded Balance:</strong> <em>{{ $period->forwarded_label }}</em></td>
                    <td class="amount">{{ $money($period->forwarded_balance) }}</td>
                </tr>

                <tr class="section-head">
                    <td><strong>Add Funds:</strong> <em>(Received from FFA)</em></td>
                    <td></td>
                </tr>
                @forelse($funds as $item)
                    <tr>
                        <td class="indent-1">{{ $line($item) }}</td>
                        <td class="amount">{{ $money($item->amount) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="indent-1 muted">No funds received for this period.</td>
                        <td></td>
                    </tr>
                @endforelse
                <tr class="grand-total">
                    <td class="total-label">Total:</td>
                    <td class="amount total-amount">P{{ $money($period->total_funds) }}</td>
                </tr>

                <tr class="section-head">
                    <td><strong>Less</strong> <em>(Disbursements and Expenses)</em></td>
                    <td></td>
                </tr>
                @foreach($expenses as $item)
                    <tr>
                        <td class="indent-1">{{ $line($item) }}</td>
                        <td class="amount">{{ $money($item->amount) }}</td>
                    </tr>
                @endforeach

                @if($medicalByMonth->isNotEmpty())
                    <tr>
                        <td class="indent-1">Medical Assistance Reimbursement (P{{ $money($medicalTotal) }})</td>
                        <td></td>
                    </tr>
                    @foreach($medicalByMonth as $month => $items)
                        <tr>
                            <td class="indent-2">{{ $month }}:</td>
                            <td></td>
                        </tr>
                        @foreach($items as $item)
                            <tr>
                                <td class="indent-3">{{ $line($item) }}</td>
                                <td class="amount">{{ $money($item->amount) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @endif

                @if($expenses->isEmpty() && $medicalByMonth->isEmpty())
                    <tr>
                        <td class="indent-1 muted">No disbursements recorded for this period.</td>
                        <td></td>
                    </tr>
                @endif

                <tr class="grand-total">
                    <td class="total-label">Total</td>
                    <td class="amount total-amount">P{{ $money($period->total_disbursements) }}</td>
                </tr>
                <tr class="cash-on-hand">
                    <td class="total-label">Cash on Hand</td>
                    <td class="amount total-amount">P{{ $money($period->cash_on_hand) }}</td>
                </tr>
            </tbody>
        </table>

        <footer class="signatures-section">
            <div class="sig-block">
                <p>Prepared by:</p>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $preparedBy }}</div>
            </div>
            <div class="sig-block">
                <p>Checked by:</p>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $checkedBy }}</div>
            </div>
        </footer>
    </div>

</body>
</html>
