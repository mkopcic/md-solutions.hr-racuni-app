<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Račun {{ $invoice->full_invoice_number ?? $invoice->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 15px 15px 70px 15px;
            margin: 0;
        }
        .page {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            position: relative;
        }

        /* HEADER */
        .header {
            margin-bottom: 15px;
            position: relative;
        }
        .logo-section {
            margin-bottom: 8px;
            margin-right: 48%;
        }
        .logo-text {
            font-size: 22px;
            font-weight: bold;
            color: #1E40AF;
            margin-bottom: 3px;
        }
        .logo-text::before {
            content: '</>';
            font-size: 20px;
            margin-right: 8px;
            color: #1E40AF;
        }
        .blue-line {
            height: 3px;
            background: #1E40AF;
            margin: 5px 0;
        }
        .tagline {
            font-size: 11px;
            color: #666;
            font-style: italic;
            margin-bottom: 10px;
        }

        /* BUSINESS INFO BOX */
        .business-box {
            position: absolute;
            right: 0;
            top: -5px;
            width: 45%;
            border: 1px solid #c7d2fe;
            border-top: 3px solid #1E40AF;
            padding: 8px;
            font-size: 9px;
            background: #f0f4ff;
            border-radius: 2px;
        }
        .business-box .title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 5px;
            color: #1E40AF;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* INVOICE NUMBER */
        .invoice-number {
            font-size: 13px;
            font-weight: bold;
            margin: 15px 0 10px 0;
        }

        /* CUSTOMER AND DATE INFO */
        .info-section {
            margin-bottom: 15px;
            font-size: 10px;
            border: 1px solid #e5e7eb;
            border-left: 3px solid #1E40AF;
            border-radius: 2px;
            background: #fafafa;
            padding: 4px 0;
        }
        .info-section table {
            width: 100%;
            border: none;
        }
        .info-section td {
            border: none;
            padding: 3px 8px;
            vertical-align: top;
        }
        .info-section .label {
            font-weight: bold;
            width: 20%;
            color: #374151;
        }
        .info-section .value {
            width: 30%;
        }

        /* ITEMS TABLE */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .items-table th {
            background: #1E40AF;
            color: white;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #1E40AF;
        }
        .items-table td {
            border: 1px solid #ddd;
            padding: 5px 4px;
            text-align: center;
        }
        .items-table td.text-left { text-align: left; }
        .items-table td.text-right { text-align: right; }
        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* TAX BREAKDOWN */
        .bottom-section {
            margin-top: 15px;
        }
        .tax-box {
            float: right;
            width: 48%;
            border: 1px solid #ddd;
            padding: 10px;
            background: #f9f9f9;
        }
        .tax-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
            font-size: 10px;
        }
        .tax-label {
            display: table-cell;
            width: 60%;
            text-align: left;
        }
        .tax-value {
            display: table-cell;
            width: 40%;
            text-align: right;
            font-weight: bold;
        }
        .tax-total {
            border-top: 2px solid #1E40AF;
            margin-top: 8px;
            padding-top: 8px;
            font-size: 12px;
            font-weight: bold;
            color: #1E40AF;
        }

        /* NOTES */
        .notes-section {
            clear: both;
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            background: #fffbf0;
            font-size: 9px;
        }
        .notes-section .title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* PAYMENT INFO */
        .payment-section {
            margin-top: 15px;
            font-size: 10px;
            border: 1px solid #c7d2fe;
            border-left: 3px solid #1E40AF;
            background: #f8faff;
            border-radius: 2px;
        }
        .payment-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-section td {
            padding: 7px 12px;
            border: none;
            vertical-align: middle;
        }
        .payment-section td + td {
            border-left: 1px solid #e0e7ff;
        }
        .payment-label {
            font-weight: bold;
            display: block;
            font-size: 8px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 2px;
        }

        /* QR CODE */
        .qr-section {
            margin-top: 12px;
            text-align: center;
        }
        .qr-code {
            display: inline-block;
            margin: 5px auto;
        }

        /* FOOTER - fixed at absolute bottom of A4 page */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 7px 15px;
            border-top: 2px solid #1E40AF;
            background: #f0f4ff;
            font-size: 8px;
            text-align: center;
            color: #444;
            line-height: 1.5;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- HEADER -->
        <div class="header">
            <div class="business-box">
                <div class="title">Ispis Fisk. Računa</div>
                <div>{{ $business->name }}</div>
                <div>{{ $business->address }}</div>
                <div>{{ $business->location ?? 'Zagreb' }}</div>
                <div>OIB: {{ $business->oib }}</div>
            </div>

            <div class="logo-section">
                <div class="logo-text">{{ $business->name }}</div>
                <div class="blue-line"></div>
                <div class="tagline">obrt za računalno programiranje</div>
            </div>

            <div class="invoice-number">
                RAČUN br: {{ $invoice->full_invoice_number ?? $invoice->id }}
            </div>
        </div>

        <!-- CUSTOMER AND DATE INFO -->
        <div class="info-section">
            <table>
                <tr>
                    <td class="label">Kupac:</td>
                    <td class="value"><strong>{{ $invoice->customer->name }}</strong></td>
                    <td class="label">Datum izdavanja:</td>
                    <td class="value">{{ $invoice->issue_date ? $invoice->issue_date->format('d.m.Y') : '' }}</td>
                </tr>
                <tr>
                    <td class="label"></td>
                    <td class="value">{{ $invoice->customer->address }}</td>
                    <td class="label">Datum isporuke:</td>
                    <td class="value">{{ $invoice->delivery_date ? $invoice->delivery_date->format('d.m.Y') : '' }}</td>
                </tr>
                <tr>
                    <td class="label"></td>
                    <td class="value">{{ $invoice->customer->city }}</td>
                    <td class="label">Rok plaćanja:</td>
                    <td class="value">{{ $invoice->due_date ? $invoice->due_date->format('d.m.Y') : '' }}</td>
                </tr>
                <tr>
                    <td class="label">OIB:</td>
                    <td class="value">{{ $invoice->customer->oib }}</td>
                    <td class="label"></td>
                    <td class="value"></td>
                </tr>
            </table>
        </div>

        <!-- ITEMS TABLE -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%">R.br</th>
                    <th style="width: 35%" class="text-left">Opis</th>
                    <th style="width: 8%">Jed.mj.</th>
                    <th style="width: 8%">Količina</th>
                    <th style="width: 12%">Cijena</th>
                    <th style="width: 12%">Iznos</th>
                    <th style="width: 10%">Popust</th>
                    <th style="width: 10%">PDV %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $item->name }}</td>
                    <td>{{ $item->unit ?? 'kom' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->total, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $item->discount > 0 ? number_format($item->discount, 2, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ number_format($item->tax_rate ?? 25, 2, ',', '.') }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TAX BREAKDOWN -->
        <div class="bottom-section clearfix">
            <div class="tax-box">
                <div class="tax-row">
                    <span class="tax-label">IZNOS (osnovica):</span>
                    <span class="tax-value">{{ number_format($invoice->subtotal ?? $invoice->total_amount * 0.8, 2, ',', '.') }} EUR</span>
                </div>
                <div class="tax-row">
                    <span class="tax-label">PDV/POREZ:</span>
                    <span class="tax-value">{{ number_format($invoice->items->first()->tax_rate ?? 25, 2, ',', '.') }}% (PDV)</span>
                </div>
                <div class="tax-row">
                    <span class="tax-label">PDV iznos:</span>
                    <span class="tax-value">{{ number_format($invoice->tax_total ?? $invoice->total_amount * 0.2, 2, ',', '.') }} EUR</span>
                </div>
                <div class="tax-row tax-total">
                    <span class="tax-label">UKUPNO:</span>
                    <span class="tax-value">{{ number_format($invoice->total_amount, 2, ',', '.') }} EUR</span>
                </div>
            </div>
        </div>

        <!-- NOTES -->
        @if($invoice->note || true)
        <div class="notes-section">
            <div class="title">Napomena:</div>
            <div>{{ $invoice->note ?? 'Obveznik je u sustavu PDV-a. Izvršena usluga. Račun izdan na dan prometa.' }}</div>
        </div>
        @endif

        <!-- PAYMENT INFO -->
        <div class="payment-section">
            <table>
                <tr>
                    <td style="width: 33%">
                        <span class="payment-label">Način plaćanja</span>
                        {{ ucfirst($invoice->payment_method ?? 'Virman') }}
                    </td>
                    <td style="width: 33%">
                        <span class="payment-label">Rok plaćanja</span>
                        {{ $invoice->due_date ? $invoice->due_date->format('d.m.Y') : '' }}
                    </td>
                    <td style="width: 34%">
                        <span class="payment-label">Mjesto i potpis</span>
                        _______________________
                    </td>
                </tr>
            </table>
        </div>

        <!-- PDF417 BARCODE (HUB3) -->
        @if(isset($qrCode))
        <div class="qr-section">
            <div class="qr-code">
                <img src="{{ $qrCode }}" width="400" height="140" />
            </div>
            <div style="font-size: 9px; margin-top: 5px; color: #666;">Skenirajte za plaćanje</div>
        </div>
        @endif

        <!-- FOOTER - fixed at bottom of A4 page -->
        <div class="footer">
            <div><strong>{{ $business->name }}</strong> &nbsp;|&nbsp; OIB: {{ $business->oib }} &nbsp;|&nbsp; IBAN: {{ $business->iban }} &nbsp;|&nbsp; {{ $business->address }} &nbsp;|&nbsp; Tel: {{ $business->phone }} &nbsp;|&nbsp; {{ $business->email }}</div>
            <div style="margin-top: 3px; border-top: 1px solid #c7d2fe; padding-top: 3px; color: #888;">
                Ovaj račun je računalom sastavljen i vrijedi bez pečata i potpisa.
            </div>
        </div>
    </div>
</body>
</html>
