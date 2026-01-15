<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 1 special order with id = 1 and add 3 special created processes
        $manufacturerID = 11; // manufacturer_id of prcocesses->product
        $countryID = Country::where('name', 'Tajikistan')->first()->id; // country_id of processes

        $order = Order::create([
            'manufacturer_id' => $manufacturerID,
            'country_id' => $countryID,
            'receive_date' => '2026-01-01',
            'sent_to_bdm_date' => '2026-01-02 00:00:01',
            'name' => 'TJ - №1',
            'pdf_file' => 'sample.pdf',
            'purchase_date' => '2026-01-03',
            'currency_id' => 1, // USD
            'sent_to_confirmation_date' => '2026-01-04 00:00:01',
            'confirmation_date' => '2026-01-05 00:00:01',
            'sent_to_manufacturer_date' => '2026-01-06 00:00:01',
            'expected_dispatch_date' => 'Maybe tomorrow',
            'production_start_date' => '2026-01-07 00:00:01',
        ]);

        // Add 3 special created processes
        $processIDs = [11, 12, 13];
        $serializationTypeIDs = [1, 2, 3];
        $quantities = [100, 200, 300];
        $prices = [1, 2, 3];
        $productionEndDates = ['2026-01-08 00:00:01', '2026-01-09 00:00:01', null];
        $packingLists = ['sample.pdf', 'sample.pdf', null];
        $readinessForShipmentFromManufacturerDates = ['2026-01-10 00:00:01', '2026-01-11 00:00:01', null];

        foreach ($processIDs as $key => $value) {
            $order->products()->create([
                'process_id' => $value,
                'serialization_type_id' => $serializationTypeIDs[$key],
                'quantity' => $quantities[$key],
                'price' => $prices[$key],
                'production_end_date' => $productionEndDates[$key],
                'packing_list_file' => $packingLists[$key],
                'readiness_for_shipment_from_manufacturer_date' => $readinessForShipmentFromManufacturerDates[$key],
            ]);
        }
    }
}
