<?php

namespace App\Models;

use App\Http\Requests\MAD\ManufacturerStoreRequest;
use App\Http\Requests\MAD\ManufacturerUpdateRequest;
use App\Support\Contracts\Model\ExportsRecordsAsExcel;
use App\Support\Contracts\Model\GeneratesBreadcrumbs;
use App\Support\Contracts\Model\HasTitleAttribute;
use App\Support\Helpers\ModelHelper;
use App\Support\Helpers\QueryFilterHelper;
use App\Support\Traits\Model\AddsDefaultQueryParamsToRequest;
use App\Support\Traits\Model\GetsMinifiedRecordsWithName;
use App\Support\Traits\Model\HasAttachments;
use App\Support\Traits\Model\HasComments;
use App\Support\Traits\Model\HasModelNamespaceAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class Manufacturer extends Model implements HasTitleAttribute, GeneratesBreadcrumbs, ExportsRecordsAsExcel
{
    /** @use HasFactory<\Database\Factories\ManufacturerFactory> */
    use HasFactory;
    use SoftDeletes;
    use HasComments;
    use HasAttachments;
    use HasModelNamespaceAttributes;
    use AddsDefaultQueryParamsToRequest;
    use GetsMinifiedRecordsWithName;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    const DEFAULT_ORDER_BY = 'updated_at';
    const DEFAULT_ORDER_DIRECTION = 'desc';
    const DEFAULT_PER_PAGE = 50;

    const LIMITED_RECORDS_COUNT_ON_EXPORT_TO_EXCEL = 5;
    const STORAGE_PATH_OF_EXCEL_TEMPLATE_FILE_FOR_EXPORT = 'app/private/excel/export-templates/epp.xlsx';
    const STORAGE_PATH_FOR_EXPORTING_EXCEL_FILES = 'app/private/excel/exports/epp';

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(ManufacturerCategory::class);
    }

    public function blacklists()
    {
        return $this->belongsToMany(ManufacturerBlacklist::class);
    }

    public function presences()
    {
        return $this->hasMany(ManufacturerPresence::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function productClasses()
    {
        return $this->belongsToMany(ProductClass::class);
    }

    public function zones()
    {
        return $this->belongsToMany(Zone::class);
    }

    public function analyst()
    {
        return $this->belongsTo(User::class, 'analyst_user_id');
    }

    public function bdm()
    {
        return $this->belongsTo(User::class, 'bdm_user_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Slow relation, use only when necessary.
    // On eager loading use $manufacturer->products->processes || with('products.processes')
    // On filtering use whereHas('processes', ...)
    public function processes()
    {
        return $this->hasManyThrough(
            Process::class,
            Product::class,
            'manufacturer_id', // Foreign key on Products table
            'product_id',   // Foreign key on Processes table
            'id',         // Local key on Manufacturers table
            'id'          // Local key on Products table
        );
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
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

    public function appendBasicAttributes(): void
    {
        $this->append([
            'base_model_class',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    /**
     * Process controls (deleting/restoring etc) are binded with Product model events,
     * so we don't need to bind them here.
     */
    protected static function booted(): void
    {
        static::saving(function ($record) {
            $record->name = mb_strtoupper($record->name);
        });

        // Trashing
        static::deleting(function ($record) {
            foreach ($record->products as $product) {
                $product->delete();
            }

            foreach ($record->meetings as $meeting) {
                $meeting->delete();
            }
        });

        static::restored(function ($record) {
            foreach ($record->products()->onlyTrashed()->get() as $product) {
                $product->restore();
            }

            foreach ($record->meetings()->onlyTrashed()->get() as $meeting) {
                $meeting->restore();
            }
        });

        static::forceDeleting(function ($record) {
            $record->zones()->detach();
            $record->productClasses()->detach();
            $record->blacklists()->detach();

            foreach ($record->presences as $presence) {
                $presence->delete();
            }

            foreach ($record->products()->withTrashed()->get() as $product) {
                $product->forceDelete();
            }

            foreach ($record->meetings()->withTrashed()->get() as $meeting) {
                $meeting->forceDelete();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWithBasicRelations($query): Builder
    {
        return $query->with([
            'country',
            'category',
            'presences',
            'blacklists',
            'productClasses',
            'zones',
            'attachments',
            'lastComment',

            'analyst:id,name,photo',
            'bdm:id,name,photo',
        ]);
    }

    public function scopeWithBasicRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
            'attachments',
            'products',
            'meetings',
        ]);
    }

    public function scopeOnlyRecordsWithProcessesReadyForOrder($query)
    {
        return $query->whereHas('processes', function ($processesQuery) {
            $processesQuery->whereNotNull('readiness_for_order_date');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Contracts
    |--------------------------------------------------------------------------
    */

    // Implement method declared in HasTitleAttribute Interface
    public function getTitleAttribute(): string
    {
        return $this->name;
    }

    // Implement method declared in GeneratesBreadcrumbs Interface
    public function generateBreadcrumbs($department = null): array
    {
        $lowercasedDepartment = strtolower($department);

        // Index page
        $breadcrumbs = [
            ['title' => __('pages.EPP'), 'link' => route($lowercasedDepartment . '.manufacturers.index')],
        ];

        // Trash page
        if ($this->trashed()) {
            $breadcrumbs[] = ['title' => __('pages.Trash'), 'link' => route($lowercasedDepartment . '.manufacturers.trash')];
        }

        // Edit page
        $breadcrumbs[] = ['title' => $this->title, 'link' => route($lowercasedDepartment . '.manufacturers.edit', $this->id)];

        return $breadcrumbs;
    }

    // Implement method declared in ExportsRecordsAsExcel Interface
    public static function queryRecordsForExportFromRequest(Request $request): Builder
    {
        $query = self::withBasicRelations()
            ->withBasicRelationCounts()
            ->with('comments'); // Important

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest($request);

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Finalize (sorting)
        ModelHelper::finalizeQueryForRequest($query, $request, 'query');

        return $query;
    }

    // Implement method declared in ExportsRecordsAsExcel Interface
    public function getExcelColumnValuesForExport(): array
    {
        return [
            $this->id,
            $this->bdm->name,
            $this->analyst->name,
            $this->country->name,
            $this->products_count,
            $this->name,
            $this->category->name,
            $this->active ? __('Active') : __('Stoped'),
            $this->important ? __('Important') : '',
            $this->productClasses->pluck('name')->implode(' '),
            $this->zones->pluck('name')->implode(' '),
            $this->blacklists->pluck('name')->implode(' '),
            $this->presences->pluck('name')->implode(' '),
            $this->website,
            $this->about,
            $this->relationship,
            $this->comments->pluck('plain_text')->implode(' / '),
            $this->lastComment?->created_at,
            $this->created_at,
            $this->updated_at,
            $this->meetings_count,
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
     *  - Apply soft delete scope (if requested)
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
        $query = self::withBasicRelations()
            ->withBasicRelationCounts();

        // Apply trashed filter
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        }

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

    /*
    |--------------------------------------------------------------------------
    | Filtering
    |--------------------------------------------------------------------------
    */

    public static function filterQueryForRequest($query, $request): Builder
    {
        // Apply base filters using helper
        $query = QueryFilterHelper::applyFilters($query, $request, self::getFilterConfig());

        // Additional filters
        self::applyRegionFilter($query, $request);
        self::applyProcessCountriesFilter($query, $request);
        self::applyHasActiveProcessesForMonthFilter($query, $request);

        return $query;
    }

    private static function getFilterConfig(): array
    {
        return [
            'whereEqual' => ['analyst_user_id', 'bdm_user_id', 'category_id', 'active', 'important'],
            'whereIn' => ['id', 'country_id'],
            'dateRange' => ['created_at', 'updated_at'],

            'belongsToManyRelation' => [
                [
                    'inputName' => 'product_classes',
                    'relationName' => 'productClasses',
                    'relationTable' => 'product_classes',
                ],

                [
                    'inputName' => 'zones',
                    'relationName' => 'zones',
                    'relationTable' => 'zones',
                ],

                [
                    'inputName' => 'blacklists',
                    'relationName' => 'blacklists',
                    'relationTable' => 'manufacturer_blacklists',
                ],
            ],
        ];
    }

    /**
     * Apply filters to the query based on 'region' or 'manufacturer_region'.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyRegionFilter($query, $request)
    {
        $region = $request->input('region') ?? $request->input('manufacturer_region');

        if (!$region) {
            return $query; // nothing to filter
        }

        $indiaCountryId = Country::getIndiaCountryID();

        switch ($region) {
            case 'Europe':
                // Exclude manufacturers from India
                $query->where('country_id', '!=', $indiaCountryId);
                break;

            case 'India':
                // Include only manufacturers from India
                $query->where('country_id', $indiaCountryId);
                break;
        }

        return $query;
    }


    /**
     * Apply filters to the query based on the country ID of related processes.
     *
     * This filter method returns records, which have related processes for selected countries.
     */
    public static function applyProcessCountriesFilter($query, $request): void
    {
        $relationIn = [
            [
                'inputName' => 'process_country_id',
                'relationName' => 'processes',
                'relationAttribute' => 'processes.country_id',
            ]
        ];

        QueryFilterHelper::filterRelationIn($request, $query, $relationIn);
    }

    /**
     * Filter only manufacturers which have active processes fot specific month.
     *
     * This function filters the query to include only records that have
     * associated processes with status history starting in the specified month and year
     * and have stage <= 5.
     */
    public static function applyHasActiveProcessesForMonthFilter($query, $request)
    {
        if ($request->filled('has_active_processes_for_specific_month')) {
            return $query->whereHas('processes.statusHistory', function ($historyQuery) use ($request) {
                $historyQuery
                    ->whereYear('start_date', $request->input('has_active_processes_for_year'))
                    ->whereMonth('start_date', $request->input('has_active_processes_for_month'))
                    ->whereHas('status.generalStatus', function ($statusQuery) {
                        $statusQuery->where('stage', '<=', 5);
                    });
            });
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Store & update
    |--------------------------------------------------------------------------
    */

    /**
     * AJAX request
     */
    public static function storeByMADFromRequest(ManufacturerStoreRequest $request): void
    {
        $record = self::create($request->all());

        // BelongsToMany relations
        $record->zones()->attach($request->input('zones'));
        $record->productClasses()->attach($request->input('productClasses'));
        $record->blacklists()->attach($request->input('blacklists'));

        // HasMany relations
        $record->storePresencesOnCreate($request->input('presences'));
        $record->storeCommentFromRequest($request);
        $record->storeAttachmentsFromRequest($request);
    }

    private function storePresencesOnCreate($presences): void
    {
        if (!$presences) return;

        foreach ($presences as $name) {
            $this->presences()->create(['name' => $name]);
        }
    }

    /**
     * AJAX request
     */
    public function updateByMADFromRequest(ManufacturerUpdateRequest $request): void
    {
        $this->update($request->all());

        // BelongsToMany relations
        $this->zones()->sync($request->input('zones'));
        $this->productClasses()->sync($request->input('productClasses'));
        $this->blacklists()->sync($request->input('blacklists'));

        // HasMany relations
        $this->syncPresencesOnEdit($request);
        $this->storeCommentFromRequest($request);
        $this->storeAttachmentsFromRequest($request);
    }

    private function syncPresencesOnEdit($request): void
    {
        $presences = $request->input('presences');

        // Remove existing presences if $presences is empty
        if (!$presences) {
            $this->presences()->delete();
            return;
        }

        // Add new presences
        foreach ($presences as $name) {
            if (!$this->presences->contains('name', $name)) {
                $this->presences()->create(['name' => $name]);
            }
        }

        // Delete removed presences
        $this->presences()->whereNotIn('name', $presences)->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Order part
    |--------------------------------------------------------------------------
    */

    public static function getMinifiedRecordsWithProcessesReadyForOrder()
    {
        return self::onlyRecordsWithProcessesReadyForOrder()
            ->minifiedRecordsWithName()
            ->get();
    }

    public function getReadyForOrderProcessesOfCountry($countryId, $appendFullEnglishProductLabelWithId = false): Collection
    {
        $processes = $this->processes()
            ->onlyReadyForOrder()
            ->withRelationsForOrder()
            ->withOnlySelectsForOrder()
            ->where('country_id', $countryId)
            ->get();

        if ($appendFullEnglishProductLabelWithId) {
            foreach ($processes as $process) {
                $process->append('full_english_product_label_with_id');
            }
        }

        return $processes;
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    /**
     * Update self 'updated_at' attribute on comment store
     */
    public function updateSelfOnCommentCreate(): void
    {
        $this->updateQuietly(['updated_at' => now()]);
    }

    public static function getMADTableHeadersForUser($user): array|null
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_MAD_EPP_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_MAD_EPP_NAME))) {
            array_push(
                $columns,
                ['title' => 'Record', 'key' => 'edit', 'width' => 60, 'sortable' => false, 'visible' => 1, 'order' => $order++],
            );
        }

        $additionalColumns = [
            ['title' => 'fields.BDM', 'key' => 'bdm_user_id', 'width' => 146, 'sortable' => true],
            ['title' => 'fields.Analyst', 'key' => 'analyst_user_id', 'width' => 146, 'sortable' => true],
            ['title' => 'fields.Country', 'key' => 'country_id', 'width' => 144, 'sortable' => true],
            ['title' => 'pages.IVP', 'key' => 'products_count', 'width' => 104, 'sortable' => true],
            ['title' => 'fields.Manufacturer', 'key' => 'name', 'width' => 140, 'sortable' => true],
            ['title' => 'fields.Category', 'key' => 'category_id', 'width' => 104, 'sortable' => true],
            ['title' => 'fields.Status', 'key' => 'active', 'width' => 106, 'sortable' => true],
            ['title' => 'properties.Important', 'key' => 'important', 'width' => 100, 'sortable' => true],
            ['title' => 'fields.Product class', 'key' => 'product_classes_name', 'width' => 114, 'sortable' => false],
            ['title' => 'fields.Zones', 'key' => 'zones_name', 'width' => 54, 'sortable' => false],
            ['title' => 'fields.Blacklist', 'key' => 'blacklists_name', 'width' => 120, 'sortable' => false],
            ['title' => 'fields.Presence', 'key' => 'presences_name', 'width' => 128, 'sortable' => false],
            ['title' => 'fields.Website', 'key' => 'website', 'width' => 180, 'sortable' => false],
            ['title' => 'fields.About company', 'key' => 'about', 'width' => 240, 'sortable' => false],
            ['title' => 'fields.Relationship', 'key' => 'relationship', 'width' => 200, 'sortable' => false],
            ['title' => 'Comments', 'key' => 'comments_count', 'width' => 132, 'sortable' => false],
            ['title' => 'comments.Last', 'key' => 'last_comment_body', 'width' => 240, 'sortable' => false],
            ['title' => 'comments.Date', 'key' => 'last_comment_created_at', 'width' => 116, 'sortable' => false],
            ['title' => 'dates.Date of creation', 'key' => 'created_at', 'width' => 130, 'sortable' => true],
            ['title' => 'dates.Update date', 'key' => 'updated_at', 'width' => 150, 'sortable' => true],
            ['title' => 'pages.Meetings', 'key' => 'meetings_count', 'width' => 86, 'sortable' => true],
            ['title' => 'ID', 'key' => 'id', 'width' => 62, 'sortable' => true],
            ['title' => 'Attachments', 'key' => 'attachments_count', 'width' => 340, 'sortable' => true],
        ];

        foreach ($additionalColumns as $column) {
            array_push($columns, [
                ...$column,
                'visible' => 1,
                'order' => $order++,
            ]);
        }

        return $columns;
    }
}
