<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Racun #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }
        .header {
            margin-bottom: 20px;
        }
        .business-info {
            float: left;
            width: 50%;
        }
        .invoice-info {
            float: right;
            width: 50%;
            text-align: right;
        }
        .customer-info {
            margin-bottom: 20px;
        }
        .invoice-title {
            text-align: center;
            font-size: 18px;
            margin: 30px 0;
            clear: both;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        .text-right {
            text-align: right;
        }
        .total {
            font-weight: bold;
        }
        .notes {
            margin-top: 20px;
            padding: 10px;
            border-top: 1px solid #ddd;
        }
        .signature {
            margin-top: 80px;
            text-align: right;
        }
        .footer {
            margin-top: 50px;
            font-size: 10px;
            text-align: center;
            color: #666;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="business-info">
            <h3>{{ $business->name }}</h3>
            <p>
                {{ $business->address }}<br>
                OIB: {{ $business->oib }}<br>
                IBAN: {{ $business->iban }}<br>
                Tel: {{ $business->phone }}<br>
                Email: {{ $business->email }}
            </p>
        </div>

        <div class="invoice-info">
            <h3>Racun #{{ $invoice->id }}</h3>
            <p>
                Datum izdavanja: {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}<br>
                Datum isporuke: {{ \Carbon\Carbon::parse($invoice->delivery_date)->format('d.m.Y') }}<br>
                Datum dospijeca: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}<br>
                Mjesto izdavanja: {{ $business->location ?? 'Zagreb' }}
            </p>
        </div>
    </div>

    <div class="customer-info">
        <h4>Kupac:</h4>
        <p>
            {{ $invoice->customer->name }}<br>
            {{ $invoice->customer->address }}<br>
            {{ $invoice->customer->city }}<br>
            OIB: {{ $invoice->customer->oib }}
        </p>
    </div>

    <div class="invoice-title">
        <h2>RACUN br. {{ $invoice->id }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">R.br.</th>
                <th width="45%">Opis</th>
                <th width="10%">Kolicina</th>
                <th width="10%">Jed. cijena</th>
                <th width="10%">Popust (%)</th>
                <th width="20%">Ukupno (EUR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $item->discount > 0 ? number_format($item->discount, 2, ',', '.') : '0,00' }}</td>
                    <td class="text-right">{{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="5" class="text-right">UKUPNO:</td>
                <td class="text-right">{{ number_format($invoice->total_amount, 2, ',', '.') }} EUR</td>
            </tr>
        </tfoot>
    </table>

    @if($invoice->note)
        <div class="notes">
            <h4>Napomena:</h4>
            <p>{{ $invoice->note }}</p>
        </div>
    @endif

    <div class="signature">
        <p>____________________________</p>
        <p>Potpis i pečat</p>
    </div>

    <div class="footer">
        <p>
            {{ $business->name }} | OIB: {{ $business->oib }} | IBAN: {{ $business->iban }}<br>
            {{ $business->address }} | Tel: {{ $business->phone }} | Email: {{ $business->email }}
        </p>
    </div>
</body>
</html>
