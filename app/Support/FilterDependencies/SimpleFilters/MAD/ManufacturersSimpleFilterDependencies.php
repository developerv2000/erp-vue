<?php

namespace App\Support\FilterDependencies\SimpleFilters\MAD;

use App\Models\User;

class ManufacturersSimpleFilterDependencies
{
    public static function getAllDependencies()
    {
        return [
            'bdmUsers' => User::getCMDBDMsMinifed(),
        ];
    }
}
