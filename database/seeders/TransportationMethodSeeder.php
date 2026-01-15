<?php

namespace Database\Seeders;

use App\Models\TransportationMethod;
use Illuminate\Database\Seeder;

class TransportationMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = [
            TransportationMethod::AUTO_NAME,
            TransportationMethod::AIR_NAME,
            TransportationMethod::SEA_NAME,
        ];

        for ($i = 0; $i < count($name); $i++) {
            $item = new TransportationMethod();
            $item->name = $name[$i];
            $item->save();
        }
    }
}
