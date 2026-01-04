<?php

namespace App\Models;

use App\Http\Requests\CMD\CMDOrderUpdateRequest;
use App\Http\Requests\PLD\PLDOrderStoreRequest;
use App\Http\Requests\PLD\PLDOrderUpdateRequest;
use App\Notifications\OrderConfirmed;
use App\Notifications\OrderSentToBdm;
use App\Notifications\OrderSentToConfirmation;
use App\Notifications\OrderSentToManufacturer;
use App\Support\Contracts\Model\HasTitleAttribute;
use App\Support\Helpers\ModelHelper;
use App\Support\Helpers\QueryFilterHelper;
use App\Support\Traits\Model\AddsDefaultQueryParamsToRequest;
use App\Support\Traits\Model\GetsMinifiedRecordsWithName;
use App\Support\Traits\Model\HasComments;
use App\Support\Traits\Model\HasModelNamespaceAttributes;
use App\Support\Traits\Model\ScopesOrderingByName;
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
    use ScopesOrderingByName;

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
     * 'ProductionType' invoices associated with the 'Order' and
     * can have multiple attached 'Products'.
     */
    public function productionInvoices()
    {
        return $this->morphMany(Invoice::class, 'invoiceable')
            ->onlyProductionType();
    }

    /*
    |--------------------------------------------------------------------------
    | Additional attributes & appends
    |--------------------------------------------------------------------------
    */

    public static function appendRecordsBasicPLDAttributes($records): void
    {
        foreach ($records as $record) {
            $record->appendBasicPLDAttributes();
        }
    }

    public function appendBasicPLDAttributes(): void
    {
        $this->append([
            'base_model_class',
            'status',
            'is_sent_to_bdm',
            'is_sent_to_confirmation',
            'is_confirmed',
            'is_sent_to_manufacturer',
        ]);
    }

    public static function appendRecordsBasicCMDAttributes($records): void
    {
        foreach ($records as $record) {
            $record->appendBasicCMDAttributes();
        }
    }

    public function appendBasicCMDAttributes(): void
    {
        $this->append([
            'base_model_class',
            'status',
            'is_sent_to_confirmation',
            'can_be_sent_for_confirmation',
            'is_confirmed',
            'is_sent_to_manufacturer',
            'production_is_started',
            'can_attach_any_production_invoice',
            'can_attach_production_prepayment_invoice',
            'can_attach_production_final_payment_invoice',
            'can_attach_production_full_payment_invoice',
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

    public function getCanBeSentForConfirmationAttribute(): bool
    {
        return !is_null($this->name);
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

    /**
     * Required loaded relations:
     * - products
     */
    public function getAllProductProductionsAreEndedAttribute(): bool
    {
        if ($this->products->isEmpty()) {
            return false;
        }

        return $this->products->every(fn($product) => $product->production_is_ended);
    }

    /**
     * Required loaded relations:
     * - products
     */
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

            $this->all_product_productions_are_ended
            => OrderProduct::STATUS_PRODUCTION_IS_ENDED_NAME,

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

    /**
     * Indicates whether any new production invoice can be attached to the order.
     *
     * Requirements:
     * - Order must be sent to the manufacturer
     * - At least one product must allow attaching a production invoice
     *
     * Required loaded relations:
     * - products
     * - products.productionInvoices
     */
    public function getCanAttachAnyProductionInvoiceAttribute(): bool
    {
        return $this->is_sent_to_manufacturer
            && $this->products->contains->can_attach_any_production_invoice;
    }

    /**
     * Indicates whether a prepayment production invoice can be attached to the order.
     *
     * Rule:
     * - No production invoices exist for the order
     *
     * Optimization:
     * - Uses eager-loaded production_invoices_count if available
     */
    public function getCanAttachProductionPrepaymentInvoiceAttribute(): bool
    {
        $invoiceCount = $this->production_invoices_count ?? $this->productionInvoices()->count();

        return $invoiceCount === 0;
    }

    /**
     * Indicates whether a final payment production invoice can be attached.
     *
     * Rule:
     * - At least one product allows attaching a final payment invoice
     *
     * Required loaded relations:
     * - products
     * - products.productionInvoices
     */
    public function getCanAttachProductionFinalPaymentInvoiceAttribute(): bool
    {
        return $this->products->contains->can_attach_production_final_payment_invoice;
    }

    /**
     * Indicates whether a full payment production invoice can be attached.
     *
     * Rule:
     * - At least one product allows attaching a full payment invoice
     *
     * Required loaded relations:
     * - products
     * - products.productionInvoices
     */
    public function getCanAttachProductionFullPaymentInvoiceAttribute(): bool
    {
        return $this->products->contains->can_attach_production_full_payment_invoice;
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

            foreach ($record->invoices as $invoice) {
                $invoice->delete();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWithBasicPLDRelations($query): Builder
    {
        return $query->with([
            'country',
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

            'products', // Maybe required when detecting 'status' of the order/product,
        ]);
    }

    public function scopeWithBasicPLDRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
            'products',
        ]);
    }

    public function scopeWithBasicCMDRelations($query): Builder
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

            'products' => function ($productsQuery) { // Maybe required when detecting 'status' of the order/product,
                $productsQuery->with([
                    'productionInvoices' => function ($invoicesQuery) { // Required in various places
                        $invoicesQuery->with([
                            'type',
                            'paymentType',
                        ]);
                    }
                ]);
            }
        ]);
    }

    public function scopeWithBasicCMDRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
            'products',
            'productionInvoices',
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
        $query = self::withBasicPLDRelations()
            ->withBasicPLDRelationCounts();

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
            self::appendRecordsBasicPLDAttributes($records);
        }

        return $records;
    }

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
    public static function queryCMDRecordsFromRequest(Request $request, string $action = 'paginate', bool $appendAttributes = false)
    {
        $query = self::onlySentToBdm()
            ->withBasicCMDRelations()
            ->withBasicCMDRelationCounts();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest(
            $request,
            'DEFAULT_CMD_ORDER_BY',
            'DEFAULT_CMD_ORDER_DIRECTION',
            'DEFAULT_CMD_PER_PAGE'
        );

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Finalize (sorting & pagination)
        $records = ModelHelper::finalizeQueryForRequest($query, $request, $action);

        // Append attributes unless raw query is requested
        if ($appendAttributes && $action !== 'query') {
            self::appendRecordsBasicCMDAttributes($records);
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
            OrderProduct::STATUS_PRODUCTION_IS_ENDED_NAME,
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

            OrderProduct::STATUS_PRODUCTION_IS_ENDED_NAME =>
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
        $this->update($request->all());

        // HasMany relations
        $this->storeCommentFromRequest($request);
    }

    /**
     * AJAX request by CMD
     */
    public function updateByCMDFromRequest(CMDOrderUpdateRequest $request): void
    {
        $this->update($request->all());

        // Update 'purchase_date'
        if (is_null($this->purchase_date)) {
            $this->update([
                'purchase_date' => now(),
            ]);
        }

        // HasMany relations
        $this->storeCommentFromRequest($request);

        // Update products
        foreach ($request->products as $product) {
            $orderProduct = $this->products()->findOrFail($product['id']);

            $orderProduct->update([
                'price' => $product['price'],
                'production_status' => isset($product['production_status']) ? $product['production_status'] : null,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    /**
     * AJAX request
     *
     * Send to CMD by PLD
     */
    public function sendToBdm(): void
    {
        if (!$this->is_sent_to_bdm) {
            $this->sent_to_bdm_date = now();
            $this->save();

            $notification = new OrderSentToBdm($this);
            User::notifyUsersBasedOnPermission($notification, 'receive-notification-when-order-is-sent-to-CMD-by-PLD');
        }
    }

    /**
     * AJAX request
     *
     * Sent to confirmation to PLD by CMD
     */
    public function sendToConfirmation(): void
    {
        if (!$this->is_sent_to_confirmation) {
            $this->sent_to_confirmation_date = now();
            $this->save();

            $notification = new OrderSentToConfirmation($this);
            User::notifyUsersBasedOnPermission($notification, 'receive-notification-when-order-is-sent-for-confirmation-by-CMD');
        }
    }

    /**
     * AJAX request
     *
     * Confirm by PLD
     */
    public function confirm(): void
    {
        if (!$this->is_confirmed) {
            $this->confirmation_date = now();
            $this->save();

            $notification = new OrderConfirmed($this);
            User::notifyUsersBasedOnPermission($notification, 'receive-notification-when-order-is-confirmed-by-PLD');
        }
    }

    /**
     * AJAX request
     *
     * Sent to manufacturer by CMD
     */
    public function sendToManufacturer(): void
    {
        if (!$this->is_sent_to_manufacturer) {
            $this->sent_to_manufacturer_date = now();
            $this->save();

            $notification = new OrderSentToManufacturer($this);
            User::notifyUsersBasedOnPermission($notification, 'receive-notification-when-order-is-sent-to-manufacturer-by-CMD');
        }
    }

    /**
     * AJAX request
     *
     * Start production by CMD
     */
    public function startProduction(): void
    {
        if (!$this->production_is_started) {
            $this->production_start_date = now();
            $this->save();
        }
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
            ['title' => 'ID', 'key' => 'id', 'width' => 62, 'sortable' => true],
            ['title' => 'fields.BDM', 'key' => 'manufacturer_bdm', 'width' => 146, 'sortable' => false],
            ['title' => 'dates.Receive', 'key' => 'receive_date', 'width' => 142, 'sortable' => true],
            ['title' => 'fields.Manufacturer', 'key' => 'manufacturer_id', 'width' => 140, 'sortable' => true],
            ['title' => 'fields.Country', 'key' => 'country_id', 'width' => 80, 'sortable' => true],
            ['title' => 'Products', 'key' => 'products_count', 'width' => 100, 'sortable' => false],
            ['title' => 'Comments', 'key' => 'comments_count', 'width' => 132, 'sortable' => false],
            ['title' => 'comments.Last', 'key' => 'last_comment_body', 'width' => 200, 'sortable' => false],
            ['title' => 'Status', 'key' => 'status', 'width' => 142, 'sortable' => false],
            ['title' => 'dates.Sent to BDM', 'key' => 'sent_to_bdm_date', 'width' => 160, 'sortable' => true],

            ['title' => 'fields.PO №', 'key' => 'name', 'width' => 136, 'sortable' => true],
            ['title' => 'dates.PO', 'key' => 'purchase_date', 'width' => 120, 'sortable' => true],
            ['title' => 'dates.Confirmation', 'key' => 'confirmation_date', 'width' => 172, 'sortable' => true],
            ['title' => 'dates.Sent to manufacturer', 'key' => 'sent_to_manufacturer_date', 'width' => 168, 'sortable' => true],
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

    public static function getCMDTableHeadersForUser($user): array|null
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_CMD_ORDERS_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_CMD_ORDERS_NAME))) {
            array_push(
                $columns,
                ['title' => 'Record', 'key' => 'edit', 'width' => 60, 'sortable' => false, 'visible' => 1, 'order' => $order++],
            );
        }

        $additionalColumns = [
            ['title' => 'ID', 'key' => 'id', 'width' => 62, 'sortable' => true],
            ['title' => 'fields.BDM', 'key' => 'manufacturer_bdm', 'width' => 146, 'sortable' => false],
            ['title' => 'dates.Receive', 'key' => 'receive_date', 'width' => 142, 'sortable' => true],
            ['title' => 'fields.Manufacturer', 'key' => 'manufacturer_id', 'width' => 140, 'sortable' => true],
            ['title' => 'fields.Country', 'key' => 'country_id', 'width' => 80, 'sortable' => true],
            ['title' => 'Products', 'key' => 'products_count', 'width' => 100, 'sortable' => false],
            ['title' => 'Comments', 'key' => 'comments_count', 'width' => 132, 'sortable' => false],
            ['title' => 'comments.Last', 'key' => 'last_comment_body', 'width' => 200, 'sortable' => false],
            ['title' => 'Status', 'key' => 'status', 'width' => 142, 'sortable' => false],
            ['title' => 'dates.Sent to BDM', 'key' => 'sent_to_bdm_date', 'width' => 160, 'sortable' => true],

            ['title' => 'fields.PO №', 'key' => 'name', 'width' => 136, 'sortable' => true],
            ['title' => 'dates.PO', 'key' => 'purchase_date', 'width' => 120, 'sortable' => true],
            ['title' => 'fields.Currency', 'key' => 'currency_id', 'width' => 86, 'sortable' => true],
            ['title' => 'dates.Sent to confirmation', 'key' => 'sent_to_confirmation_date', 'width' => 244, 'sortable' => true],
            ['title' => 'dates.Confirmation', 'key' => 'confirmation_date', 'width' => 172, 'sortable' => true],
            ['title' => 'dates.Sent to manufacturer', 'key' => 'sent_to_manufacturer_date', 'width' => 168, 'sortable' => true],
            ['title' => 'dates.Expected dispatch', 'key' => 'expected_dispatch_date', 'width' => 190, 'sortable' => false],
            ['title' => 'Invoices', 'key' => 'production_invoices_count', 'width' => 212, 'sortable' => false],
            ['title' => 'dates.Production start', 'key' => 'production_start_date', 'width' => 204, 'sortable' => true],
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
