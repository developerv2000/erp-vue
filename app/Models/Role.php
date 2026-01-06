<?php

namespace App\Models;

use App\Support\Helpers\ModelHelper;
use App\Support\Helpers\QueryFilterHelper;
use App\Support\Traits\Model\AddsDefaultQueryParamsToRequest;
use App\Support\Traits\Model\FindsRecordByName;
use App\Support\Traits\Model\ScopesOrderingByName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Role extends Model
{
    use FindsRecordByName;
    use ScopesOrderingByName;
    use AddsDefaultQueryParamsToRequest;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    // Querying
    const DEFAULT_ORDER_BY = 'name';
    const DEFAULT_ORDER_DIRECTION = 'asc';
    const DEFAULT_PER_PAGE = 50;

    // Notes: Checkout RoleSeeder for better guide.

    // Global roles
    const GLOBAL_ADMINISTRATOR_NAME = 'Global administrator';   // Full access. Doesn`t attach any role related permissions.
    const INACTIVE_NAME = 'Inactive';                           // No access, can`t login. Doesn`t attach any role related permissions.

    // MAD
    const MAD_ADMINISTRATOR_NAME = 'MAD administrator';         // Full access to 'MAD part'. Attaches role related permissions.
    const MAD_MODERATOR_NAME = 'MAD moderator';                 // Can view/create/edit/update/delete/export all 'MAD part' and comments. Attaches role related permissions.
    const MAD_GUEST_NAME = 'MAD guest';                         // Can only view 'MAD EPP/IVP/KVPP'. Can`t create/edit/update/delete/export. Attaches role related permissions.
    const MAD_INTERN_NAME = 'MAD intern';                       // Can view/edit only 'EPP' and 'IVP' of 'MAD part'. Attaches role related permissions.
    const MAD_ANALYST_NAME = 'MAD analyst';                     // User is assosiated as 'Analyst'. Doesn`t attach any role related permissions.

    // CMD
    const CMD_BDM_NAME = 'CMD BDM';                             // User is assosiated as 'BDM'. Not fully implemented yet!

    // PLPD
    const PLD_LOGISTICIAN_NAME = 'PLD logistician';           // Not fully implemented yet!

    // DD
    const DD_DESIGNER_NAME = 'DD Designer';                     // Not fully implemented yet!

    // PRD
    const PRD_FINANCIER_NAME = 'PRD Financier';                 // Not fully implemented yet!

    // MD
    const MD_SERIALIZER_NAME = 'MD Serializer';                 // Not fully implemented yet!

    // ELD
    const ELD_LOGISTICIAN_NAME = 'ELD Logistician';                 // Not fully implemented yet!

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWithBasicRelations($query)
    {
        return $query->with([
            'department',
            'permissions',
        ]);
    }

    public function scopeWithBasicRelationCounts($query)
    {
        return $query->withCount([
            'users',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtering
    |--------------------------------------------------------------------------
    */

    public static function filterQueryForRequest($query, $request): Builder
    {
        return QueryFilterHelper::applyFilters($query, $request, self::getFilterConfig());
    }

    private static function getFilterConfig(): array
    {
        return [
            'whereIn' => ['id', 'department_id'],
            'whereEqual' => ['global'],

            'belongsToManyRelation' => [
                [
                    'inputName' => 'permissions',
                    'relationName' => 'permissions',
                    'relationTable' => 'permissions',
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Queries
    |--------------------------------------------------------------------------
    */

    /**
     * Build and execute a model query based on request parameters.
     *
     * Steps:
     *  - Apply default relations & counts
     *  - Normalize query params (pagination, sorting, etc.)
     *  - Apply filters
     *  - Finalize query with sorting & pagination
     *
     * @param $action  ('paginate', 'get' or 'query')
     * @return mixed
     */
    public static function queryRecordsFromRequest(Request $request, string $action = 'paginate')
    {
        $query = self::withBasicRelations()->withBasicRelationCounts();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest($request);

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Finalize (sorting & pagination)
        $records = ModelHelper::finalizeQueryForRequest($query, $request, $action);

        return $records;
    }
}
