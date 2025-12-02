<?php

namespace App\Http\Controllers\administration;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Support\Helpers\ControllerHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('administration/pages/departments/Index', [
            // Lazy loads. Refetched only on locale change
            'allTableHeaders' => fn() => $this->getAllTableHeadersTranslated(),

            // Lazy loads. Never refetched again
            'records' => fn() => Department::queryRecordsFromRequest($request, 'get'),
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
