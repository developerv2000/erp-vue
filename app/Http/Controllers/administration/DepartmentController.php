<?php

namespace App\Http\Controllers\administration;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Support\Helpers\ControllerHelper;
use App\Support\Helpers\ModelHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $getDepartments = function () use ($request) {
            $query = Department::withBasicRelations()->withBasicRelationCounts();
            Department::addDefaultQueryParamsToRequest($request);

            return ModelHelper::finalizeQueryForRequest($query, $request, 'get');
        };

        return Inertia::render('administration/pages/departments/Index', [
            // Lazy loads. Refetched only on locale change
            'allTableHeaders' => fn() => $this->getAllTableHeadersTranslated(),

            // Lazy loads. Never refetched again
            'records' => fn() => $getDepartments(),
        ]);
    }

    private function getAllTableHeadersTranslated(): Collection
    {
        $headers = collect([
            ['title' => "fields.Name", 'key' => 'name', 'width' => 220, 'sortable' => true,],
            ['title' => "fields.Abbreviation", 'key' => 'abbreviation', 'width' => 132, 'sortable' => true,],
            ['title' => "Roles", 'key' => 'roles_name', 'width' => 160, 'sortable' => false,],
            ['title' => "Users", 'key' => 'users_count', 'width' => 132, 'sortable' => true,],
            ['title' => "Permissions", 'key' => 'permissons_name', 'width' => 940, 'sortable' => false,],
        ]);

        ControllerHelper::translateTableHeadersTitle($headers);

        return $headers;
    }
}
