<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'Dominican Republic', 'code' =>	'DO'],
            ['name' => 'Guatemala', 'code' =>	'GT'],
            ['name' => 'Russia', 'code' =>	'RU'],
            ['name' => 'Azerbaijan', 'code' =>	'AZ'],
            ['name' => 'Moldova', 'code' =>	'MD'],
            ['name' => 'Kenya', 'code' =>	'KE'],
            ['name' => 'Tanzania', 'code' =>	'TZ'],
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
