<?php

namespace App\Support\FilterDependencies\SimpleFilters\administration;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;

class RolesSimpleFilter
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
