<?php

namespace App\Models;

use App\Http\Requests\CMD\CMDOrderUpdateRequest;
use App\Http\Requests\PLD\PLDOrderStoreRequest;
use App\Http\Requests\PLD\PLDOrderUpdateRequest;
use App\Support\Contracts\Model\HasTitleAttribute;
use App\Support\Helpers\ModelHelper;
use App\Support\Helpers\QueryFilterHelper;
use App\Support\Traits\Model\AddsDefaultQueryParamsToRequest;
use App\Support\Traits\Model\GetsMinifiedRecordsWithName;
use App\Support\Traits\Model\HasComments;
use App\Support\Traits\Model\HasModelNamespaceAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class Order extends Model implements HasTitleAttribute
{
    use HasComments;
    use HasModelNamespaceAttributes;
    use AddsDefaultQueryParamsToRequest;
    use GetsMinifiedRecordsWithName;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    // PLD
    const DEFAULT_PLD_ORDER_BY = 'id';
    // const DEFAULT_PLD_ORDER_BY = 'updated_at';
    const DEFAULT_PLD_ORDER_DIRECTION = 'asc';
    const DEFAULT_PLD_PER_PAGE = 50;

    // CMD
    const DEFAULT_CMD_ORDER_BY = 'id';
    // const DEFAULT_CMD_ORDER_BY = 'sent_to_bdm_date';
    const DEFAULT_CMD_ORDER_DIRECTION = 'asc';
    const DEFAULT_CMD_PER_PAGE = 50;

    // PRD
    const DEFAULT_PRD_ORDER_BY = 'id';
    // const DEFAULT_PRD_ORDER_BY = 'sent_to_bdm_date';
    const DEFAULT_PRD_ORDER_DIRECTION = 'asc';
    const DEFAULT_PRD_PER_PAGE = 50;

    // Statuses
    const STATUS_CREATED_NAME = 'Created';
    const STATUS_IS_SENT_TO_BDM_NAME = 'Sent to BDM';
    const STATUS_IS_SENT_TO_CONFIRMATION_NAME = 'Sent to confirmation';
    const STATUS_IS_CONFIRMED_NAME = 'Confirmed';
    const STATUS_IS_SENT_TO_MANUFACTURER_NAME = 'Sent to manufacturer';
    const STATUS_PRODUCTION_IS_STARTED_NAME = 'Production started';

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    protected $guarded = ['id'];

    protected $casts = [
        'receive_date' => 'date',
        'sent_to_bdm_date' => 'datetime',
        'purchase_date' => 'date',
        'sent_to_confirmation_date' => 'datetime',
        'confirmation_date' => 'datetime',
        'sent_to_manufacturer_date' => 'datetime',
        'production_start_date' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class)->withTrashed();;
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    /**
     * Can have invoices of 'ProductionType' and maybe later (not fully planned yet)
     * can also have invoices of 'Delivery to warehouse' type.
     */
    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    /**
     * 'ProductionType' invoices associated with the 'Order' and
     * can have many 'Products' attached.
     */
    public function productionInvoices()
    {
        return $this->invoices()
            ->onlyProductionType();
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

    public function getIsSentToBdmAttribute(): bool
    {
        return !is_null($this->sent_to_bdm_date);
    }

    public function getIsSentToConfirmationAttribute(): bool
    {
        return !is_null($this->sent_to_confirmation_date);
    }

    public function getIsConfirmedAttribute(): bool
    {
        return !is_null($this->confirmation_date);
    }

    public function getIsSentToManufacturerAttribute(): bool
    {
        return !is_null($this->sent_to_manufacturer_date);
    }

    public function getProductionIsStartedAttribute(): bool
    {
        return !is_null($this->production_start_date);
    }

    public function getAllProductProductionsAreFinishedAttribute(): bool
    {
        if ($this->products->isEmpty()) {
            return false;
        }

        return $this->products->every(fn($product) => $product->production_is_finished);
    }

    public function getAllProductsAreReadyForShipmentFromManufacturerAttribute(): bool
    {
        if ($this->products->isEmpty()) {
            return false;
        }

        return $this->products->every(fn($product) => $product->is_ready_for_shipment_from_manufacturer);
    }

    public function getStatusAttribute(): string
    {
        return match (true) {
            $this->all_products_are_ready_for_shipment_from_manufacturer
            => OrderProduct::STATUS_IS_READY_FOR_SHIPMENT_FROM_MANUFACTURER_NAME,

            $this->all_products_production_is_finished
            => OrderProduct::STATUS_PRODUCTION_IS_FINISHED_NAME,

            $this->production_is_started
            => self::STATUS_PRODUCTION_IS_STARTED_NAME,

            $this->is_sent_to_manufacturer
            => self::STATUS_IS_SENT_TO_MANUFACTURER_NAME,

            $this->is_confirmed
            => self::STATUS_IS_CONFIRMED_NAME,

            $this->is_sent_to_confirmation
            => self::STATUS_IS_SENT_TO_CONFIRMATION_NAME,

            $this->is_sent_to_bdm
            => self::STATUS_IS_SENT_TO_BDM_NAME,

            default
            => self::STATUS_CREATED_NAME,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::updated(function ($record) {
            foreach ($record->products as $product) {
                $product->syncCurrencyWithRelatedProcess();
            }
        });

        static::deleting(function ($record) {
            foreach ($record->products as $product) {
                $product->delete();
            }

            // foreach ($record->invoices as $invoice) {
            //     $invoice->delete();
            // }
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
            'currency',
            'lastComment',

            'manufacturer' => function ($manufacturersQuery) {
                $manufacturersQuery->select(
                    'manufacturers.id',
                    'manufacturers.name',
                    'bdm_user_id',
                )
                    ->with([
                        'bdm:id,name,photo',
                    ]);
            },
        ]);
    }

    public function scopeWithBasicRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
            'products',
            // 'invoices',
        ]);
    }

    public function scopeOnlySentToBdm($query): Builder
    {
        return $query->whereNotNull('sent_to_bdm_date');
    }

    public function scopeOnlySentToManufacturer($query): Builder
    {
        return $query->whereNotNull('sent_to_manufacturer_date');
    }

    public function scopeOnlyProductionIsStarted($query): Builder
    {
        return $query->whereNotNull('production_start_date');
    }

    public function scopeOnlyWithName($query): Builder
    {
        return $query->whereNotNull('name');
    }

    /*
    |--------------------------------------------------------------------------
    | Contracts
    |--------------------------------------------------------------------------
    */

    // Implement method declared in HasTitleAttribute Interface
    public function getTitleAttribute(): string
    {
        return $this->name ?: ('#' . $this->id);
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
    public static function queryPLDRecordsFromRequest(Request $request, string $action = 'paginate', bool $appendAttributes = false)
    {
        $query = self::withBasicRelations()
            ->withBasicRelationCounts();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest(
            $request,
            'DEFAULT_PLD_ORDER_BY',
            'DEFAULT_PLD_ORDER_DIRECTION',
            'DEFAULT_PLD_PER_PAGE'
        );

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
        self::applyStatusFilter($query, $request);

        return $query;
    }

    private static function getFilterConfig(): array
    {
        return [
            'whereIn' => ['id', 'manufacturer_id', 'country_id', 'name'],
            'dateRange' => [
                'receive_date',
                'sent_to_bdm_date',
                'sent_to_manufacturer_date',
                'created_at',
                'updated_at',
            ],

            'relationEqual' => [
                [
                    'inputName' => 'manufacturer_bdm_user_id',
                    'relationName' => 'manufacturer',
                    'relationAttribute' => 'manufacturers.bdm_user_id',
                ],
            ],
        ];
    }

    public static function getFilterStatusOptions(): array
    {
        return [
            self::STATUS_CREATED_NAME,
            self::STATUS_IS_SENT_TO_BDM_NAME,
            self::STATUS_IS_SENT_TO_CONFIRMATION_NAME,
            self::STATUS_IS_CONFIRMED_NAME,
            self::STATUS_IS_SENT_TO_MANUFACTURER_NAME,
            self::STATUS_PRODUCTION_IS_STARTED_NAME,
            OrderProduct::STATUS_PRODUCTION_IS_FINISHED_NAME,
            OrderProduct::STATUS_IS_READY_FOR_SHIPMENT_FROM_MANUFACTURER_NAME,
        ];
    }

    /**
     * Applies a status-based filter to the query.
     */
    public static function applyStatusFilter(Builder $query, Request $request): void
    {
        $status = $request->input('status');

        if (!$status) {
            return;
        }

        match ($status) {
            self::STATUS_CREATED_NAME =>
            $query->whereNull('sent_to_bdm_date'),

            self::STATUS_IS_SENT_TO_BDM_NAME =>
            $query
                ->whereNotNull('sent_to_bdm_date')
                ->whereNull('sent_to_confirmation_date'),

            self::STATUS_IS_SENT_TO_CONFIRMATION_NAME =>
            $query
                ->whereNotNull('sent_to_confirmation_date')
                ->whereNull('confirmation_date'),

            self::STATUS_IS_CONFIRMED_NAME =>
            $query
                ->whereNotNull('confirmation_date')
                ->whereNull('sent_to_manufacturer_date'),

            self::STATUS_IS_SENT_TO_MANUFACTURER_NAME =>
            $query
                ->whereNotNull('sent_to_manufacturer_date')
                ->whereNull('production_start_date'),

            self::STATUS_PRODUCTION_IS_STARTED_NAME =>
            $query
                ->whereNotNull('production_start_date')
                ->whereHas(
                    'products',
                    fn($pq) => $pq->whereNotNull('production_end_date')
                ),

            OrderProduct::STATUS_PRODUCTION_IS_FINISHED_NAME =>
            $query
                ->whereDoesntHave(
                    'products',
                    fn($pq) => $pq->whereNull('production_end_date')
                )
                ->whereHas(
                    'products',
                    fn($pq) =>
                    $pq->whereNotNull('readiness_for_shipment_from_manufacturer_date')
                ),

            OrderProduct::STATUS_IS_READY_FOR_SHIPMENT_FROM_MANUFACTURER_NAME =>
            $query
                ->whereDoesntHave(
                    'products',
                    fn($pq) =>
                    $pq->whereNull('readiness_for_shipment_from_manufacturer_date')
                ),

            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Store & update
    |--------------------------------------------------------------------------
    */

    /**
     * AJAX request by PLD
     */
    public static function storeByPLDFromRequest(PLDOrderStoreRequest $request): void
    {
        $record = self::create($request->only([
            'manufacturer_id',
            'country_id',
            'receive_date',
        ]));

        // HasMany relations
        $record->storeCommentFromRequest($request);

        // Store products
        $products = $request->input('products', []);

        foreach ($products as $product) {
            $newProduct = $record->products()->create([
                'process_id' => $product['process_id'],
                'quantity' => $product['quantity'],
                'serialization_type_id' => $product['serialization_type_id'],
            ]);

            // Store product comments
            if (isset($product['comment']) && $product['comment']) {
                $newProduct->comments()->create([
                    'body' => '<p>' . $product['comment'] . '</p>',
                    'user_id' => auth()->user()->id,
                ]);
            }
        }
    }

    /**
     * AJAX request by PLD
     */
    public function updateByPLDFromRequest(PLDOrderUpdateRequest $request): void
    {
        $this->update($request->safe());

        // HasMany relations
        $this->storeCommentFromRequest($request);
    }

    /**
     * AJAX request by CMD
     */
    public function updateByCMDFromRequest(CMDOrderUpdateRequest $request): void
    {
        $this->update($request->safe());

        // Update 'purchase_date'
        if (is_null($this->purchase_date)) {
            $this->update([
                'purchase_date' => now(),
            ]);
        }

        // HasMany relations
        $this->storeCommentFromRequest($request);

        // Update products
        foreach ($request->products as $id => $product) {
            $orderProduct = $this->products()->findOrFail($id);

            $orderProduct->update([
                'price' => $product['price'],
                'production_status' => isset($product['production_status']) ? $product['production_status'] : null,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Action availability
    |--------------------------------------------------------------------------
    */

    public function canAttachNewProducts(): bool
    {
        // Use eager-loaded count if available, otherwise fallback to counting the relation
        $invoiceCount = $this->production_invoices_count ?? $this->productionInvoices()->count();

        return $invoiceCount === 0;
    }

    public function canAttachNewInvoice(): bool
    {
        return $this->is_sent_to_manufacturer
            && $this->products->contains->canAttachNewProductionInvoice();
    }

    public function canAttachProductionInvoiceOFPrepaymentType(): bool
    {
        // Use eager-loaded count if available, otherwise fallback to counting the relation
        $invoiceCount = $this->production_invoices_count ?? $this->productionInvoices()->count();

        return $invoiceCount === 0;
    }

    public function canAttachProductionInvoiceOfFinalPaymentType(): bool
    {
        return $this->products->contains->canAttachProductionInvoiceOfFinalPaymentType();
    }

    public function canAttachProductionInvoiceOfFullPaymentType(): bool
    {
        return $this->products->contains->canAttachProductionInvoiceOfFullPaymentType();
    }

    /*
    |--------------------------------------------------------------------------
    | Table headers
    |--------------------------------------------------------------------------
    */

    public static function getPLDTableHeadersForUser($user): array|null
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_PLD_ORDERS_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_PLD_ORDERS_NAME))) {
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
