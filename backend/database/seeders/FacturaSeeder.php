<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Seeder;

class FacturaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa1 = Company::create([
            'name' => 'Servicios Tech SA de CV',
            'rfc' => 'STE850101ABC',
            'person_type' => 'MORAL',
            'email' => 'contacto@serviciostech.mx',
            'phone' => '5551234567',
            'address' => 'Av. Reforma 123, CDMX',
        ]);

        $empresa2 = Company::create([
            'name' => 'Juan Pérez López',
            'rfc' => 'PELJ800101ABC',
            'person_type' => 'FISICA',
            'email' => 'juan.perez@email.com',
            'phone' => '5559876543',
            'address' => 'Calle Juárez 45, Monterrey',
        ]);

        $cliente1 = Client::create([
            'company_id' => $empresa1->id,
            'name' => 'Distribuidora Norte SA',
            'rfc' => 'DNS900101XYZ',
            'person_type' => 'MORAL',
            'email' => 'compras@distribuidoranorte.mx',
            'phone' => '8181112233',
            'address' => 'Blvd. Industrial 100, Monterrey',
        ]);

        $cliente2 = Client::create([
            'company_id' => $empresa1->id,
            'name' => 'María García Hernández',
            'rfc' => 'GAHM850505DEF',
            'person_type' => 'FISICA',
            'email' => 'maria.garcia@email.com',
            'phone' => '3334445566',
            'address' => 'Calle Hidalgo 78, Guadalajara',
        ]);

        $cliente3 = Client::create([
            'company_id' => $empresa2->id,
            'name' => 'Consultores Asociados SC',
            'rfc' => 'CAS950101GHI',
            'person_type' => 'MORAL',
            'email' => 'info@consultores.mx',
            'address' => 'Av. Insurgentes 200, CDMX',
        ]);

        $invoice1 = Invoice::create([
            'company_id' => $empresa1->id,
            'client_id' => $cliente1->id,
            'date' => now()->subDays(10),
            'status' => 'SENT',
            'subtotal' => 15000.00,
            'tax' => 2400.00,
            'total' => 17400.00,
            'currency' => 'MXN',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice1->id,
            'description' => 'Desarrollo de sitio web corporativo',
            'quantity' => 1,
            'unit_price' => 10000.00,
            'total' => 10000.00,
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice1->id,
            'description' => 'Mantenimiento mensual hosting',
            'quantity' => 5,
            'unit_price' => 1000.00,
            'total' => 5000.00,
        ]);

        $invoice2 = Invoice::create([
            'company_id' => $empresa1->id,
            'client_id' => $cliente2->id,
            'date' => now()->subDays(5),
            'status' => 'DRAFT',
            'subtotal' => 3500.00,
            'tax' => 560.00,
            'total' => 4060.00,
            'currency' => 'MXN',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice2->id,
            'description' => 'Consultoría SEO',
            'quantity' => 7,
            'unit_price' => 500.00,
            'total' => 3500.00,
        ]);

        $invoice3 = Invoice::create([
            'company_id' => $empresa2->id,
            'client_id' => $cliente3->id,
            'date' => now(),
            'status' => 'PAID',
            'subtotal' => 8000.00,
            'tax' => 1280.00,
            'total' => 9280.00,
            'currency' => 'MXN',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice3->id,
            'description' => 'Auditoría de sistemas',
            'quantity' => 1,
            'unit_price' => 5000.00,
            'total' => 5000.00,
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice3->id,
            'description' => 'Capacitación en sitio',
            'quantity' => 3,
            'unit_price' => 1000.00,
            'total' => 3000.00,
        ]);
    }
}
