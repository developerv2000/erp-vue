<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'Tajikistan', 'code' => 'TJ'],
            ['name' => 'Kazakstan', 'code' => 'KZ'],
            ['name' => 'Dominican Republic', 'code' => 'DO'],
            ['name' => 'Guatemala', 'code' => 'GT'],
            ['name' => 'Russia', 'code' => 'RU'],
            ['name' => 'Azerbaijan', 'code' => 'AZ'],
            ['name' => 'Moldova', 'code' => 'MD'],
            ['name' => 'India', 'code' => 'IN'],
        ];

        $orderedCountries = collect($countries)->sortBy('name');

        foreach ($orderedCountries as $country) {
            Country::create([
                'name' => $country['name'],
                'code' => $country['code'],
            ]);
        }
    }
}
