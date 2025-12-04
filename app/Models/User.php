<?php

namespace App\Models;

use App\Support\Helpers\FileHelper;
use App\Support\Helpers\ModelHelper;
use App\Support\Helpers\QueryFilterHelper;
use App\Support\Traits\Model\AddsDefaultQueryParamsToRequest;
use App\Support\Traits\Model\UploadsFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;
    use UploadsFile;
    use AddsDefaultQueryParamsToRequest;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    // Query
    const DEFAULT_ORDER_BY = 'name';
    const DEFAULT_ORDER_DIRECTION = 'asc';
    const DEFAULT_PER_PAGE = 50;

    // Photo
    const PHOTO_PATH = 'images/users';
    const PHOTO_WIDTH = 400;
    const PHOTO_HEIGHT = 400;
    const DELETED_USER_PHOTO = 'deleted-user.png';

    // Appearance settings
    const DEFAULT_THEME = 'light';
    const DEFAULT_LOCALE = 'ru';
    const DEFAULT_IS_LEFTBAR_COLLAPSED = false;

    // Setting keys of table headers
    // MAD
    const MAD_EPP_HEADERS_KEY = 'MAD_EPP';
    const MAD_IVP_HEADERS_KEY = 'MAD_IVP';
    const MAD_VPS_HEADERS_KEY = 'MAD_VPS';

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
        ];
    }

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'photo_url'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function responsibleCountries()
    {
        return $this->belongsToMany(Country::class, 'responsible_country_user');
    }

    public function manufacturersAsAnalyst()
    {
        return $this->hasMany(Manufacturer::class, 'analyst_user_id');
    }

    public function manufacturersAsBdm()
    {
        return $this->hasMany(Manufacturer::class, 'bdm_user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Additional attributes & appends
    |--------------------------------------------------------------------------
    */

    public static function appendRecordsBasicAttributes($records): void
    {
        foreach ($records as $record) {
            $record->appendBasicAttributes();
        }
    }

    // 'photo_url' is always appended automatically
    public function appendBasicAttributes(): void
    {
        $this->append([
            'usage_count',
        ]);
    }

    // Ensure that all related model counts are loaded.
    public function getUsageCountAttribute(): int
    {
        return $this->manufacturers_as_analyst_count
            + $this->manufacturers_as_bdm_count;
        // + $this->product_searches_count;
    }

    public function getPhotoUrlAttribute(): string
    {
        return url('storage/' . self::PHOTO_PATH . '/' . $this->photo);
    }

    public function getPhotoPathAttribute(): string
    {
        return storage_path('app/public/' . self::PHOTO_PATH . '/' . $this->photo);
    }

    /*
    |--------------------------------------------------------------------------
    | Relation loads
    |--------------------------------------------------------------------------
    */

    /**
     * Load basic relations for authenticated user.
     *
     * Used by EnsureUserRelationsAreLoaded middleware.
     */
    public function loadBasicAuthRelations(): void
    {
        $this->loadMissing([
            'roles' => function ($rolesQuery) {
                $rolesQuery->with('permissions');
            },
            'permissions',
            'responsibleCountries',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::deleting(function ($record) {
            // Load counts which are required for 'usage_count' attribute
            $record->loadCount(
                'manufacturersAsAnalyst',
                'manufacturersAsBdm',
                // 'productSearches'
            );

            // Throw error if user is in use
            if ($record->usage_count > 0) {
                throw ValidationException::withMessages([
                    'user_deletion' => trans('validation.custom.users.user_is_in_use', ['name' => $record->name]),
                ]);
            }

            // Delete related models but keep 'comments'
            $record->roles()->detach();
            $record->permissions()->detach();
            $record->responsibleCountries()->detach();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOnlyCMDBDMs($query): Builder
    {
        return $query->whereRelation('roles', 'name', Role::CMD_BDM_NAME);
    }

    public function scopeOnlyMADAnalysts($query): Builder
    {
        return $query->whereRelation('roles', 'name', Role::MAD_ANALYST_NAME);
    }

    public function scopeWithBasicRelations($query): Builder
    {
        return $query->with([
            'department',
            'roles',
            'permissions',
            'responsibleCountries',
        ]);
    }

    public function scopeWithBasicRelationCounts($query): Builder
    {
        return $query->withCount([
            'manufacturersAsAnalyst',
            'manufacturersAsBdm',
            // 'productSearches'
            'comments',
        ]);
    }

    /**
     * Load basic relations, while sending notifications.
     *
     * Roles with permissions must be loaded, because of using gates.
     */
    public function scopeWithBasicRelationsToNotify($query): Builder
    {
        return $query->with([
            'roles' => function ($rolesQuery) {
                $rolesQuery->with('permissions');
            },
            'permissions'
        ]);
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
     *  - Append basic attributes (if requested and unless returning raw query)
     *
     * @param $action  ('paginate', 'get' or 'query')
     * @return mixed
     */
    public static function queryRecordsFromRequest(Request $request, string $action = 'paginate', bool $appendAttributes = false)
    {
        $query = self::withBasicRelations()->withBasicRelationCounts();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest($request);

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Finalize (sorting & pagination)
        $records = ModelHelper::finalizeQueryForRequest($query, $request, $action);

        // Append attributes unless raw query is requested
        if ($appendAttributes && $action !== 'query') {
            self::appendRecordsBasicAttributes($records);
        }

        return $records;
    }

    public static function getCMDBDMsMinifed(): Collection
    {
        return self::onlyCMDBDMs()->select('id', 'name')->get();
    }

    public static function getMADAnalystsMinified(): Collection
    {
        return self::onlyMADAnalysts()->select('id', 'name')->get();
    }

    public static function getAllMinified(): Collection
    {
        return self::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */

    public function hasRole($role): bool
    {
        return $this->roles->contains('name', $role);
    }

    public function isInactive(): bool
    {
        return $this->hasRole(Role::INACTIVE_NAME);
    }

    public function isGlobalAdministrator(): bool
    {
        return $this->hasRole(Role::GLOBAL_ADMINISTRATOR_NAME);
    }

    public function isAnyAdministrator(): bool
    {
        return $this->isGlobalAdministrator()
            || $this->isMADAdministrator();
    }

    public function isMADAdministrator(): bool
    {
        return $this->hasRole(Role::MAD_ADMINISTRATOR_NAME);
    }

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    /**
     * Determine if the user has a given permission.
     *
     * This function checks if the user has the specified permission. User-specific permissions
     * (attached directly to the user) have a higher priority than role-based permissions.
     * If the user has been granted a permission directly, or via their role, they are considered
     * to have that permission unless explicitly denied. Denying permissions (e.g., 'CAN_NOT_*')
     * will override allowing permissions.
     *
     * @param string $permission The name of the permission to check.
     * @return bool True if the user has the permission, false otherwise.
     */
    public function hasPermission($permissionName)
    {
        // Check if there is an explicit "CAN_NOT_*" permission for the user first
        $deniedPermissionName = Permission::getDenyingPermission($permissionName);

        // If the user has the explicit "CAN_NOT_*" permission, deny access
        if ($this->permissions->contains('name', $deniedPermissionName)) {
            return false;
        }

        // Check user-specific permissions for explicit allow
        if ($this->permissions->contains('name', $permissionName)) {
            return true;
        }

        // Check for "CAN_NOT_*" permission in the user's roles
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $deniedPermissionName)) {
                return false; // If any role denies the permission, deny access
            }
        }

        // Check for explicit allow in the user's roles
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $permissionName)) {
                return true; // Allow if the permission is found in any role
            }
        }

        return false; // Default deny if no permission is found
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
            'whereEqual' => ['analyst_user_id', 'bdm_user_id', 'category_id', 'active', 'important'],
            'whereIn' => ['id', 'department_id'],
            'like' => ['email'],
            'dateRange' => ['created_at', 'updated_at'],

            'belongsToManyRelation' => [
                [
                    'inputName' => 'permissions',
                    'relationName' => 'permissions',
                    'relationTable' => 'permissions',
                ],

                [
                    'inputName' => 'roles',
                    'relationName' => 'roles',
                    'relationTable' => 'roles',
                ],

                [
                    'inputName' => 'responsible_countries',
                    'relationName' => 'responsibleCountries',
                    'relationTable' => 'countries',
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Settings: Main helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Update the specified setting for the user.
     */
    public function updateSetting($key, $value): void
    {
        $settings = $this->settings;
        $settings[$key] = $value;

        $this->settings = $settings;
        $this->save();
    }

    /**
     * Reset users appearance & table settings to default.
     * Used after creating & updating users by admin.
     *
     * Empty settings is used for Inactive users.
     */
    public function resetSettings(): void
    {
        // Refresh user because roles may have been updated
        $this->refresh();

        // Empty settings for Inactive users
        if ($this->isInactive()) {
            $this->settings = null;
            $this->save();
            return;
        }

        // Appearance
        $settings = [
            'theme' => User::DEFAULT_THEME,
            'locale' => User::DEFAULT_LOCALE,
            'is_leftbar_collapsed' => User::DEFAULT_IS_LEFTBAR_COLLAPSED,
            'table_headers' => [],
        ];

        $this->settings = $settings;
        $this->save();

        // Table headers
        $this->resetMADTableHeaders($settings);
    }

    /**
     * Used via artisan command line.
     */
    public static function resetSettingsOfAllUsers(): void
    {
        self::all()->each(function ($user) {
            $user->resetSettings();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Settings: Table headers helpers
    |--------------------------------------------------------------------------
    */

    public function resetTableHeadersByKey(string $key): void
    {
        $this->refresh();
        $settings = $this->settings;
        $headersSettings = $settings['table_headers'];

        $defaultHeaders = match ($key) {
            self::MAD_EPP_HEADERS_KEY => Manufacturer::getMADTableHeadersForUser($this),
            self::MAD_IVP_HEADERS_KEY => Product::getMADTableHeadersForUser($this),
            self::MAD_VPS_HEADERS_KEY => Process::getMADTableHeadersForUser($this),

            default => throw new InvalidArgumentException("Unknown key: $key"),
        };

        $headersSettings = array_merge($headersSettings ?? [], [$key => $defaultHeaders]);

        $settings['table_headers'] = $headersSettings;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Used via artisan command line.
     */
    public static function resetTableHeadersByKeyForAllUsers(string $key): void
    {
        self::all()->each(function ($user) use ($key) {
            $user->resetTableHeadersByKey($key);
        });
    }

    /**
     * Collect all table headers for a given key, from user settings
     * and translate their titles.
     */
    public function collectTranslatedTableHeadersByKey($key): Collection
    {
        $headers = collect($this->settings['table_headers'][$key])->sortBy('order');

        $headers->transform(function ($header) {
            $header['title'] = trans($header['title']);
            return $header;
        });

        return $headers;
    }

    /**
     * Filters out only the visible table headers from all headers collection
     *
     * @param  \Illuminate\Support\Collection  $columns
     * @return array
     */
    public static function filterOnlyVisibleTableHeaders($headers): array
    {
        return $headers->filter(fn($header) => $header['visible'] ?? false)
            ->sortBy('order')
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Settings: Department based table headers
    |--------------------------------------------------------------------------
    */

    public function resetMADTableHeaders($settings): void
    {
        $this->refresh();
        $settings = $this->settings;
        $headersSettings = $settings['table_headers'];

        $headersSettings[self::MAD_EPP_HEADERS_KEY] = Manufacturer::getMADTableHeadersForUser($this);
        $headersSettings[self::MAD_IVP_HEADERS_KEY] = Product::getMADTableHeadersForUser($this);
        $headersSettings[self::MAD_VPS_HEADERS_KEY] = Process::getMADTableHeadersForUser($this);

        $settings['table_headers'] = $headersSettings;
        $this->settings = $settings;
        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    /**
     * Update the user's profile personal data based on the request data.
     *
     * This method is used by users to update their own profile via the profile edit page.
     *
     * @param \Illuminate\Http\Request $request The request object containing the profile data.
     * @return void
     */
    public function updateProfilePersonalData($request): void
    {
        $validatedData = $request->validated();

        // Update all fields except 'photo'
        $this->update(collect($validatedData)->except('photo')->toArray());

        // Upload user's photo if provided and resize it
        if ($request->hasFile('photo')) {
            $this->uploadPhoto();
        }
    }

    /**
     * Update the user's password from the profile edit page.
     *
     * Important: Laravel automatically logouts user from other devices, while user is updating his own password.
     *
     * @param \Illuminate\Http\Request $request The request object containing the new password.
     * @return void
     */
    public function updateProfilePassword($request): void
    {
        $this->update([
            'password' => bcrypt($request->new_password),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store & Update by admin
    |--------------------------------------------------------------------------
    */

    public static function storeByAdminFromRequest($request): void
    {
        $record = self::create($request->validated());

        // BelongsToMany relations
        $record->roles()->attach($request->input('roles'));
        $record->permissions()->attach($request->input('permissions'));
        $record->responsibleCountries()->attach($request->input('responsible_countries'));

        // Reset all settings of the user
        $record->resetSettings();

        // Upload user's photo
        $record->uploadPhoto();
    }

    /**
     * Update by admin.
     *
     * Logouts updated user from all devices for security.
     */
    public function updateByAdminFromRequest($request): void
    {
        // Update the user's profile
        $this->update($request->validated());

        // BelongsToMany relations
        $this->roles()->sync($request->input('roles'));
        $this->permissions()->sync($request->input('permissions'));
        $this->responsibleCountries()->sync($request->input('responsible_countries'));

        // Reset settings
        $this->resetSettings();

        // Manually logout user from all devices
        if (Auth::user()->id != $this->id) {
            $this->logoutFromAllSessions();
        }

        // Upload user's photo if provided
        if ($request->hasFile('photo')) {
            $this->uploadPhoto();
        }
    }

    /**
     * Update users password by admin.
     *
     * Laravel automatically logouts user from all devices, while user is updating his own password.
     * Thats why manually logout user from all devices, if not own password is being updated.
     */
    public function updatePasswordByAdmin($request): void
    {
        // Update the user's password with the new hashed password
        $this->update([
            'password' => bcrypt($request->new_password),
        ]);

        // Manually logout from all devices
        if (Auth::user()->id != $this->id) {
            $this->logoutFromAllSessions();
        }
    }

    /**
     * Transfer records (like manufacturersAsAnalyst) except comments to another user by admin.
     */
    public function transferRecordsByAdmin($request): void
    {
        $toUserId = $request->input('to_user_id');

        // manufacturersAsAnalyst
        $this->manufacturersAsAnalyst()->update([
            'analyst_user_id' => $toUserId,
        ]);

        // manufacturersAsBdm
        $this->manufacturersAsBdm()->update([
            'bdm_user_id' => $toUserId,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    public static function getStoragePhotoFolderPath(): string
    {
        return storage_path('app/public/' . self::PHOTO_PATH);
    }

    public static function getImageUrlForDeletedUser(): string
    {
        return url('storage/' . self::PHOTO_PATH . '/' . self::DELETED_USER_PHOTO);
    }

    public static function notifyUsersBasedOnPermission($notification, $permission): void
    {
        self::withBasicRelationsToNotify()->each(function ($user) use ($notification, $permission) {
            if (Gate::forUser($user)->allows($permission)) {
                $user->notify($notification);
            }
        });
    }

    public function uploadPhoto(): void
    {
        $this->uploadFile('photo', self::getStoragePhotoFolderPath(), $this->name);
        FileHelper::resizeImage($this->photo_path, self::PHOTO_WIDTH, self::PHOTO_HEIGHT);
    }

    /**
     * Logout user manually from all devices, by clearing sessions and remember_token.
     *
     * Laravel automatically logouts user from other devices, while user is updating his own password.
     * Used only when updating user by admin!
     */
    private function logoutFromAllSessions(): void
    {
        // Delete all sessions for the current user
        DB::table('sessions')->where('user_id', $this->id)->delete();

        // Delete users remember_token.
        $this->refresh();
        $this->remember_token = null;
        $this->save();
    }

    /**
     * Detect users home page, based on permissions.
     */
    public function detectHomeRouteName(): string
    {
        $homepageRoutes = [
            // MAD
            'mad.manufacturers.index' => 'view-MAD-EPP',
            'mad.products.index' => 'view-MAD-IVP',
            'mad.processes.index' => 'view-MAD-VPS',
        ];

        foreach ($homepageRoutes as $routeName => $gate) {
            if (Gate::allows($gate)) {
                return route($routeName);
            }
        }

        // Default home if no pages are accessible
        return route('profile.edit');
    }
}
