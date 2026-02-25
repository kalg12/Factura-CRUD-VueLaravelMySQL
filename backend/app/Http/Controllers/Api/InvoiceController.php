<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

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
