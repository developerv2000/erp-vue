<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use App\Models\User;
use App\Support\FilterDependencies\SimpleFilters\MAD\ManufacturersSimpleFilterDependencies;
use App\Support\FilterDependencies\SmartFilters\MAD\ManufacturersSmartFilterDependencies;
use App\Support\Traits\Controller\DestroysModelRecords;
use App\Support\Traits\Controller\RestoresModelRecords;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MADManufacturerController extends Controller
{
    use DestroysModelRecords;
    use RestoresModelRecords;

    // used in multiple destroy/restore traits
    public static $model = Manufacturer::class;

    public function index(Request $request)
    {
        $getAllTableHeaders = fn() => $request->user()->collectTableHeadersBySettingsKey(User::SETTINGS_KEY_OF_MAD_EPP_TABLE);
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
        $getAllTableHeaders = fn() => $request->user()->collectTableHeadersBySettingsKey(User::SETTINGS_KEY_OF_MAD_EPP_TABLE);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleHeaders($getAllTableHeaders());

        return Inertia::render('departments/MAD/pages/manufacturers/Trash', [
            'allTableHeaders' => $getAllTableHeaders, // Lazy load
            'tableVisibleHeaders' => $getVisibleHeaders, // Lazy load
            'simpleFilterDependencies' => fn() => ManufacturersSimpleFilterDependencies::getAllDependencies(), // Lazy load
            'smartFilterDependencies' => ManufacturersSmartFilterDependencies::getAllDependencies(),
        ]);
    }
}
