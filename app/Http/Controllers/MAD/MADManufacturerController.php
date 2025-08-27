<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Manufacturer;
use App\Models\ManufacturerBlacklist;
use App\Models\ManufacturerCategory;
use App\Models\ProductClass;
use App\Models\User;
use App\Models\Zone;
use App\Support\FilterDependencies\SimpleFilters\MAD\ManufacturersSimpleFilterDependencies;
use App\Support\FilterDependencies\SmartFilters\MAD\ManufacturersSmartFilterDependencies;
use App\Support\Traits\Controller\DestroysModelRecords;
use App\Support\Traits\Controller\PrependsTrashPageTableHeaders;
use App\Support\Traits\Controller\RestoresModelRecords;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MADManufacturerController extends Controller
{
    use DestroysModelRecords;
    use RestoresModelRecords;
    use PrependsTrashPageTableHeaders;

    // used in multiple destroy/restore traits
    public static $model = Manufacturer::class;

    public function index(Request $request)
    {
        $getAllTableHeaders = fn() => $request->user()->collectTableHeadersTranslatedFromSettings(User::SETTINGS_KEY_OF_MAD_EPP_TABLE);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleHeaders($getAllTableHeaders());

        return Inertia::render('departments/MAD/pages/manufacturers/Index', [
            'allTableHeaders' => $getAllTableHeaders, // Lazy load
            'tableVisibleHeaders' => $getVisibleHeaders, // Lazy load
            'simpleFilterDependencies' => fn() => ManufacturersSimpleFilterDependencies::getAllDependencies(), // Lazy load
            'smartFilterDependencies' => ManufacturersSmartFilterDependencies::getAllDependencies(),
        ]);
    }

    public function trash(Request $request)
    {
        $getAllTableHeaders = fn() => $this->prependTrashPageTableHeaders(
            $request->user()->collectTableHeadersTranslatedFromSettings(User::SETTINGS_KEY_OF_MAD_EPP_TABLE)
        );

        $getVisibleHeaders = fn() => User::filterOnlyVisibleHeaders($getAllTableHeaders());

        return Inertia::render('departments/MAD/pages/manufacturers/Trash', [
            'tableVisibleHeaders' => $getVisibleHeaders, // Lazy load
            'simpleFilterDependencies' => fn() => ManufacturersSimpleFilterDependencies::getAllDependencies(), // Lazy load
            'smartFilterDependencies' => ManufacturersSmartFilterDependencies::getAllDependencies(),
        ]);
    }

    public function create()
    {
        return Inertia::render('departments/MAD/pages/manufacturers/Create', [
            'analystUsers' => User::getMADAnalystsMinified(),
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'countriesOrderedByName' => Country::orderByName()->get(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
            'manufacturers' => Manufacturer::getMinifiedRecordsWithName(),
            'categories' => ManufacturerCategory::orderByName()->get(),
            'zones' => Zone::orderByName()->get(),
            'defaultSelectedZoneIDs' => Zone::getRelatedDefaultSelectedIDValues(),
            'productClasses' => ProductClass::orderByName()->get(),
            'blacklists' => ManufacturerBlacklist::orderByName()->get(),
            'statusOptions' => Manufacturer::getStatusOptions(),
        ]);
    }
}
