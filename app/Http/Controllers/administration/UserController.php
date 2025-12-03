<?php

namespace App\Http\Controllers\administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\administration\UserStoreRequest;
use App\Http\Requests\administration\UserUpdateRequest;
use App\Models\Country;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Helpers\ControllerHelper;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class UserController extends Controller
{
    use DestroysModelRecords;

    // Required for DestroysModelRecords trait
    public static $model = User::class;

    public function index(Request $request)
    {
        return Inertia::render('administration/pages/users/Index', [
            // Refetched only on locale change
            'allTableHeaders' => fn() => $this->getAllTableHeadersTranslated(),

            // Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
        ]);
    }

    public function create()
    {
        // No lazy loads required, because AJAX request is used on store
        return Inertia::render('administration/pages/v/Index', [
            'permissions' => Permission::orderByName()->get(),
            'roles' => Role::orderByName()->get(),
            'departments' => Department::orderByName()->get(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
        ]);
    }

    /**
     * AJAX request
     */
    public function store(UserStoreRequest $request)
    {
        User::storeByAdminFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    public function edit(User $record)
    {
        $record->appendBasicAttributes();

        return Inertia::render('departments/MAD/pages/manufacturers/Edit', [
            // Refetched after record update
            'record' => $record,

            // Lazy loads. Never refetched again
            'permissions' => fn() => Permission::orderByName()->get(),
            'roles' => fn() => Role::orderByName()->get(),
            'departments' => fn() => Department::orderByName()->get(),
            'countriesOrderedByProcessesCount' => fn() => Country::orderByProcessesCount()->get(),
        ]);
    }

    /**
     * AJAX request
     */
    public function update(UserUpdateRequest $request, User $record)
    {
        $record->updateByAdminFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    private function getAllTableHeadersTranslated(): Collection
    {
        $headers = collect([
            ['title' => "fields.Photo", 'key' => 'photo', 'width' => 74, 'sortable' => false],
            ['title' => "fields.Name", 'key' => 'name', 'width' => 180, 'sortable' => true],
            ['title' => "fields.Email", 'key' => 'email', 'width' => 180, 'sortable' => true],
            ['title' => "Department", 'key' => 'department_id', 'width' => 132, 'sortable' => true],
            ['title' => "Roles", 'key' => 'roles_name', 'width' => 160, 'sortable' => false],
            ['title' => "Permissions", 'key' => 'permissions_name', 'width' => 280, 'sortable' => false],
            ['title' => "fields.Responsible", 'key' => 'responsible_country_names', 'width' => 132, 'sortable' => false],
            ['title' => "Records", 'key' => 'records_count', 'width' => 172, 'sortable' => false],
            ['title' => 'dates.Date of creation', 'key' => 'created_at', 'width' => 130, 'sortable' => true],
            ['title' => 'dates.Update date', 'key' => 'updated_at', 'width' => 150, 'sortable' => true],
        ]);

        ControllerHelper::translateTableHeadersTitle($headers);

        return $headers;
    }

    private function getFilterDependencies(): array
    {
        return [
            'users' => User::getAllMinified(),
            'permissions' => Permission::orderByName()->get(),
            'roles' => Role::orderByName()->get(),
            'departments' => Department::orderByName()->get(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
        ];
    }
}
