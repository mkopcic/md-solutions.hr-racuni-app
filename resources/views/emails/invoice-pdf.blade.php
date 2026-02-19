<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Račun</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #1E40AF;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .invoice-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
        }
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details td {
            padding: 8px;
            border-bottom: 1px solid #f3f4f6;
        }
        .invoice-details td:first-child {
            font-weight: bold;
            color: #6b7280;
            width: 40%;
        }
        .footer {
            background-color: #f3f4f6;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #1E40AF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #1E40AF;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📄 {{ $business->name }}</h1>
        <p style="margin: 0;">Račun #{{ $invoice->full_invoice_number }}</p>
    </div>

    <div class="content">
        <p>Poštovani,</p>

        <p>U prilogu se nalazi račun za kupca <strong>{{ $customerName }}</strong>.</p>

        <div class="invoice-details">
            <table>
                <tr>
                    <td>Broj računa:</td>
                    <td><strong>{{ $invoice->full_invoice_number }}</strong></td>
                </tr>
                <tr>
                    <td>Datum izdavanja:</td>
                    <td>{{ $invoice->issue_date->format('d.m.Y') }}</td>
                </tr>
                <tr>
                    <td>Rok plaćanja:</td>
                    <td>{{ $invoice->due_date->format('d.m.Y') }}</td>
                </tr>
                <tr>
                    <td>Kupac:</td>
                    <td>{{ $customerName }}</td>
                </tr>
            </table>

            <div class="amount">
                Ukupno: {{ $totalAmount }} €
            </div>
        </div>

        <p>PDF račun se nalazi u prilogu ovog emaila.</p>

        <p style="margin-top: 30px;">S poštovanjem,<br>
        <strong>{{ $business->name }}</strong></p>
    </div>

    <div class="footer">
        <p><strong>{{ $business->name }}</strong></p>
        <p>{{ $business->address }} | OIB: {{ $business->oib }}</p>
        <p>IBAN: {{ $business->iban }}</p>
        @if($business->email)
        <p>Email: {{ $business->email }}</p>
        @endif
        @if($business->phone)
        <p>Tel: {{ $business->phone }}</p>
        @endif
    </div>
</body>
</html>
