<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::query()
            ->with(['company', 'client'])
            ->orderByDesc('id')
            ->paginate(10);

        return InvoiceResource::collection($invoices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $itemsData = $data['items'];
            unset($data['items']);

            [$subtotal, $tax, $total] = $this->calculateTotals($itemsData);

            $invoice = Invoice::create(array_merge($data, [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]));

            foreach ($itemsData as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return new InvoiceResource($invoice->load('items'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        return new InvoiceResource($invoice->load(['company', 'client', 'items']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        return DB::transaction(function () use ($request, $invoice) {
            $data = $request->validated();
            $itemsData = $data['items'] ?? null;
            unset($data['items']);

            if ($itemsData !== null) {
                InvoiceItem::where('invoice_id', $invoice->id)->delete();

                foreach ($itemsData as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total' => $item['quantity'] * $item['unit_price'],
                    ]);
                }

                [$subtotal, $tax, $total] = $this->calculateTotals($itemsData);

                $data['subtotal'] = $subtotal;
                $data['tax'] = $tax;
                $data['total'] = $total;
            }

            $invoice->update($data);

            return new InvoiceResource($invoice->load('items'));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return response()->noContent();
    }

    /**
     * Export selected invoices as PDF (one PDF per invoice, zipped would need extra package).
     * GET /api/invoices/export/pdf?ids[]=1&ids[]=2
     * Returns the first invoice PDF for simplicity; frontend can call once per id for multiple.
     */
    public function exportPdf(Request $request)
    {
        $ids = $request->query('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $ids = array_map('intval', (array) $ids);

        if (empty($ids)) {
            return response()->json(['message' => 'Se requiere al menos un id en ids[]'], 422);
        }

        $invoices = Invoice::with(['company', 'client', 'items'])
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        if ($invoices->isEmpty()) {
            return response()->json(['message' => 'No se encontraron facturas'], 404);
        }

        // Si solo hay una factura, devolver ese PDF
        if ($invoices->count() === 1) {
            $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoices->first()]);
            return $pdf->download('factura-' . $invoices->first()->id . '.pdf');
        }

        // Varias facturas: concatenar en un solo PDF (varias páginas)
        $pdf = Pdf::loadView('invoices.pdf-multi', ['invoices' => $invoices]);
        return $pdf->download('facturas-' . implode('-', $ids) . '.pdf');
    }

    /**
     * Export selected invoices as CSV.
     * GET /api/invoices/export/csv?ids[]=1&ids[]=2
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $ids = $request->query('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $ids = array_map('intval', (array) $ids);

        $invoices = Invoice::with(['company', 'client', 'items'])
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        $filename = 'facturas-' . date('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($invoices) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fputcsv($out, [
                'ID', 'Fecha', 'Estado', 'Empresa', 'Cliente', 'Subtotal', 'IVA', 'Total', 'Moneda',
            ], ';');

            foreach ($invoices as $inv) {
                fputcsv($out, [
                    $inv->id,
                    $inv->date->format('Y-m-d'),
                    $inv->status,
                    $inv->company->name ?? '',
                    $inv->client->name ?? '',
                    $inv->subtotal,
                    $inv->tax,
                    $inv->total,
                    $inv->currency,
                ], ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function calculateTotals(array $items): array
    {
        $subtotal = collect($items)->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        $tax = $subtotal * 0.16;
        $total = $subtotal + $tax;

        return [$subtotal, $tax, $total];
    }
}
