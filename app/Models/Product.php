<?php

namespace App\Models;

use App\Http\Requests\MAD\ProductStoreRequest;
use App\Http\Requests\MAD\ProductUpdateRequest;
use App\Support\Contracts\Model\ExportsProductSelection;
use App\Support\Contracts\Model\ExportsRecordsAsExcel;
use App\Support\Contracts\Model\GeneratesBreadcrumbs;
use App\Support\Contracts\Model\HasTitleAttribute;
use App\Support\Helpers\GeneralHelper;
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

class Product extends Model implements HasTitleAttribute, GeneratesBreadcrumbs, ExportsRecordsAsExcel, ExportsProductSelection
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
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

    const LIMITED_RECORDS_COUNT_ON_EXPORT_TO_EXCEL = 160;
    const STORAGE_PATH_OF_EXCEL_TEMPLATE_FILE_FOR_EXPORT = 'app/private/excel/export-templates/ivp.xlsx';
    const STORAGE_PATH_FOR_EXPORTING_EXCEL_FILES = 'app/private/excel/exports/ivp';

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

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class)->withTrashed();
    }

    public function processes()
    {
        return $this->hasMany(Process::class);
    }

    public function inn()
    {
        return $this->belongsTo(Inn::class);
    }

    public function form()
    {
        return $this->belongsTo(ProductForm::class, 'form_id');
    }

    public function atx()
    {
        return $this->belongsTo(Atx::class);
    }

    public function shelfLife()
    {
        return $this->belongsTo(ProductShelfLife::class);
    }

    public function class()
    {
        return $this->belongsTo(ProductClass::class);
    }

    public function zones()
    {
        return $this->belongsToMany(Zone::class);
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
            'index_link_of_related_processes',
            'matched_product_searches',
        ]);

        $this->form->append('parent_name');
    }

    // Used in products.index/trash pages table
    public function getIndexLinkOfRelatedProcessesAttribute(): string
    {
        return route('mad.processes.index', [
            'manufacturer_id[]' => $this->manufacturer_id,
            'product_inn_id[]' => $this->inn_id,
            'product_form_id[]' => $this->form_id,
            'product_dosage' => $this->dosage,
            'product_pack' => $this->pack,
            'initialize_from_inertia_page' => true,
        ]);
    }

    // Used in products.index/trash pages table & product-selection export
    public function getMatchedProductSearchesAttribute(): Collection
    {
        return ProductSearch::where([
            'inn_id' => $this->inn_id,
            'form_id' => $this->form_id,
            'dosage' => $this->dosage,
            'pack' => $this->pack,
        ])
            ->select('id', 'country_id', 'status_id')
            ->withOnly('country')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        // trashing
        static::deleting(function ($record) {
            foreach ($record->processes as $process) {
                $process->delete();
            }
        });

        static::restoring(function ($record) {
            if ($record->manufacturer->trashed()) {
                $record->manufacturer->restore();
            }
        });

        static::restored(function ($record) {
            foreach ($record->processes()->onlyTrashed()->get() as $process) {
                $process->restore();
            }
        });

        static::forceDeleting(function ($record) {
            $record->zones()->detach();

            foreach ($record->processes()->withTrashed()->get() as $process) {
                $process->forceDelete();
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
            'inn',
            'atx',
            'shelfLife',
            'class',
            'zones',
            'attachments',
            'lastComment',

            'form' => function ($formsQuery) {
                $formsQuery->with(['parent']);
            },

            'manufacturer' => function ($manufacturersQuery) {
                $manufacturersQuery->select([
                    'id',
                    'name',
                    'country_id',
                    'category_id',
                    'analyst_user_id',
                    'bdm_user_id'
                ])

                    ->withOnly([
                        'country',
                        'category',

                        'analyst:id,name,photo',
                        'bdm:id,name,photo',
                    ]);
            },
        ]);
    }

    public function scopeWithBasicRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
            'attachments',
            'processes',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Contracts
    |--------------------------------------------------------------------------
    */

    // Implement method declared in HasTitleAttribute Interface
    public function getTitleAttribute(): string
    {
        return GeneralHelper::truncateString($this->form->name, 12)
            . ' / '
            . GeneralHelper::truncateString($this->inn->name, 40);
    }

    // Implement method declared in GeneratesBreadcrumbs Interface
    public function generateBreadcrumbs($department = null): array
    {
        $lowercasedDepartment = strtolower($department);

        // Index page
        $breadcrumbs = [
            ['title' => __('pages.EPP'), 'link' => route($lowercasedDepartment . '.products.index')],
        ];

        // Trash page
        if ($this->trashed()) {
            $breadcrumbs[] = ['title' => __('pages.Trash'), 'link' => route($lowercasedDepartment . '.products.trash')];
        }

        // Edit page
        $breadcrumbs[] = ['title' => $this->title, 'link' => route($lowercasedDepartment . '.products.edit', $this->id)];

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
            $this->processes_count,
            $this->manufacturer->category->name,
            $this->manufacturer->country->name,
            $this->manufacturer->name,
            $this->inn->name,
            $this->form->name,
            $this->form->parent_name,
            $this->dosage,
            $this->pack,
            $this->moq,
            $this->shelfLife?->name,
            $this->class->name,
            $this->atx?->name,
            $this->atx?->short_name,
            $this->dossier,
            $this->zones->pluck('name')->implode(' '),
            $this->brand,
            $this->bioequivalence,
            $this->validity_period,
            $this->registered_in_eu ? __('Registered') : '',
            $this->sold_in_eu ? __('Sold') : '',
            $this->down_payment,
            $this->comments->pluck('plain_text')->implode(' / '),
            $this->lastComment?->created_at,
            $this->manufacturer->bdm->name,
            $this->manufacturer->analyst->name,
            $this->created_at,
            $this->updated_at,
            $this->matched_product_searches->count(),
        ];
    }

    //  Implement method declared in ExportsProductSelection Interface.
    public function scopeWithRelationsForProductSelection($query)
    {
        // Select only required fields
        return $query
            ->with([
                'inn',
                'form',
                'shelfLife',
            ])
            ->select([
                'products.id',
                'inn_id',
                'form_id',
                'shelf_life_id',
                'dosage',
                'pack',
                'moq',
            ]);
    }

    // Implement method declared in ExportsProductSelection Interface
    public static function queryRecordsForProductSelection(Request $request): Builder
    {
        $query = self::withRelationsForProductSelection();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest($request);

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Finalize (sorting)
        ModelHelper::finalizeQueryForRequest($query, $request, 'query');

        return $query;
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

        return $query;
    }

    private static function getFilterConfig(): array
    {
        return [
            'whereIn' => ['id', 'inn_id', 'form_id', 'class_id', 'shelf_life_id', 'brand', 'manufacturer_id'],
            'like' => ['dosage', 'pack'],
            'dateRange' => ['created_at', 'updated_at'],

            'belongsToManyRelation' => [
                [
                    'inputName' => 'zones',
                    'relationName' => 'zones',
                    'relationTable' => 'zones',
                ],
            ],

            'relationEqual' => [
                [
                    'inputName' => 'manufacturer_analyst_user_id',
                    'relationName' => 'manufacturer',
                    'relationAttribute' => 'manufacturers.analyst_user_id',
                ],

                [
                    'inputName' => 'manufacturer_bdm_user_id',
                    'relationName' => 'manufacturer',
                    'relationAttribute' => 'manufacturers.bdm_user_id',
                ],

                [
                    'inputName' => 'manufacturer_category_id',
                    'relationName' => 'manufacturer',
                    'relationAttribute' => 'manufacturers.category_id',
                ]
            ],

            'relationIn' => [
                [
                    'inputName' => 'manufacturer_country_id',
                    'relationName' => 'manufacturer',
                    'relationAttribute' => 'manufacturers.country_id',
                ]
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Store & update
    |--------------------------------------------------------------------------
    */

    /**
     * Create multiple instances of the model from the request data.
     *
     * AJAX request.
     *
     * This method iterates over each products,
     * validates request for uniqueness,
     * and creates new instances on validation success.
     */
    public static function storeMultipleRecordsByMADFromRequest(Request $request, Atx $atx): void
    {
        // Merge the 'atx_id' into the request
        $request->merge([
            'atx_id' => $atx->id,
        ]);

        // Extract products from the request
        $products = $request->input('products', []);

        // Iterate over each products
        foreach ($products as $product) {
            // Merge the product attributes into the request
            $mergedRequest = $request->merge([
                'dosage' => $product['dosage'],
                'pack' => isset($product['pack']) ? $product['pack'] : null,
                'moq' => isset($product['moq']) ? $product['moq'] : null,
            ]);

            // Create a ProductStoreRequest instance from the merged request
            $formRequest = ProductStoreRequest::createFrom($mergedRequest);

            // Create a validator instance
            $validator = app('validator')->make(
                $formRequest->all(),
                $formRequest->rules(),
                $formRequest->messages()
            );

            // Perform validation
            $validator->validate();

            // Create an instance using the merged request data
            $record = self::create($mergedRequest->all());

            // BelongsToMany relations
            $record->zones()->attach($request->input('zones'));

            // HasMany relations
            $record->storeCommentFromRequest($request);

            // Upload attachments if there is only one product
            if (count($products) === 1) {
                $record->storeAttachmentsFromRequest($request);
            }
        }
    }

    /**
     * AJAX request
     */
    public function updateByMADFromRequest(ProductUpdateRequest $request, $atx): void
    {
        // Merge the 'atx_id' into the request
        $request->merge([
            'atx_id' => $atx->id,
        ]);

        // Update record
        $this->update($request->all());

        // BelongsToMany relations
        $this->zones()->sync($request->input('zones'));

        // HasMany relations
        $this->storeCommentFromRequest($request);
        $this->storeAttachmentsFromRequest($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    /**
     * Used on products.edit page
     */
    public function ensureAtxExists()
    {
        if (!$this->atx) {
            $atx = Atx::create([
                'inn_id' => $this->inn_id,
                'form_id' => $this->form_id,
                'name' => '-',
                'short_name' => null,
            ]);

            $this->update([
                'atx_id' => $atx->id
            ]);

            $this->load('atx');
        }
    }

    // Used on filters
    public static function getAllUniqueBrands()
    {
        return self::whereNotNull('brand')->distinct()->pluck('brand');
    }

    /**
     * Update self attributes on related process 'create' or 'edit'
     */
    public function updateOnRelatedProcessCreateOrEdit($request)
    {
        // Global attributes
        $this->form_id = $request->input('product_form_id');
        $this->dosage = $request->input('product_dosage');
        $this->pack = $request->input('product_pack');
        $this->shelf_life_id = $request->input('product_shelf_life_id');
        $this->class_id = $request->input('product_class_id');
        $this->moq = $request->input('product_moq');

        // Save only if any field is updated
        if ($this->isDirty()) {
            $this->save();
        }
    }

    /**
     * Get similar records based on the provided request data.
     *
     * Used for AJAX requests to retrieve similar records, on the products create form.
     *
     * @param  \Illuminate\Http\Request  $request The request object containing form data.
     * @return \Illuminate\Database\Eloquent\Collection A collection of similar records.
     */
    public static function getSimilarRecordsForRequest($request)
    {
        // Get the family IDs of the selected form
        $formFamilyIDs = ProductForm::find($request->form_id)->getFamilyIDs();

        // Query similar records based on manufacturer, inn, and form family IDs
        $similarRecords = self::withTrashed()
            ->where('manufacturer_id', $request->manufacturer_id)
            ->where('inn_id', $request->inn_id)
            ->whereIn('form_id', $formFamilyIDs)
            ->with(['form'])
            ->get();

        return $similarRecords;
    }

    public static function getMADTableHeadersForUser($user): array|null
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_MAD_IVP_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_MAD_IVP_NAME))) {
            array_push(
                $columns,
                ['title' => 'Record', 'key' => 'edit', 'width' => 60, 'sortable' => false, 'visible' => 1, 'order' => $order++],
            );
        }

        $additionalColumns = [
            ['title' => 'Processes', 'key' => 'processes_count', 'width' => 136, 'sortable' => true],
            ['title' => 'fields.Category', 'key' => 'manufacturer_category_name', 'width' => 84, 'sortable' => false],
            ['title' => 'fields.Country', 'key' => 'manufacturer_country_name', 'width' => 144, 'sortable' => false],
            ['title' => 'fields.Manufacturer', 'key' => 'manufacturer_id', 'width' => 140, 'sortable' => true],
            ['title' => 'fields.Generic', 'key' => 'inn_id', 'width' => 180, 'sortable' => true],
            ['title' => 'fields.Form', 'key' => 'form_id', 'width' => 130, 'sortable' => true],
            ['title' => 'fields.Basic form', 'key' => 'form_parent_name', 'width' => 130, 'sortable' => false],
            ['title' => 'fields.Dosage', 'key' => 'dosage', 'width' => 120, 'sortable' => true],
            ['title' => 'fields.Pack', 'key' => 'pack', 'width' => 110, 'sortable' => false],
            ['title' => 'fields.MOQ', 'key' => 'moq', 'width' => 158, 'sortable' => true],
            ['title' => 'fields.Shelf life', 'key' => 'shelf_life_id', 'width' => 130, 'sortable' => true],
            ['title' => 'fields.Product class', 'key' => 'class_id', 'width' => 96, 'sortable' => true],
            ['title' => 'fields.ATX', 'key' => 'atx_id', 'width' => 150, 'sortable' => true],
            ['title' => 'fields.Our ATX', 'key' => 'atx_short_name', 'width' => 150, 'sortable' => false],
            ['title' => 'fields.Dossier', 'key' => 'dossier', 'width' => 140, 'sortable' => false],
            ['title' => 'fields.Zones', 'key' => 'zones_name', 'width' => 54, 'sortable' => false],
            ['title' => 'fields.Brand', 'key' => 'brand', 'width' => 150, 'sortable' => true],
            ['title' => 'fields.Bioequivalence', 'key' => 'bioequivalence', 'width' => 120, 'sortable' => true],
            ['title' => 'fields.Validity period', 'key' => 'validity_period', 'width' => 132, 'sortable' => true],
            ['title' => 'fields.Registered in EU', 'key' => 'registered_in_eu', 'width' => 138, 'sortable' => true],
            ['title' => 'fields.Sold in EU', 'key' => 'sold_in_eu', 'width' => 134, 'sortable' => true],
            ['title' => 'fields.Down payment', 'key' => 'down_payment', 'width' => 120, 'sortable' => false],
            ['title' => 'Comments', 'key' => 'comments_count', 'width' => 132, 'sortable' => false],
            ['title' => 'comments.Last', 'key' => 'last_comment_body', 'width' => 240, 'sortable' => false],
            ['title' => 'comments.Date', 'key' => 'last_comment_created_at', 'width' => 116, 'sortable' => false],
            ['title' => 'fields.BDM', 'key' => 'manufacturer_bdm', 'width' => 146, 'sortable' => false],
            ['title' => 'fields.Analyst', 'key' => 'manufacturer_analyst', 'width' => 146, 'sortable' => false],
            ['title' => 'dates.Date of creation', 'key' => 'created_at', 'width' => 130, 'sortable' => true],
            ['title' => 'dates.Update date', 'key' => 'updated_at', 'width' => 150, 'sortable' => true],
            ['title' => 'fields.Matched KVPP', 'key' => 'matched_product_searches', 'width' => 146, 'sortable' => false],
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
