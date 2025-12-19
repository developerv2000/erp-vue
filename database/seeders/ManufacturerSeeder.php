<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Manufacturer;
use App\Models\ManufacturerCategory;
use App\Models\ProductClass;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ManufacturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Manufacturer::factory(10)->create();

        // Create 2 special manufacturers for testing
        $zonesCount = Zone::count();
        $categoryCount = ManufacturerCategory::count();
        $productclassCount = ProductClass::count();

        $bdmID = User::where('name', 'Irini Kouimtzidou')->first()->id;
        $analystID = User::where('name', 'Nuruloev Olimjon')->first()->id;
        $countryID = Country::where('name', 'Russia')->first()->id;

        $names = [
            'Good Factory',
            'Best Factory',
        ];

        foreach ($names as $name) {
            $manufacturer = Manufacturer::create([ // id = 11, 12
                'name' => $name,
                'website' => 'https://' . fake()->domainName(),
                'about' => fake()->sentences(2, true),
                'relationship' => fake()->sentences(1, true),
                'active' => fake()->boolean(),
                'important' => fake()->boolean(),
                'bdm_user_id' => $bdmID, // Irini Kouimtzidou
                'analyst_user_id' => $analystID, // Nuruloev Olimjon
                'category_id' => rand(1, $categoryCount),
                'country_id' => $countryID, // Russia
                'created_at' => fake()->dateTimeBetween('-5 days', 'now'),
            ]);

            $manufacturer->zones()->attach(rand(1, $zonesCount));
            $manufacturer->productClasses()->attach(rand(1, $productclassCount));
        }
    }
}
