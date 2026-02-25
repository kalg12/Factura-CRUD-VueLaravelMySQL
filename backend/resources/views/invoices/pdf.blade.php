<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $invoice->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { margin-bottom: 24px; border-bottom: 2px solid #333; padding-bottom: 12px; }
        .company { font-weight: bold; font-size: 14px; }
        .meta { margin: 16px 0; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        .totals { margin-top: 16px; max-width: 280px; margin-left: auto; }
        .totals tr td:first-child { font-weight: bold; }
        .totals td { border: none; padding: 4px 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">{{ $invoice->company->name }}</div>
        <div>RFC: {{ $invoice->company->rfc }}</div>
        @if($invoice->company->address)
        <div>{{ $invoice->company->address }}</div>
        @endif
    </div>

    <h2>Factura #{{ $invoice->id }}</h2>

    <div class="meta">
        <strong>Fecha:</strong> {{ $invoice->date->format('d/m/Y') }} &nbsp;|&nbsp;
        <strong>Estado:</strong> {{ $invoice->status }} &nbsp;|&nbsp;
        <strong>Cliente:</strong> {{ $invoice->client->name }}
        @if($invoice->client->rfc) (RFC: {{ $invoice->client->rfc }}) @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">P. unitario</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ $invoice->currency }} {{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td>IVA (16%)</td><td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td></tr>
        <tr><td><strong>Total</strong></td><td class="text-right"><strong>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</strong></td></tr>
    </table>
</body>
</html>
