<?php

namespace App\Http\Controllers\administration;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Support\FilterDependencies\SimpleFilters\administration\PermissionsSimpleFilter;
use App\Support\Helpers\ControllerHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('administration/pages/permissions/Index', [
            // Refetched only on locale change
            'allTableHeaders' => fn() => $this->getAllTableHeadersTranslated(),
            // Refetched on filter form submit
            'records' => fn() => Permission::queryRecordsFromRequest($request, 'get'),

            // Never refetched again
            'simpleFilterDependencies' => fn() => PermissionsSimpleFilter::getAllDependencies(),
        ]);
    }

    private function getAllTableHeadersTranslated(): Collection
    {
        $headers = collect([
            ['title' => "fields.Name", 'key' => 'name', 'width' => 280, 'sortable' => true,],
            ['title' => "Department", 'key' => 'department_id', 'width' => 120, 'sortable' => true,],
            ['title' => "properties.Global", 'key' => 'global', 'width' => 120, 'sortable' => true,],
            ['title' => "Users", 'key' => 'users_count', 'width' => 132, 'sortable' => true,],
            ['title' => "Roles", 'key' => 'roles_name', 'width' => 220, 'sortable' => false,],
        ]);

        ControllerHelper::translateTableHeadersTitle($headers);

        return $headers;
    }
}
