<?php

namespace App\Support\FilterDependencies\SimpleFilters\administration;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;

class PermissionsSimpleFilter
{
    public static function getAllDependencies()
    {
        return [
            'roles' => Role::orderByName()->get(),
            'permissions' => Permission::orderByName()->get(),
            'departments' => Department::orderByName()->get(),
        ];
    }
}

