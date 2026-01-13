<?php

namespace Database\Seeders;

use App\Models\ProcessResponsiblePerson;
use Illuminate\Database\Seeder;

class ProcessResponsiblePersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = [
            'БДМ',
            'КПГ',
        ];

        for ($i = 0; $i < count($name); $i++) {
            $record = new ProcessResponsiblePerson();
            $record->name = $name[$i];
            $record->save();
        }
    }
}
