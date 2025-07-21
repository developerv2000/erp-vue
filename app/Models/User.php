<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Support\Helpers\FileHelper;
use App\Support\Traits\Model\AddsDefaultQueryParamsToRequest;
use App\Support\Traits\Model\FinalizesQueryForRequest;
use App\Support\Traits\Model\UploadsFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use UploadsFile;
    use AddsDefaultQueryParamsToRequest;
    use FinalizesQueryForRequest;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    const DEFAULT_ORDER_BY = 'name';
    const DEFAULT_ORDER_TYPE = 'asc';
    const DEFAULT_PAGINATION_LIMIT = 50;

    const DEFAULT_THEME = 'light';
    const DEFAULT_LOCALE = 'ru';
    const DEFAULT_IS_LEFTBAR_COLLAPSED = false;

    const PHOTO_PATH = 'images/users';
    const PHOTO_WIDTH = 400;
    const PHOTO_HEIGHT = 400;

    // Table setting keys
    const MAD_EPP_TABLE_SETTINGS_KEY = 'MAD_EPP';
    const MAD_IVP_TABLE_SETTINGS_KEY = 'MAD_IVP';
    const MAD_VPS_TABLE_SETTINGS_KEY = 'MAD_VPS';
    const MAD_KVPP_TABLE_SETTINGS_KEY = 'MAD_KVPP';
    const MAD_MEETINGS_TABLE_SETTINGS_KEY = 'MAD_Meetings';
    const MAD_DH_TABLE_SETTINGS_KEY = 'MAD_DH';

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

    public function productSearches()
    {
        return $this->hasMany(ProductSearch::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Additional attributes
    |--------------------------------------------------------------------------
    */

    public function getPhotoUrlAttribute(): string
    {
        return url('storage/' . self::PHOTO_PATH . '/' . $this->photo);
    }

    public function getPhotoPathAttribute()
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
    public function loadBasicAuthRelations()
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
    | Queries
    |--------------------------------------------------------------------------
    */

    public static function getCMDBDMsMinifed()
    {
        return self::onlyCMDBDMs()->select('id', 'name')->get();
    }

    public static function getMADAnalystsMinified()
    {
        return self::onlyMADAnalysts()->select('id', 'name')->get();
    }

    public static function getAllMinified()
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

    public function hasRole($role)
    {
        return $this->roles->contains('name', $role);
    }

    public function isInactive()
    {
        return $this->hasRole(Role::INACTIVE_NAME);
    }

    public function isGlobalAdministrator()
    {
        return $this->hasRole(Role::GLOBAL_ADMINISTRATOR_NAME);
    }

    public function isMADAdministrator()
    {
        return $this->hasRole(Role::MAD_ADMINISTRATOR_NAME);
    }

    public function isAnyAdministrator()
    {
        return $this->isGlobalAdministrator()
            || $this->isMADAdministrator();
    }

    public function isMADAnalyst()
    {
        return $this->hasRole(Role::MAD_ANALYST_NAME);
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
    | Settings
    |--------------------------------------------------------------------------
    */

    /**
     * Update the specified setting for the user.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
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
    public function resetAllSettingsToDefault(): void
    {
        // Refresh user because roles may have been updated
        $this->refresh();

        // Empty settings for Inactive users
        if ($this->isInactive()) {
            $this->settings = null;
            $this->save();
            return;
        }

        // Appearance settings
        $settings = [
            'theme' => User::DEFAULT_THEME,
            'locale' => User::DEFAULT_LOCALE,
            'is_leftbar_collapsed' => User::DEFAULT_IS_LEFTBAR_COLLAPSED,
            'tables' => [],
        ];

        $this->settings = $settings;
        $this->save();

        // Table settings
        $this->resetMADTableSettings($settings);
    }

    /**
     * Reset users MAD table settings.
     */
    public function resetMADTableSettings($settings)
    {
        $this->refresh();
        $settings = $this->settings;
        $tableSettings = $settings['tables'];

        $tableSettings[self::MAD_EPP_TABLE_SETTINGS_KEY] = Manufacturer::getDefaultMADTableSettingsForUser($this);
        $tableSettings[self::MAD_IVP_TABLE_SETTINGS_KEY] = Product::getDefaultMADTableSettingsForUser($this);
        $tableSettings[self::MAD_VPS_TABLE_SETTINGS_KEY] = Process::getDefaultMADTableSettingsForUser($this);
        $tableSettings[self::MAD_KVPP_TABLE_SETTINGS_KEY] = ProductSearch::getDefaultMADTableSettingsForUser($this);
        $tableSettings[self::MAD_MEETINGS_TABLE_SETTINGS_KEY] = Meeting::getDefaultMADTableSettingsForUser($this);
        $tableSettings[self::MAD_DH_TABLE_SETTINGS_KEY] = Process::getDefaultMADDHTableSettingsForUser($this);

        $settings['tables'] = $tableSettings;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Reset all settings to default for all users.
     *
     * Used via artisan command line.
     */
    public static function resetAllSettingsToDefaultForAll()
    {
        self::all()->each(function ($user) {
            $user->resetAllSettingsToDefault();
        });
    }

    /**
     * Reset only specific table settings.
     */
    public function resetSpecificTableSettings(string $key): void
    {
        $this->refresh();
        $settings = $this->settings;
        $tableSettings = $settings['tables'];

        $defaultSettings = match ($key) {
            self::MAD_EPP_TABLE_SETTINGS_KEY => Manufacturer::getDefaultMADTableSettingsForUser($this),
            self::MAD_IVP_TABLE_SETTINGS_KEY => Product::getDefaultMADTableSettingsForUser($this),
            self::MAD_VPS_TABLE_SETTINGS_KEY => Process::getDefaultMADTableSettingsForUser($this),
            self::MAD_KVPP_TABLE_SETTINGS_KEY => ProductSearch::getDefaultMADTableSettingsForUser($this),
            self::MAD_MEETINGS_TABLE_SETTINGS_KEY => Meeting::getDefaultMADTableSettingsForUser($this),
            self::MAD_DH_TABLE_SETTINGS_KEY => Process::getDefaultMADDHTableSettingsForUser($this),

            default => throw new InvalidArgumentException("Unknown key: $key"),
        };

        $tableSettings = array_merge($tableSettings ?? [], [$key => $defaultSettings]);

        $settings['tables'] = $tableSettings;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Reset only specific table settings for all users.
     *
     * Can be used via artisan command line.
     */
    public function resetSpecificTableSettingsForAll(string $key): void
    {
        self::all()->each(function ($user) use ($key) {
            $user->resetSpecificTableSettings($key);
        });
    }

    /**
     * Collects all table columns for a given key from user settings.
     *
     * @param  string  $key
     * @return \Illuminate\Support\Collection
     */
    public function collectTableColumnsBySettingsKey($key): Collection
    {
        return collect($this->settings['tables'][$key])->sortBy('order');
    }

    /**
     * Filters out only the visible columns from the provided collection.
     *
     * @param  \Illuminate\Support\Collection  $columns
     * @return array
     */
    public static function filterOnlyVisibleColumns($columns): array
    {
        return $columns->filter(fn($column) => $column['visible'] ?? false)
            ->sortBy('order')
            ->values()
            ->all();
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
    | Filtering
    |--------------------------------------------------------------------------
    */

    public static function filterQueryForRequest($query, $request)
    {
        // Apply base filters using helper
        $query = QueryFilterHelper::applyFilters($query, $request, self::getFilterConfig());

        return $query;
    }

    private static function getFilterConfig(): array
    {
        return [
            'whereIn' => ['id', 'department_id'],
            'like' => ['email'],
            'dateRange' => ['created_at', 'updated_at'],
            'belongsToMany' => ['permissions', 'roles', 'responsibleCountries'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Create & Update
    |--------------------------------------------------------------------------
    */

    /**
     * Create new user by admin.
     */
    public static function createFromRequest($request)
    {
        $record = self::create($request->validated());

        // Attach belongsToMany associations
        $record->roles()->attach($request->input('roles'));
        $record->permissions()->attach($request->input('permissions'));
        $record->responsibleCountries()->attach($request->input('responsibleCountries'));

        // Load all settings for the user
        $record->resetAllSettingsToDefault();

        // Upload user's photo
        $record->uploadPhoto();
    }

    /**
     * Update user by admin.
     *
     * Logouts user from all devices.
     */
    public function updateFromRequest($request)
    {
        // Update the user's profile
        $this->update($request->validated());

        // BelongsToMany relations
        $this->roles()->sync($request->input('roles'));
        $this->permissions()->sync($request->input('permissions'));
        $this->responsibleCountries()->sync($request->input('responsibleCountries'));

        // Reset settings
        $this->resetAllSettingsToDefault();

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
     * Laravel automatically logouts user from other devices, while user is updating his own password.
     * Thats why manually logout user from all devices, if not own password is being updated.
     */
    public function updatePassword($request): void
    {
        // Update the user's password with the new hashed password
        $this->update([
            'password' => bcrypt($request->password),
        ]);

        // Manually logout from all devices
        if (Auth::user()->id != $this->id) {
            $this->logoutFromAllSessions();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    public static function notifyUsersBasedOnPermission($notification, $permission)
    {
        self::withBasicRelationsToNotify()->each(function ($user) use ($notification, $permission) {
            if (Gate::forUser($user)->allows($permission)) {
                $user->notify($notification);
            }
        });
    }

    /**
     * Logout user manually from all devices, by clearing sessions and remember_token.
     *
     * Laravel automatically logouts user from other devices, while user is updating his own password.
     * Used only while updating users by admin!
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
     * Upload users photo.
     */
    public function uploadPhoto()
    {
        $this->uploadFile('photo', storage_path('app/public/' . self::PHOTO_PATH), $this->name);
        FileHelper::resizeImage($this->photo_path, self::PHOTO_WIDTH, self::PHOTO_HEIGHT);
    }

    /**
     * Detect users home page, based on permissions.
     */
    public function detectHomeRouteName()
    {
        $homepageRoutes = [
            // MAD
            'mad.manufacturers.index' => 'view-MAD-EPP',
            'mad.product-searches.index' => 'view-MAD-KVPP',
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
