<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = [
            Department::MGMT_NAME,
            Department::MAD_NAME,
            Department::CMD_NAME,
            Department::PLD_NAME,
            Department::PRD_NAME,
            Department::PPDD_NAME,
            Department::ELD_NAME,
            Department::MD_NAME,
            Department::DD_NAME,
        ];

        $abbreviation = [
            Department::MGMT_ABBREVIATION,
            Department::MAD_ABBREVIATION,
            Department::CMD_ABBREVIATION,
            Department::PLD_ABBREVIATION,
            Department::PRD_ABBREVIATION,
            Department::PPDD_ABBREVIATION,
            Department::ELD_ABBREVIATION,
            Department::MD_ABBREVIATION,
            Department::DD_ABBREVIATION,
        ];

        for ($i = 0; $i < count($name); $i++) {
            Department::create([
                'name' => $name[$i],
                'abbreviation' => $abbreviation[$i],
            ]);
        }
    }
}
