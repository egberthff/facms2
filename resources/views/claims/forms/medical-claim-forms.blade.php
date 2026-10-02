<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FCFA Medical Expenses Claim Forms - {{ $claim->claim_number }}</title>
    <style>
        /* --- Screen Styling (For browser viewing) --- */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.22;
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

        /* Letter size simulation container on screen */
        .print {
            max-width: 8.5in;
            margin: 0 auto;
        }
        .form-page {
            background-color: white;
            min-height: 11in;
            padding: 0.45in 0.6in;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            box-sizing: border-box;
            position: relative;
        }

        /* Form Layout Formatting */
        .form-label {
            display: inline-block;
            border: 1px solid #000;
            padding: 2px 10px;
            font-weight: bold;
            font-style: italic;
            font-size: 11pt;
        }
        .header {
            text-align: center;
            line-height: 1.2;
            font-size: 11pt;
            margin-top: 6px;
        }
        .header .office { font-weight: bold; text-decoration: underline; }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin: 16px 0 4px 0;
        }
        .rule {
            text-align: center;
            letter-spacing: 1px;
            overflow: hidden;
            white-space: nowrap;
            margin-bottom: 6px;
        }
        .month-indicator {
            text-align: right;
            margin: 4px 0 12px 0;
        }
        .addressee { margin: 0 0 10px 0; line-height: 1.2; }
        .addressee strong { text-transform: uppercase; }
        .addressee strong:empty { display: inline-block; min-width: 2.6in; border-bottom: 1px solid #000; min-height: 1.1em; }
        .subject {
            font-weight: bold;
            margin: 10px 0 10px 0.6in;
        }
        p { margin: 0 0 8px 0; }
        .indent { text-indent: 0.6in; }
        .justify { text-align: justify; }

        /* Fill-in blanks: value printed on an underline with an italic caption below */
        .blank {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 1.6in;
            text-align: center;
            line-height: 1.1;
            vertical-align: bottom;
            text-indent: 0;
        }
        .blank:empty::before { content: "\00a0"; }
        .blank.sm { min-width: 0.9in; }
        .blank.lg { min-width: 3in; }
        .blank.xl { min-width: 4.2in; }
        .blank.full { display: block; width: 100%; }
        .field {
            display: inline-block;
            vertical-align: top;
            text-align: center;
            text-indent: 0;
        }
        .caption {
            display: block;
            font-style: italic;
            font-size: 10pt;
            line-height: 1.05;
            text-align: center;
        }

        /* Signatures */
        .sig-row {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            margin-top: 14px;
        }
        .sig-col { width: 48%; }
        .sig-right { margin-left: 58%; }
        .sig-name { font-weight: bold; text-transform: uppercase; }
        /* Unfilled officer: keep a signature line */
        .sig-name:empty { display: inline-block; min-width: 2.6in; border-bottom: 1px solid #000; min-height: 1.1em; }
        .sig-name.underline { text-decoration: underline; }
        .sig-line {
            border-top: 1px solid #000;
            width: 3in;
            margin: 36px auto 0 auto;
        }
        .center { text-align: center; }
        .italic { font-style: italic; }

        .note {
            color: #e00000;
            font-style: italic;
            text-align: justify;
            margin-top: 16px;
            font-size: 10.5pt;
        }

        /* --- PRINT ONLY STYLING --- */
        @media print {
            body, html, * {
                visibility: hidden;
                background: none !important;
                box-shadow: none !important;
            }
            .print, .print * {
                visibility: visible;
            }
            body { padding: 0; }
            .print {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: 100%;
                margin: 0;
            }
            .form-page {
                min-height: 0;
                padding: 0;
                margin: 0;
                page-break-after: always;
                break-after: page;
            }
            .form-page:last-child {
                page-break-after: auto;
                break-after: auto;
            }
            .note { color: #e00000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            @page {
                size: letter;
                margin: 0.45in 0.6in;
            }
        }
    </style>
</head>
<body>

    <!-- This section will be completely hidden during print -->
    <div class="no-print-zone">
        <p><strong>Screen View:</strong> Forms 1 to 4 for claim {{ $claim->claim_number }} will be printed on separate pages.</p>
        <button onclick="window.print()">Print Forms</button>
    </div>

    <!-- START OF PRINT CONTAINER -->
    <div class="print">
        @include('claims.forms.form-1-application-letter')
        @include('claims.forms.form-2-campus-endorsement')
        @include('claims.forms.form-3-president-endorsement')
        @include('claims.forms.form-4-acknowledgment-receipt')
    </div>
    <!-- END OF PRINT CONTAINER -->

</body>
</html>
