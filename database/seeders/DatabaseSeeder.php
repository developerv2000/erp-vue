<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            CountrySeeder::class,
            UserSeeder::class,
            ManufacturerCategorySeeder::class,
            ManufacturerBlacklistSeeder::class,
            ZoneSeeder::class,
            ProductClassSeeder::class,
            ManufacturerSeeder::class,
            InnSeeder::class,
            ProductFormSeeder::class,
            ProductShelfLifeSeeder::class,
            ProductSeeder::class,
            // CurrencySeeder::class,
            // MarketingAuthorizationHolderSeeder::class,
            // ProcessResponsiblePersonSeeder::class,
            // ProcessGeneralStatusSeeder::class,
            // ProcessStatusSeeder::class,
            // ProcessSeeder::class,
            // ProductSearchStatusSeeder::class,
            // ProductSearchPrioritySeeder::class,
            // PortfolioManagerSeeder::class,
            // ProductSearchSeeder::class,
            // MeetingSeeder::class,
        ]);
    }
}
