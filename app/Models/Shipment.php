<?php

namespace App\Models;

use App\Http\Requests\import\ImportProductStoreRequest;
use App\Http\Requests\import\ImportProductUpdateRequest;
use App\Support\Contracts\Model\HasTitleAttribute;
use App\Support\Helpers\QueryFilterHelper;
use App\Support\Traits\Model\AddsDefaultQueryParamsToRequest;
use App\Support\Traits\Model\HasComments;
use App\Support\Traits\Model\HasModelNamespaceAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class Shipment extends Model implements HasTitleAttribute
{
    use HasComments;
    use HasModelNamespaceAttributes;
    use AddsDefaultQueryParamsToRequest;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

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
            'confirmed',
            'completed',
            'arrived_at_warehouse',
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

    public function getArrivedAtWarehouseAttribute(): bool
    {
        return !is_null($this->arrived_at_warehouse);
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

        // Additional filters
        self::applyStatusFilter($query, $request);

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
    public function storeFromImportPageRequest(ImportProductStoreRequest $request): void
    {
        $record = self::create($request->all());

        // HasMany relations
        $record->storeCommentFromRequest($request);
    }

    /**
     * AJAX request from Import page
     *
     * Primarily done by ELD!
     */
    public function updateFromImportPageRequest(ImportProductUpdateRequest $request): void
    {
        $this->update($request->all());

        // HasMany relations
        $this->storeCommentFromRequest($request);
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
    | Table headers
    |--------------------------------------------------------------------------
    */

    public static function getImportTableHeadersForUser($user): ?array
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_IMPORT_PRODUCTS_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_IMPORT_PRODUCTS_NAME))) {
            array_push(
                $columns,
                ['title' => 'Record', 'key' => 'edit', 'width' => 60, 'sortable' => false, 'visible' => 1, 'order' => $order++],
            );
        }

        $additionalColumns = [
            ['title' => 'fields.Manufacturer', 'key' => 'order_manufacturer_id', 'width' => 140, 'sortable' => false],
            ['title' => 'fields.Country', 'key' => 'order_country_id', 'width' => 80, 'sortable' => false],
            ['title' => 'Order', 'key' => 'order_id', 'width' => 120, 'sortable' => true],
            ['title' => 'fields.TM Eng', 'key' => 'process_trademark_en', 'width' => 146, 'sortable' => false],
            ['title' => 'fields.TM Rus', 'key' => 'process_trademark_ru', 'width' => 146, 'sortable' => false],
            ['title' => 'fields.MAH', 'key' => 'process_marketing_authorization_holder_id', 'width' => 102, 'sortable' => true],
            ['title' => 'fields.Quantity', 'key' => 'quantity', 'width' => 112, 'sortable' => false],
            ['title' => 'fields.Price', 'key' => 'price', 'width' => 70, 'sortable' => false],
            ['title' => 'fields.Currency', 'key' => 'order_currency_id', 'width' => 84, 'sortable' => false],
            ['title' => 'fields.Total price', 'key' => 'total_price', 'width' => 132, 'sortable' => false],
            ['title' => 'Status', 'key' => 'status', 'width' => 142, 'sortable' => false],

            ['title' => 'Comments', 'key' => 'comments_count', 'width' => 132, 'sortable' => false],
            ['title' => 'comments.Last', 'key' => 'last_comment_body', 'width' => 200, 'sortable' => false],

            ['title' => 'fields.Serialization type', 'key' => 'serialization_type_id', 'width' => 156, 'sortable' => true],
            ['title' => 'fields.Production status', 'key' => 'production_status', 'width' => 160, 'sortable' => false],

            ['title' => 'dates.Layout approved', 'key' => 'layout_approved_date', 'width' => 170, 'sortable' => false],
            ['title' => 'dates.Prepayment completion', 'key' => 'production_prepayment_invoice_payment_completed_date', 'width' => 216, 'sortable' => false],
            ['title' => 'dates.Production end', 'key' => 'production_end_date', 'width' => 240, 'sortable' => true],
            ['title' => 'dates.Final payment request', 'key' => 'production_final_or_full_payment_invoice_sent_for_payment_date', 'width' => 236, 'sortable' => false], // Not 'payment_request_date_by_financier'
            ['title' => 'dates.Final payment completion', 'key' => 'production_final_or_full_payment_invoice_payment_completed_date', 'width' => 264, 'sortable' => false],
            ['title' => 'dates.Ready for shipment', 'key' => 'readiness_for_shipment_from_manufacturer_date', 'width' => 180, 'sortable' => true],
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
