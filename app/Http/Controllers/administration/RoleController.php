<?php

namespace App\Http\Controllers\administration;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Helpers\ControllerHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('administration/pages/roles/Index', [
            // Refetched only on locale change
            'allTableHeaders' => fn() => $this->getAllTableHeadersTranslated(),
            // Refetched on filter form submit
            'records' => fn() => Role::queryRecordsFromRequest($request, 'get'),

            // Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
        ]);
    }

    private function getAllTableHeadersTranslated(): Collection
    {
        $headers = collect([
            ['title' => "fields.Name", 'key' => 'name', 'width' => 210, 'sortable' => true],
            ['title' => "Department", 'key' => 'department_id', 'width' => 120, 'sortable' => true],
            ['title' => "properties.Global", 'key' => 'global', 'width' => 120, 'sortable' => true],
            ['title' => "Users", 'key' => 'users_count', 'width' => 132, 'sortable' => true],
            ['title' => "fields.Description", 'key' => 'description', 'width' => 300, 'sortable' => false],
            ['title' => "Permissions", 'key' => 'permissions_name', 'width' => 620, 'sortable' => false],
        ]);

        ControllerHelper::translateTableHeadersTitle($headers);

        return $headers;
    }

    private function getFilterDependencies(): array
    {
        return [
            'roles' => Role::orderByName()->get(),
            'permissions' => Permission::orderByName()->get(),
            'departments' => Department::orderByName()->get(),
        ];
    }
}
