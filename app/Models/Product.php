<?php

namespace App\Models;

use App\Http\Requests\ProductStoreRequest;
use App\Support\Abstracts\BaseModel;
use App\Support\Contracts\Model\CanExportRecordsAsExcel;
use App\Support\Contracts\Model\ExportsProductSelection;
use App\Support\Contracts\Model\HasTitle;
use App\Support\Helpers\GeneralHelper;
use App\Support\Helpers\QueryFilterHelper;
use App\Support\Traits\Model\Commentable;
use App\Support\Traits\Model\ExportsRecordsAsExcel;
use App\Support\Traits\Model\HasAttachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;

class Product extends BaseModel implements HasTitle, ExportsProductSelection
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    use SoftDeletes;
    use Commentable;
    use HasAttachments;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    const SETTINGS_MAD_TABLE_COLUMNS_KEY = 'MAD_IVP_table_columns';

    const DEFAULT_ORDER_BY = 'updated_at';
    const DEFAULT_ORDER_TYPE = 'desc';
    const DEFAULT_PAGINATION_LIMIT = 50;

    const LIMITED_EXCEL_RECORDS_COUNT_FOR_EXPORT = 80;
    const STORAGE_PATH_OF_EXCEL_TEMPLATE_FILE_FOR_EXPORT = 'app/excel/export-templates/ivp.xlsx';
    const STORAGE_PATH_FOR_EXPORTING_EXCEL_FILES = 'app/excel/exports/ivp';

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
    | Additional attributes
    |--------------------------------------------------------------------------
    */

    public function getProcessesIndexLinkAttribute()
    {
        return route('mad.processes.index', [
            'manufacturer_id[]' => $this->manufacturer_id,
            'inn_id[]' => $this->inn_id,
            'form_id[]' => $this->form_id,
            'dosage' => $this->dosage,
            'pack' => $this->pack,
        ]);
    }

    /**
     * Also used while exporting product selection.
     */
    public function getMatchedProductSearchesAttribute()
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
        static::deleting(function ($record) { // trashing
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

    public function scopeWithBasicRelations($query)
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

    public function scopeWithBasicRelationCounts($query)
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

    // Implement method defined in BaseModel abstract class
    public function generateBreadcrumbs($department = null): array
    {
        $breadcrumbs = [
            ['link' => route('mad.products.index'), 'text' => __('IVP')],
        ];

        if ($this->trashed()) {
            $breadcrumbs[] = ['link' => route('mad.products.trash'), 'text' => __('Trash')];
        }

        $breadcrumbs[] = ['link' => route('mad.products.edit', $this->id), 'text' => $this->title];

        return $breadcrumbs;
    }

    // Implement method declared in HasTitle Interface
    public function getTitleAttribute(): string
    {
        return GeneralHelper::truncateString($this->form->name, 12)
            . ' / '
            . GeneralHelper::truncateString($this->inn->name, 40);
    }

    // Implement method declared in CanExportRecordsAsExcel Interface
    public function scopeWithRelationsForExport($query)
    {
        return $query->withBasicRelations()
            ->withBasicRelationCounts()
            ->with(['comments']);
    }

    /**
     * Implement method declared in ExportsProductSelection Interface.
     */
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

    // Implement method declared in CanExportRecordsAsExcel Interface
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
            'whereIn' => ['id', 'inn_id', 'form_id', 'class_id', 'shelf_life_id', 'brand', 'manufacturer_id'],
            'like' => ['dosage', 'pack'],
            'belongsToMany' => ['zones'],
            'dateRange' => ['created_at', 'updated_at'],

            'relationEqual' => [
                [
                    'name' => 'manufacturer',
                    'attribute' => 'analyst_user_id',
                ],

                [
                    'name' => 'manufacturer',
                    'attribute' => 'bdm_user_id',
                ],
            ],

            'relationIn' => [
                [
                    'name' => 'manufacturer',
                    'attribute' => 'country_id',
                ]
            ],

            'relationEqualAmbiguous' => [
                [
                    'name' => 'manufacturer',
                    'attribute' => 'manufacturer_category_id',
                    'ambiguousAttribute' => 'manufacturers.category_id',
                ]
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Create & Update
    |--------------------------------------------------------------------------
    */

    public static function createFromRequest($request)
    {
        $record = self::create($request->all());

        // BelongsToMany relations
        $record->zones()->attach($request->input('zones'));

        // HasMany relations
        $record->storeCommentFromRequest($request);
        $record->storeAttachmentsFromRequest($request);
    }

    /**
     * Create multiple instances of the model from the request data.
     *
     * This method iterates over each products,
     * validates request for uniqueness,
     * and creates new instances on validation success.
     *
     * @param \Illuminate\Http\Request $request The request containing data.
     * @return void
     */
    public static function createMultipleRecordsFromRequest($request)
    {
        // Get 'atx_id' for each product
        $atxID = Atx::where([
            'inn_id' => $request->input('inn_id'),
            'form_id' => $request->input('form_id'),
        ])->first()->id;

        // Merge the 'atx_id' into the request
        $request->merge([
            'atx_id' => $atxID,
        ]);

        // Extract products from the request
        $products = $request->input('products', []);

        // Iterate over each products
        foreach ($products as $product) {
            // Merge the product attributes into the request
            $mergedRequest = $request->merge([
                'dosage' => $product['dosage'],
                'pack' => $product['pack'],
                'moq' => $product['moq'],
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
            $record->storeAttachmentsFromRequest($request);
        }
    }

    public function updateFromRequest($request)
    {
        $this->update($request->all());

        // Validate ATX
        $this->validateATX();

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
     * Validate product ATX.
     *
     * Used on products.update route.
     */
    public function validateATX()
    {
        $atx = Atx::where([
            'inn_id' => $this->inn_id,
            'form_id' => $this->form_id,
        ])->first();

        $this->update([
            'atx_id' => $atx ? $atx->id : null,
        ]);
    }

    // Used on filter
    public static function getAllUniqueBrands()
    {
        return self::whereNotNull('brand')->distinct()->pluck('brand');
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
        $similarRecords = Product::withTrashed()->where('manufacturer_id', $request->manufacturer_id)
            ->where('inn_id', $request->inn_id)
            ->whereIn('form_id', $formFamilyIDs)
            ->with(['form'])
            ->get();

        return $similarRecords;
    }

    /**
     * Provides the default MAD table columns along with their properties.
     *
     * These columns are typically used to display data in tables,
     * such as on index and trash pages, and are iterated over in a loop.
     *
     * @return array
     */
    public static function getDefaultMADTableSettingsForUser($user)
    {
        if (Gate::forUser($user)->denies('view-MAD-IVP')) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows('edit-MAD-IVP')) {
            array_push(
                $columns,
                ['name' => 'Edit', 'order' => $order++, 'width' => 40, 'visible' => 1],
            );
        }

        array_push(
            $columns,
            ['name' => 'Processes', 'order' => $order++, 'width' => 132, 'visible' => 1],
            ['name' => 'Category', 'order' => $order++, 'width' => 84, 'visible' => 1],
            ['name' => 'Country', 'order' => $order++, 'width' => 144, 'visible' => 1],
            ['name' => 'Manufacturer', 'order' => $order++, 'width' => 140, 'visible' => 1],
            ['name' => 'Generic', 'order' => $order++, 'width' => 180, 'visible' => 1],
            ['name' => 'Form', 'order' => $order++, 'width' => 130, 'visible' => 1],
            ['name' => 'Basic form', 'order' => $order++, 'width' => 130, 'visible' => 1],
            ['name' => 'Dosage', 'order' => $order++, 'width' => 120, 'visible' => 1],
            ['name' => 'Pack', 'order' => $order++, 'width' => 110, 'visible' => 1],
            ['name' => 'MOQ', 'order' => $order++, 'width' => 158, 'visible' => 1],
            ['name' => 'Shelf life', 'order' => $order++, 'width' => 130, 'visible' => 1],
            ['name' => 'Product class', 'order' => $order++, 'width' => 96, 'visible' => 1],
            ['name' => 'ATX', 'order' => $order++, 'width' => 190, 'visible' => 1],
            ['name' => 'Our ATX', 'order' => $order++, 'width' => 150, 'visible' => 1],
            ['name' => 'Dossier', 'order' => $order++, 'width' => 140, 'visible' => 1],
            ['name' => 'Zones', 'order' => $order++, 'width' => 54, 'visible' => 1],
            ['name' => 'Brand', 'order' => $order++, 'width' => 150, 'visible' => 1],
            ['name' => 'Bioequivalence', 'order' => $order++, 'width' => 120, 'visible' => 1],
            ['name' => 'Validity period', 'order' => $order++, 'width' => 132, 'visible' => 1],
            ['name' => 'Registered in EU', 'order' => $order++, 'width' => 138, 'visible' => 1],
            ['name' => 'Sold in EU', 'order' => $order++, 'width' => 134, 'visible' => 1],
            ['name' => 'Down payment', 'order' => $order++, 'width' => 120, 'visible' => 1],
            ['name' => 'Comments', 'order' => $order++, 'width' => 132, 'visible' => 1],
            ['name' => 'Last comment', 'order' => $order++, 'width' => 240, 'visible' => 1],
            ['name' => 'Comments date', 'order' => $order++, 'width' => 116, 'visible' => 1],
            ['name' => 'BDM', 'order' => $order++, 'width' => 142, 'visible' => 1],
            ['name' => 'Analyst', 'order' => $order++, 'width' => 142, 'visible' => 1],
            ['name' => 'Date of creation', 'order' => $order++, 'width' => 130, 'visible' => 1],
            ['name' => 'Update date', 'order' => $order++, 'width' => 150, 'visible' => 1],
            ['name' => 'Matched KVPP', 'order' => $order++, 'width' => 146, 'visible' => 1],
            ['name' => 'ID', 'order' => $order++, 'width' => 62, 'visible' => 1],
            ['name' => 'Attachments', 'order' => $order++, 'width' => 260, 'visible' => 1],
        );

        return $columns;
    }
}
