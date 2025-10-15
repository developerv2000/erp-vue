<?php

namespace App\Models;

use App\Support\Helpers\FileHelper;
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
    use HasFactory;
    use Notifiable;
    use UploadsFile;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

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
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOnlyCMDBDMs($query)
    {
        return $query->whereRelation('roles', 'name', Role::CMD_BDM_NAME);
    }

    public function scopeOnlyMADAnalysts($query)
    {
        return $query->whereRelation('roles', 'name', Role::MAD_ANALYST_NAME);
    }

    /**
     * Load basic relations, while sending notifications.
     *
     * Roles with permissions must be loaded, because of using gates.
     */
    public function scopeWithBasicRelationsToNotify($query)
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

    public static function getCMDBDMsMinifed()
    {
        return self::onlyCMDBDMs()->select('id', 'name')->get();
    }

    public static function getMADAnalystsMinified()
    {
        return self::onlyMADAnalysts()->select('id', 'name')->get();
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

    public function isAnyAdministrator()
    {
        return $this->isGlobalAdministrator()
            || $this->isMADAdministrator();
    }

    public function isMADAdministrator()
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
    public static function resetSettingsOfAllUsers()
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

    public function resetMADTableHeaders($settings)
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
    | Misc
    |--------------------------------------------------------------------------
    */

    public static function getImageUrlForDeletedUser(): string
    {
        return url('storage/' . self::PHOTO_PATH . '/' . self::DELETED_USER_PHOTO);
    }

    public static function notifyUsersBasedOnPermission($notification, $permission)
    {
        self::withBasicRelationsToNotify()->each(function ($user) use ($notification, $permission) {
            if (Gate::forUser($user)->allows($permission)) {
                $user->notify($notification);
            }
        });
    }

    public function uploadPhoto(): void
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
            'mad.products.index' => 'view-IVP-EPP',
            'mad.processes.index' => 'view-VPS-EPP',
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
