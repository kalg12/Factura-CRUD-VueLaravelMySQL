<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Facturas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        .invoice-block { page-break-after: always; margin-bottom: 24px; }
        .invoice-block:last-child { page-break-after: auto; }
        .header { margin-bottom: 16px; border-bottom: 1px solid #333; padding-bottom: 8px; }
        .company { font-weight: bold; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        .totals { max-width: 240px; margin-left: auto; }
        .totals tr td:first-child { font-weight: bold; }
        .totals td { border: none; padding: 2px 6px; }
    </style>
</head>
<body>
    @foreach($invoices as $invoice)
    <div class="invoice-block">
        <div class="header">
            <span class="company">{{ $invoice->company->name }}</span> — RFC: {{ $invoice->company->rfc }}
        </div>
        <strong>Factura #{{ $invoice->id }}</strong> — {{ $invoice->date->format('d/m/Y') }} — {{ $invoice->client->name }}

        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="text-right">Cant.</th>
                    <th class="text-right">P. unit.</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr><td>Subtotal</td><td class="text-right">{{ number_format($invoice->subtotal, 2) }}</td></tr>
            <tr><td>IVA</td><td class="text-right">{{ number_format($invoice->tax, 2) }}</td></tr>
            <tr><td><strong>Total</strong></td><td class="text-right"><strong>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</strong></td></tr>
        </table>
    </div>
    @endforeach
</body>
</html>
