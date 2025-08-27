<?php

namespace App\Support\FilterDependencies\SimpleFilters\MAD;

use App\Models\Country;
use App\Models\Manufacturer;
use App\Models\ManufacturerBlacklist;
use App\Models\ManufacturerCategory;
use App\Models\ProductClass;
use App\Models\User;
use App\Models\Zone;

class ManufacturersSimpleFilterDependencies
{
    public static function getAllDependencies()
    {
        return [
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'regions' => Country::getRegionOptions(),
            'categories' => ManufacturerCategory::orderByName()->get(),
            'productClasses' => ProductClass::orderByName()->get(),
            'zones' => Zone::orderByName()->get(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(), // used in has processes_for_country filter
            'blacklists' => ManufacturerBlacklist::orderByName()->get(),
        ];
    }
}
