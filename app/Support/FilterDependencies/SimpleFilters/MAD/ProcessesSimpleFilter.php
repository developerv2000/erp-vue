<?php

namespace App\Support\FilterDependencies\SimpleFilters\MAD;

use App\Models\Country;
use App\Models\ManufacturerCategory;
use App\Models\MarketingAuthorizationHolder;
use App\Models\Process;
use App\Models\ProcessGeneralStatus;
use App\Models\ProcessResponsiblePerson;
use App\Models\Product;
use App\Models\ProductClass;
use App\Models\User;

class ProcessesSimpleFilter
{
    public static function getAllDependencies()
    {
        return [
            'deadlineStatusOptions' => Process::getDeadlineStatusOptions(),
            'countriesOrderedByName' => Country::orderByName()->get(),
            'analystUsers' => User::getMADAnalystsMinified(),
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'responsiblePeople' => ProcessResponsiblePerson::orderByName()->get(),
            'MAHs' => MarketingAuthorizationHolder::orderByName()->get(),
            'productClasses' => ProductClass::orderByName()->get(),
            'manufacturerCategories' => ManufacturerCategory::orderByName()->get(),
            'generalStatuses' => ProcessGeneralStatus::all(),
            'generalStatusNamesForAnalysts' => ProcessGeneralStatus::getUniqueNamesForAnalysts(),
            'regions' => Country::getRegionOptions(),
            'brands' => Product::getAllUniqueBrands(),
        ];
    }
}
