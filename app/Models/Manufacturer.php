<?php

namespace App\Models;

use App\Support\Abstracts\BaseModel;
use App\Support\Contracts\Model\ExportsRecordsAsExcel;
use App\Support\Helpers\QueryFilterHelper;
use App\Support\Traits\Model\Commentable;
use App\Support\Traits\Model\GetsMinifiedRecordsWithName;
use App\Support\Traits\Model\HasAttachments;
use App\Support\Traits\Model\HasModelNamespace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class Manufacturer extends BaseModel implements ExportsRecordsAsExcel
{
    /** @use HasFactory<\Database\Factories\ManufacturerFactory> */
    use HasFactory;
    use SoftDeletes;
    use Commentable;
    use HasAttachments;
    use GetsMinifiedRecordsWithName;
    use HasModelNamespace;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    const DEFAULT_ORDER_BY = 'updated_at';
    const DEFAULT_ORDER_DIRECTION = 'desc';
    const DEFAULT_PER_PAGE = 50;

    const LIMITED_EXCEL_RECORDS_COUNT_FOR_EXPORT = 5;
    const STORAGE_PATH_OF_EXCEL_TEMPLATE_FILE_FOR_EXPORT = 'app/private/excel/export-templates/epp.xlsx';
    const STORAGE_PATH_OF_EXPORTED_EXCEL_FILES = 'app/private/excel/exports/epp';

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

    public function products()
    {
        return $this->hasMany(Product::class);
    }

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

    /*
    |--------------------------------------------------------------------------
    | Additional attributes & appends
    |--------------------------------------------------------------------------
    */

    /**
     * Used on manufacturers.edit form
     */
    public function getPresenceNamesArrayAttribute(): array
    {
        return $this->presences->pluck('name')->toArray();
    }

    public static function appendBasicAttributes($records)
    {
        foreach ($records as $record) {
            $record->append([
                'base_model_class',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function ($record) {
            $record->name = mb_strtoupper($record->name);
        });

        static::deleting(function ($record) { // trashing
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

    public function scopeWithBasicRelations($query)
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

    public function scopeWithBasicRelationCounts($query)
    {
        return $query->withCount([
            'comments',
            'attachments',
            'products',
            'meetings',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Contracts
    |--------------------------------------------------------------------------
    */

    // Implement method declared in Breadcrumbable Interface
    public function generateBreadcrumbs($department = null): array
    {
        return [];
    }

    // Implement method declared in CanExportRecordsAsExcel Interface
    public static function queryForExport(Request $request): Builder
    {
        $query = self::withBasicRelations()
            ->withBasicRelationCounts()
            ->with('comments');

        self::addDefaultQueryParamsToRequest($request);
        self::filterQueryForRequest($query, $request);

        return self::finalizeQueryForRequest($query, $request, 'query');
    }

    // Implement method declared in CanExportRecordsAsExcel Interface
    public function getExcelColumnValuesForExport(): array
    {
        return [
            $this->id,
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
     *  - Append basic attributes (unless returning raw query)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $action  ('paginate', 'get' or 'query')
     * @return mixed
     */
    public static function queryFromRequest(Request $request, string $action = 'paginate')
    {
        $query = self::withBasicRelations()->withBasicRelationCounts();

        // Apply trashed filter
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        }

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest($request);

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Finalize (sorting & pagination)
        $records = self::finalizeQueryForRequest($query, $request, $action);

        // Append attributes unless raw query is requested
        if ($action !== 'query') {
            self::appendBasicAttributes($records);
        }

        return $records;
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
            'belongsToMany' => ['productClasses', 'zones', 'blacklists'],
            'dateRange' => ['created_at', 'updated_at'],
        ];
    }

    /**
     * Apply filters to the query based on the specific manufacturer countries.
     *
     * @param Illuminate\Database\Eloquent\Builder $query The query builder instance to apply filters to.
     * @param Illuminate\Http\Request $request The HTTP request object containing filter parameters.
     * @return Illuminate\Database\Eloquent\Builder The modified query builder instance.
     */
    public static function applyRegionFilter($query, $request)
    {
        $region = $request->input('region');

        if ($region) {
            // Get the ID of the country 'INDIA' for comparison
            $indiaCountryId = Country::getIndiaCountryID();

            // Apply conditions based on the region
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
        }
    }

    /**
     * Apply filters to the query based on the country ID of related processes.
     *
     * This filter method returns records, which have related processes for selected countries.
     */
    public static function applyProcessCountriesFilter($query, $request)
    {
        $relationInAmbiguous = [
            [
                'name' => 'processes',
                'attribute' => 'process_country_id',
                'ambiguousAttribute' => 'processes.country_id',
            ]
        ];

        QueryFilterHelper::filterRelationInAmbiguous($request, $query, $relationInAmbiguous);
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
    | Misc
    |--------------------------------------------------------------------------
    */

    /**
     * Update self 'updated_at' field on comment store
     */
    public function updateSelfOnCommentCreate()
    {
        $this->updateQuietly(['updated_at' => now()]);
    }

    /**
     * Return an array of status options
     *
     * Used on records filter and on create/update as radiogroups options
     *
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            (object) ['caption' => trans('Active'), 'value' => 1],
            (object) ['caption' => trans('Stopped'), 'value' => 0],
        ];
    }

    public static function getDefaultMADTableHeadersForUser($user)
    {
        if (Gate::forUser($user)->denies('view-MAD-EPP')) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows('edit-MAD-EPP')) {
            array_push(
                $columns,
                ['title' => 'Edit', 'key' => 'edit', 'order' => $order++, 'width' => 56, 'visible' => 1, 'sortable' => false],
            );
        }

        array_push(
            $columns,
            ['title' => 'fields.BDM', 'key' => 'bdm_user_id', 'order' => $order++, 'width' => 146, 'visible' => 1, 'sortable' => true],
            ['title' => 'fields.Analyst', 'key' => 'analyst_user_id', 'order' => $order++, 'width' => 146, 'visible' => 1, 'sortable' => true],
            ['title' => 'fields.Country', 'key' => 'country_id', 'order' => $order++, 'width' => 144, 'visible' => 1, 'sortable' => true],
            ['title' => 'pages.IVP', 'key' => 'products_count', 'order' => $order++, 'width' => 104, 'visible' => 1, 'sortable' => true],
            ['title' => 'fields.Manufacturer', 'key' => 'name', 'order' => $order++, 'width' => 140, 'visible' => 1, 'sortable' => true],
            ['title' => 'fields.Category', 'key' => 'category_id', 'order' => $order++, 'width' => 104, 'visible' => 1, 'sortable' => true],
            ['title' => 'fields.Status', 'key' => 'active', 'order' => $order++, 'width' => 106, 'visible' => 1, 'sortable' => true],
            ['title' => 'properties.Important', 'key' => 'important', 'order' => $order++, 'width' => 100, 'visible' => 1, 'sortable' => true],
            ['title' => 'fields.Product class', 'key' => 'product_classes_name', 'order' => $order++, 'width' => 114, 'visible' => 1, 'sortable' => false],
            ['title' => 'fields.Zones', 'key' => 'zones_name', 'order' => $order++, 'width' => 54, 'visible' => 1, 'sortable' => false],
            ['title' => 'fields.Blacklist', 'key' => 'blacklists_name', 'order' => $order++, 'width' => 120, 'visible' => 1, 'sortable' => false],
            ['title' => 'fields.Presence', 'key' => 'presences_name', 'order' => $order++, 'width' => 128, 'visible' => 1, 'sortable' => false],
            ['title' => 'fields.Website', 'key' => 'website', 'order' => $order++, 'width' => 180, 'visible' => 1, 'sortable' => false],
            ['title' => 'fields.About company', 'key' => 'about', 'order' => $order++, 'width' => 240, 'visible' => 1, 'sortable' => false],
            ['title' => 'fields.Relationship', 'key' => 'relationship', 'order' => $order++, 'width' => 200, 'visible' => 1, 'sortable' => false],
            ['title' => 'Comments', 'key' => 'comments_count', 'order' => $order++, 'width' => 132, 'visible' => 1, 'sortable' => false],
            ['title' => 'comments.Last comment', 'key' => 'last_comment_body', 'order' => $order++, 'width' => 240, 'visible' => 1, 'sortable' => false],
            ['title' => 'comments.Comments date', 'key' => 'last_comment_created_at', 'order' => $order++, 'width' => 116, 'visible' => 1, 'sortable' => false],
            ['title' => 'dates.Date of creation', 'key' => 'created_at', 'order' => $order++, 'width' => 130, 'visible' => 1, 'sortable' => true],
            ['title' => 'dates.Update date', 'key' => 'updated_at', 'order' => $order++, 'width' => 150, 'visible' => 1, 'sortable' => true],
            ['title' => 'pages.Meetings', 'key' => 'meetings_count', 'order' => $order++, 'width' => 86, 'visible' => 1, 'sortable' => true],
            ['title' => 'ID', 'key' => 'id', 'order' => $order++, 'width' => 62, 'visible' => 1, 'sortable' => true],
            ['title' => 'Attachments', 'key' => 'attachments_count', 'order' => $order++, 'width' => 260, 'visible' => 1, 'sortable' => true],
        );

        return $columns;
    }
}
