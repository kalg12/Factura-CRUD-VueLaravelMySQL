<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\InvoiceController;

Route::apiResource('companies', CompanyController::class);
Route::apiResource('clients', ClientController::class);
Route::apiResource('invoices', InvoiceController::class);

