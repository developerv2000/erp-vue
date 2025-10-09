<?php

namespace App\Support\FilterDependencies\SimpleFilters\MAD;

use App\Models\Country;
use App\Models\ManufacturerCategory;
use App\Models\Product;
use App\Models\ProductClass;
use App\Models\ProductShelfLife;
use App\Models\User;
use App\Models\Zone;

class ProductsSimpleFilterDependencies
{
    public static function getAllDependencies()
    {
        return [
            'analystUsers' => User::getMADAnalystsMinified(),
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'productClasses' => ProductClass::orderByName()->get(),
            'shelfLifes' => ProductShelfLife::all(),
            'zones' => Zone::orderByName()->get(),
            'countriesOrderedByName' => Country::orderByName()->get(),
            'manufacturerCategories' => ManufacturerCategory::orderByName()->get(),
            'brands' => Product::getAllUniqueBrands(),
        ];
    }
}
