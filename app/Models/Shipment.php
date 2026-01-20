<?php

namespace App\Models;

use App\Http\Requests\import\ImportProductStoreRequest;
use App\Http\Requests\import\ImportProductUpdateRequest;
use App\Http\Requests\ImportShipmentStoreRequest;
use App\Http\Requests\ImportShipmentUpdateRequest;
use App\Support\Contracts\Model\HasTitleAttribute;
use App\Support\Helpers\ModelHelper;
use App\Support\Helpers\QueryFilterHelper;
use App\Support\Traits\Model\AddsDefaultQueryParamsToRequest;
use App\Support\Traits\Model\HasComments;
use App\Support\Traits\Model\HasModelNamespaceAttributes;
use App\Support\Traits\Model\UploadsFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class Shipment extends Model implements HasTitleAttribute
{
    use HasComments;
    use HasModelNamespaceAttributes;
    use AddsDefaultQueryParamsToRequest;
    use UploadsFile;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    // Storage files
    const STORAGE_FILES_PATH = 'app/private/shipments';
    const PACKING_LIST_FILE_FOLDER_NAME = 'packing-lists';

    // Import
    const DEFAULT_IMPORT_ORDER_BY = 'id';
    const DEFAULT_IMPORT_ORDER_DIRECTION = 'asc';
    const DEFAULT_IMPORT_PER_PAGE = 50;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    protected $guarded = ['id'];

    protected $casts = [
        'transportation_requested_at' => 'date',
        'rate_approved_at' => 'date',
        'confirmed_at' => 'date',
        'completed_at' => 'datetime',
        'arrived_at_warehouse' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class)->withTrashed();
    }

    public function products()
    {
        return $this->hasMany(OrderProduct::class, 'shipment_from_manufacturer_id');
    }

    public function transportationMethod()
    {
        return $this->belongsTo(TransportationMethod::class);
    }

    public function destination()
    {
        return $this->belongsTo(ShipmentDestination::class, 'destination_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Additional attributes & appends
    |--------------------------------------------------------------------------
    */

    public static function appendRecordsBasicImportAttributes($records): void
    {
        foreach ($records as $record) {
            $record->appendBasicImportAttributes();
        }
    }

    public function appendBasicImportAttributes(): void
    {
        $this->append([
            'base_model_class',
            'packing_list_file_url',
            'confirmed',
            'completed',
            'has_arrived_at_warehouse',
        ]);

        $this->loadMissing('products.process');

        foreach ($this->products as $product) {
            $product->process->append([
                'full_english_product_label',
                'full_russian_product_label',
            ]);
        }
    }

    public function getConfirmedAttribute(): bool
    {
        return !is_null($this->confirmed_at);
    }

    public function getCompletedAttribute(): bool
    {
        return !is_null($this->completed_at);
    }

    // Avoid duplicate 'arrived_at_warehouse' attribute
    public function getHasArrivedAtWarehouseAttribute(): bool
    {
        return !is_null($this->arrived_at_warehouse);
    }

    public function getPackingListFileUrlAttribute(): string
    {
        return route('shipments.files', [
            'path' => self::PACKING_LIST_FILE_FOLDER_NAME . '/' . $this->packing_list_file,
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
            foreach ($record->products as $product) {
                $product->shipment_from_manufacturer_id = null;
                $product->produced_by_manufacturer_quantity = null;
                $product->save();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWithBasicImportRelations($query): Builder
    {
        return $query->with([
            'lastComment',
            'transportationMethod',
            'destination',
            'currency',

            'manufacturer' => function ($manufacturersQuery) {
                $manufacturersQuery->select(
                    'manufacturers.id',
                    'manufacturers.name',
                    'manufacturers.country_id',
                )
                    ->with([
                        'country',
                    ]);
            },

            'products.process' => function ($processQuery) {
                $processQuery->withRelationsForOrderProduct()
                    ->withOnlySelectsForOrderProduct();
            },
        ]);
    }

    public function scopeWithBasicImportRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
            'products',
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
        return '#' . $this->id . ' — ' . $this->manufacturer->name;
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
    public static function queryImportRecordsFromRequest(Request $request, string $action = 'paginate', bool $appendAttributes = false)
    {
        $query = self::withBasicImportRelations()
            ->withBasicImportRelationCounts();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest(
            $request,
            'DEFAULT_IMPORT_ORDER_BY',
            'DEFAULT_IMPORT_ORDER_DIRECTION',
            'DEFAULT_IMPORT_PER_PAGE'
        );

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Finalize (sorting & pagination)
        $records = ModelHelper::finalizeQueryForRequest($query, $request, $action);

        // Append attributes unless raw query is requested
        if ($appendAttributes && $action !== 'query') {
            self::appendRecordsBasicImportAttributes($records);
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
            'whereIn' => ['id', 'manufacturer_id', 'transportation_method_id', 'destination_id'],
            'dateRange' => [
                'confirmed_at',
                'completed_at',
                'arrived_at_warehouse',
                'created_at',
                'updated_at',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Store & update
    |--------------------------------------------------------------------------
    */

    /**
     * AJAX request from Import page
     *
     * Primarily done by ELD!
     */
    public static function storeFromImportPageRequest(ImportShipmentStoreRequest $request): void
    {
        DB::transaction(function () use ($request) {
            // Create shipment
            $shipment = self::create($request->all());

            // Add products to shipment
            $selectedProducts = collect($request->array('products', []))
                ->where('checked', true);

            $databaseProducts = OrderProduct::whereIn('id', $selectedProducts->pluck('id'))->get();

            foreach ($selectedProducts as $selected) {
                $product = $databaseProducts->where('id', $selected['id'])->first();
                $product->shipment_from_manufacturer_id = $shipment->id;
                $product->produced_by_manufacturer_quantity = $selected['produced_by_manufacturer_quantity'];
                $product->save();
            }

            // HasMany relations
            $shipment->storeCommentFromRequest($request);

            // Upload files
            $shipment->uploadFile('packing_list_file', self::getPackingListFileFolderPath());
        });
    }

    /**
     * AJAX request from Import page
     *
     * Primarily done by ELD!
     */
    public function updateFromImportPageRequest(ImportShipmentUpdateRequest $request): void
    {
        DB::transaction(function () use ($request) {
            // Update shipment
            $this->update($request->except([ // exclude nullable files
                'packing_list_file',
            ]));

            // Update products in shipment
            foreach ($request->array('products', []) as $productData) {
                $product = $this->products()->findOrFail($productData['id']);
                $product->produced_by_manufacturer_quantity = $productData['produced_by_manufacturer_quantity'];
                $product->save();
            }

            // HasMany relations
            $this->storeCommentFromRequest($request);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    /**
     * AJAX request
     *
     * Primarily done by ELD!
     */
    public function complete(): void
    {
        if (!$this->completed) {
            $this->completed_at = now();
            $this->save();
        }
    }

    /**
     * AJAX request
     *
     * Primarily done by ELD!
     */
    public function arriveAtWarehouse(): void
    {
        if (!$this->arrived_at_warehouse) {
            $this->arrived_at_warehouse = now();
            $this->save();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Storage paths
    |--------------------------------------------------------------------------
    */

    public static function getPackingListFileFolderPath(): string
    {
        return storage_path(self::STORAGE_FILES_PATH . '/' . self::PACKING_LIST_FILE_FOLDER_NAME);
    }

    /*
    |--------------------------------------------------------------------------
    | Table headers
    |--------------------------------------------------------------------------
    */

    public static function getImportTableHeadersForUser($user): ?array
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_IMPORT_SHIPMENTS_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_IMPORT_SHIPMENTS_NAME))) {
            array_push(
                $columns,
                ['title' => 'Record', 'key' => 'edit', 'width' => 60, 'sortable' => false, 'visible' => 1, 'order' => $order++],
            );
        }

        $additionalColumns = [
            ['title' => 'ID', 'key' => 'id', 'width' => 62, 'sortable' => true],
            ['title' => 'fields.Manufacturer', 'key' => 'manufacturer_id', 'width' => 140, 'sortable' => true],
            ['title' => 'fields.Packing list', 'key' => 'packing_list_file', 'width' => 152, 'sortable' => false],
            ['title' => 'Products', 'key' => 'products_count', 'width' => 100, 'sortable' => false],
            ['title' => 'fields.Transportation method', 'key' => 'transportation_method_id', 'width' => 144, 'sortable' => true],
            ['title' => 'fields.Destination', 'key' => 'destination_id', 'width' => 160, 'sortable' => true],
            ['title' => 'fields.Pallets', 'key' => 'pallets_quantity', 'width' => 80, 'sortable' => false],
            ['title' => 'fields.Volume', 'key' => 'volume', 'width' => 72, 'sortable' => false],
            ['title' => 'dates.Transportation request', 'key' => 'transportation_requested_at', 'width' => 244, 'sortable' => true],
            ['title' => 'fields.Forwarder', 'key' => 'forwarder', 'width' => 116, 'sortable' => false],
            ['title' => 'fields.Price', 'key' => 'price', 'width' => 70, 'sortable' => false],
            ['title' => 'fields.Currency', 'key' => 'currency_id', 'width' => 84, 'sortable' => true],
            ['title' => 'dates.Rate approved', 'key' => 'rate_approved_at', 'width' => 184, 'sortable' => true],
            ['title' => 'dates.Confirmed', 'key' => 'confirmed_at', 'width' => 172, 'sortable' => true],
            ['title' => 'dates.Completed', 'key' => 'completed_at', 'width' => 156, 'sortable' => true],
            ['title' => 'dates.Arrived at warehouse', 'key' => 'arrived_at_warehouse', 'width' => 188, 'sortable' => true],

            ['title' => 'Comments', 'key' => 'comments_count', 'width' => 132, 'sortable' => false],
            ['title' => 'comments.Last', 'key' => 'last_comment_body', 'width' => 200, 'sortable' => false],

            ['title' => 'dates.Date of creation', 'key' => 'created_at', 'width' => 130, 'sortable' => true],
            ['title' => 'dates.Update date', 'key' => 'updated_at', 'width' => 150, 'sortable' => true],
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
