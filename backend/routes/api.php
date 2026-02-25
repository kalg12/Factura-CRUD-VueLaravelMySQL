<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\InvoiceController;

Route::apiResource('companies', CompanyController::class);
Route::apiResource('clients', ClientController::class);

Route::get('invoices/export/pdf', [InvoiceController::class, 'exportPdf'])->name('invoices.export.pdf');
Route::get('invoices/export/csv', [InvoiceController::class, 'exportCsv'])->name('invoices.export.csv');
Route::apiResource('invoices', InvoiceController::class);

