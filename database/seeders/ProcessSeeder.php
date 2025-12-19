<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use App\Models\MarketingAuthorizationHolder;
use App\Models\Process;
use App\Models\ProcessResponsiblePerson;
use App\Models\ProcessStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Process::factory(10)->create();

        // Create 6 special processes with ids = 11, 12, 13, 14, 15, 16
        // for 2 special manufacturers with id = 11, 12

        $registeredStatusiD = ProcessStatus::where('name', 'Р')->first()->id;
        $countryID = Country::where('name', 'Tajikistan')->first()->id;
        $responsiblePersonCount = ProcessResponsiblePerson::count();
        $currencyID = Currency::where('name', 'USD')->first()->id;
        $mahCount = MarketingAuthorizationHolder::count();

        $productIDs = [
            11,
            12,
            13,
            14,
            15,
            16,
        ];

        $trademarkEn = [
            'Pardifen',
            'Belasef',
            'Vitaspey',
            'Roza',
            'Svetox',
            'Gervetin',
        ];

        $trademarkRu = [
            'Пардифен',
            'Белацеф',
            'Витаспей',
            'Роза',
            'Цветокс',
            'Герветин',
        ];

        foreach ($productIDs as $Key => $value) {
            Process::create([
                'product_id' => $productIDs[$Key],
                'status_id' => $registeredStatusiD,
                'responsible_person_update_date' => now(),
                'responsible_person_id' => rand(1, $responsiblePersonCount),
                'days_past_since_last_activity' => 0,
                'country_id' => $countryID,
                'forecast_year_1' => rand(1, 10000),
                'forecast_year_2' => rand(1, 10000),
                'forecast_year_3' => rand(1, 10000),
                'forecast_year_1_update_date' => now(),
                'currency_id' => $currencyID,
                'manufacturer_first_offered_price' => '1',
                'manufacturer_followed_offered_price' => '2',
                'our_first_offered_price' => '1',
                'our_followed_offered_price' => '2',
                'marketing_authorization_holder_id' => rand(1, $mahCount),
                'trademark_en' => $trademarkEn[$Key],
                'trademark_ru' => $trademarkRu[$Key],
                'agreed_price' => 2,
                'contracted_in_asp' => true,
                'registered_in_asp' => true,
                'readiness_for_order_date' => fake()->dateTimeBetween('-2 month', 'now'),
            ]);
        }
    }
}
