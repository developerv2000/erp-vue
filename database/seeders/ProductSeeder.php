<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductClass;
use App\Models\ProductForm;
use App\Models\ProductShelfLife;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory()->count(10)->create();

        // Create 6 special products with ids = 11, 12, 13, 14, 15, 16
        // for 2 special manufacturers with id = 11, 12
        $classCount = ProductClass::count();
        $shelfLifeCount = ProductShelfLife::count();
        $zonesCount = Zone::count();
        $formCount = ProductForm::count();

        $manufacturerIDs = [
            11,
            11,
            11,
            12,
            12,
            12,
        ];

        $innIDs = [
            1,
            1,
            2,
            2,
            3,
            4,
        ];

        foreach ($innIDs as $key => $value) {
            $product = Product::create([
                'manufacturer_id' => $manufacturerIDs[$key],
                'inn_id' => $value,
                'brand' => fake()->name(),
                'form_id' => rand(1, $formCount),
                'class_id' => rand(1, $classCount),
                'dosage' => fake()->numberBetween(10, 100),
                'pack' => fake()->numberBetween(10, 100) . ' ML',
                'moq' => fake()->numberBetween(10, 1000),
                'shelf_life_id' => rand(1, $shelfLifeCount),
                'dossier' => fake()->sentence(),
                'bioequivalence' => fake()->name() . ' ' . fake()->numberBetween(1, 5000),
                'down_payment' => fake()->numberBetween(1, 10000) . ' $',
                'validity_period' => fake()->dateTimeBetween('-2 year', 'now'),
                'registered_in_eu' => fake()->boolean(),
                'sold_in_eu' => fake()->boolean(),
            ]);

            $product->zones()->attach(rand(1, $zonesCount));
        }
    }
}
