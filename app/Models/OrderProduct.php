<?php

namespace App\Models;

use App\Http\Requests\CMD\CMDOrderProductUpdateRequest;
use App\Http\Requests\DD\DDOrderProductUpdateRequest;
use App\Http\Requests\MD\MDOrderProductUpdateRequest;
use App\Http\Requests\MD\MDSerializedByManufacturerUpdateRequest;
use App\Http\Requests\PLD\PLDOrderProductUpdateRequest;
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
use Illuminate\Support\Facades\Gate;

class OrderProduct extends Model implements HasTitleAttribute
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
    const STORAGE_FILES_PATH = 'app/private/order-products';
    const PACKING_LIST_FILE_FOLDER_NAME = 'packing-lists';
    const COA_FILE_FOLDER_NAME = 'COA-files';
    const COO_FILE_FOLDER_NAME = 'COO-files';
    const DECLARATION_FOR_EUROPE_FILE_FOLDER_NAME = 'declarations-for-europe';

    // PLD
    const DEFAULT_PLD_ORDER_BY = 'order_id';
    // const DEFAULT_PLD_ORDER_BY = 'updated_at';
    const DEFAULT_PLD_ORDER_DIRECTION = 'asc';
    const DEFAULT_PLD_PER_PAGE = 50;

    // CMD
    const DEFAULT_CMD_ORDER_BY = 'id';
    // const DEFAULT_CMD_ORDER_BY = 'order_sent_to_bdm_date';
    const DEFAULT_CMD_ORDER_DIRECTION = 'asc';
    const DEFAULT_CMD_PER_PAGE = 50;

    // PRD
    const DEFAULT_PRD_ORDER_BY = 'id';
    // const DEFAULT_PRD_ORDER_BY = 'order_sent_to_manufacturer_date';
    const DEFAULT_PRD_ORDER_DIRECTION = 'asc';
    const DEFAULT_PRD_PER_PAGE = 50;

    // DD
    const DEFAULT_DD_ORDER_BY = 'id';
    // const DEFAULT_DD_ORDER_BY = 'order_sent_to_manufacturer_date';
    const DEFAULT_DD_ORDER_DIRECTION = 'asc';
    const DEFAULT_DD_PER_PAGE = 50;

    // MD
    const DEFAULT_MD_SERIALIZED_BY_MANUFACTURER_ORDER_BY = 'id';
    // const DEFAULT_MD_SERIALIZED_BY_MANUFACTURER_ORDER_DIRECTION = 'order_production_start_date';
    const DEFAULT_MD_SERIALIZED_BY_MANUFACTURER_ORDER_DIRECTION = 'asc';
    const DEFAULT_MD_SERIALIZED_BY_MANUFACTURER_PER_PAGE = 50;

    // Statuses
    const STATUS_PRODUCTION_IS_ENDED_NAME = 'Production is ended';
    const STATUS_IS_READY_FOR_SHIPMENT_FROM_MANUFACTURER_NAME = 'Ready for shipment from manufacturer';

    // Serialization statuses
    const SERIALIZATION_STATUS_SERIALIZATION_CODES_REQUESTED_NAME = 'Serialization codes requested';
    const SERIALIZATION_STATUS_SERIALIZATION_CODES_SENT_NAME = 'Serialization codes sent';
    const SERIALIZATION_STATUS_SERIALIZATION_REPORT_RECEIVED_NAME = 'Serialization report received';
    const SERIALIZATION_STATUS_REPORT_SENT_TO_HUB_NAME = 'Report sent to hub';

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    protected $guarded = ['id'];

    protected $casts = [
        'date_of_sending_new_layout_to_manufacturer' => 'date',
        'date_of_receiving_print_proof_from_manufacturer' => 'date',
        'layout_approved_date' => 'date',
        'serialization_codes_request_date' => 'datetime',
        'serialization_codes_sent_date' => 'datetime',
        'serialization_report_recieved_date' => 'datetime',
        'report_sent_to_hub_date' => 'datetime',
        'production_end_date' => 'datetime',
        'readiness_for_shipment_from_manufacturer_date' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function process()
    {
        return $this->belongsTo(Process::class)->withTrashed();
    }

    // Slow relation, use only when necessary.
    // On eager loading use $orderProduct->process->product || with('process.product')
    // On filtering use whereHas('product', ...)
    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            Process::class,
            'id', // Foreign key on the Processes table
            'id', // Foreign key on the Products table
            'process_id', // Local key on the Orders table
            'product_id' // Local key on the Processes table
        )
            ->withTrashedParents()
            ->withTrashed();
    }

    // Orders can have invoices of 'ProductionType'
    // and that invoice can have many attached 'Products'.
    public function productionInvoices()
    {
        return $this->belongsToMany(Invoice::class)
            ->onlyProductionType();
    }

    public function serializationType()
    {
        return $this->belongsTo(SerializationType::class);
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
            'total_price',
            'production_prepayment_invoice',
            'production_final_or_full_payment_invoice',
        ]);

        $this->order->append([
            'title',
        ]);

        $this->process->append([
            'full_english_product_label',
            'full_russian_product_label',
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
            'total_price',
            'production_is_started',
            'production_is_ended',
            'packing_list_file_url',
            'coa_file_url',
            'coo_file_url',
            'declaration_for_europe_file_url',
            'is_ready_for_shipment_from_manufacturer',
            'can_be_set_as_ready_for_shipment_from_manufacturer',
        ]);

        $this->order->append([
            'title',
        ]);

        $this->process->append([
            'full_english_product_label',
            'full_russian_product_label',
        ]);
    }

    public static function appendRecordsBasicDDAttributes($records): void
    {
        foreach ($records as $record) {
            $record->appendBasicDDAttributes();
        }
    }

    public function appendBasicDDAttributes(): void
    {
        $this->append([
            'base_model_class',
        ]);

        $this->process->append([
            'full_english_product_label',
            'full_russian_product_label',
        ]);
    }

    public static function appendRecordsBasicMDAttributes($records): void
    {
        foreach ($records as $record) {
            $record->appendBasicMDAttributes();
        }
    }

    public function appendBasicMDAttributes(): void
    {
        $this->append([
            'base_model_class',
            'serialization_status',
        ]);

        $this->process->append([
            'full_english_product_label',
            'full_russian_product_label',
        ]);
    }

    public function getLayoutApprovedAttribute(): bool
    {
        return !is_null($this->layout_approved_date);
    }

    public function getTotalPriceAttribute()
    {
        $total = $this->quantity * $this->price;

        return floor($total * 100) / 100;
    }

    public function getProductionIsStartedAttribute(): bool
    {
        return $this->order->production_is_started;
    }

    public function getProductionIsEndedAttribute(): bool
    {
        return !is_null($this->production_end_date);
    }

    /**
     * Used on "cmd.order-products.edit" page
     * to display additional inputs
     */
    public function getCanBePreparedForShippingFromManufacturerAttribute(): bool
    {
        return $this->productionInvoices
            ->whereIn('payment_type_id', [
                InvoicePaymentType::FULL_PAYMENT_ID,
                InvoicePaymentType::FINAL_PAYMENT_ID,
            ])
            ->whereNotNull('sent_for_payment_date')
            ->isNotEmpty();
    }

    public function getCanBeSetAsReadyForShipmentFromManufacturerAttribute(): bool
    {
        return $this->production_is_ended
            && !is_null($this->packing_list_file);
    }

    public function getIsReadyForShipmentFromManufacturerAttribute(): bool
    {
        return !is_null($this->readiness_for_shipment_from_manufacturer_date);
    }

    public function getStatusAttribute(): string
    {
        return match (true) {
            $this->is_ready_for_shipment_from_manufacturer
            => self::STATUS_IS_READY_FOR_SHIPMENT_FROM_MANUFACTURER_NAME,

            $this->production_is_ended
            => self::STATUS_PRODUCTION_IS_ENDED_NAME,

            default
            => $this->order->status,
        };
    }

    public function getSerializationStatusAttribute(): string
    {
        return match (true) {
            !is_null($this->report_sent_to_hub_date)
            => self::SERIALIZATION_STATUS_REPORT_SENT_TO_HUB_NAME,

            !is_null($this->serialization_report_recieved_date)
            => self::SERIALIZATION_STATUS_SERIALIZATION_REPORT_RECEIVED_NAME,

            !is_null($this->serialization_codes_sent_date)
            => self::SERIALIZATION_STATUS_SERIALIZATION_CODES_SENT_NAME,

            !is_null($this->serialization_codes_request_date)
            => self::SERIALIZATION_STATUS_SERIALIZATION_CODES_REQUESTED_NAME,

            default
            => Order::STATUS_PRODUCTION_IS_STARTED_NAME,
        };
    }

    /**
     * Indicates whether any new production invoice can be attached to the product.
     *
     * Rule:
     * - Any of the payment-type-specific rules must be satisfied
     *
     * Required loaded relations:
     * - productionInvoices
     */
    public function getCanAttachAnyProductionInvoiceAttribute(): bool
    {
        return $this->can_attach_production_prepayment_invoice
            || $this->can_attach_production_final_payment_invoice
            || $this->can_attach_production_full_payment_invoice;
    }

    /**
     * Indicates whether a prepayment production invoice can be attached.
     *
     * Rule:
     * - No production invoices exist for the product
     *
     * Required loaded relations:
     * - productionInvoices
     */
    public function getCanAttachProductionPrepaymentInvoiceAttribute(): bool
    {
        return $this->productionInvoices->count() === 0;
    }

    /**
     * Indicates whether a final payment production invoice can be attached.
     *
     * Rule:
     * - A prepayment invoice exists
     * - No final payment invoice exists yet
     *
     * Required loaded relations:
     * - productionInvoices
     */
    public function getCanAttachProductionFinalPaymentInvoiceAttribute(): bool
    {
        $hasPrepayment = $this->productionInvoices
            ->where('payment_type_id', InvoicePaymentType::PREPAYMENT_ID)
            ->isNotEmpty();

        $hasFinalPayment = $this->productionInvoices
            ->where('payment_type_id', InvoicePaymentType::FINAL_PAYMENT_ID)
            ->isNotEmpty();

        return $hasPrepayment && ! $hasFinalPayment;
    }

    /**
     * Indicates whether a full payment production invoice can be attached.
     *
     * Rule:
     * - No production invoices exist for the product
     *
     * Required loaded relations:
     * - productionInvoices
     */
    public function getCanAttachProductionFullPaymentInvoiceAttribute(): bool
    {
        return $this->productionInvoices->count() === 0;
    }

    /**
     * Required loaded relations:
     * - productionInvoices
     */
    public function getProductionPrepaymentInvoiceAttribute()
    {
        return $this->productionInvoices
            ->where('payment_type_id', InvoicePaymentType::PREPAYMENT_ID)
            ->first();
    }

    /**
     * Required loaded relations:
     * - productionInvoices
     */
    public function getProductionFinalOrFullPaymentInvoiceAttribute()
    {
        return $this->productionInvoices
            ->whereIn('payment_type_id', [
                InvoicePaymentType::FINAL_PAYMENT_ID,
                InvoicePaymentType::FULL_PAYMENT_ID,
            ])
            ->first();
    }

    public function getPackingListFileUrlAttribute(): string
    {
        return route('order-products.files', [
            'path' => self::PACKING_LIST_FILE_FOLDER_NAME . '/' . $this->packing_list_file,
        ]);
    }

    public function getCooFileUrlAttribute(): string
    {
        return route('order-products.files', [
            'path' => self::COO_FILE_FOLDER_NAME . '/' . $this->coo_file,
        ]);
    }

    public function getCoaFileUrlAttribute(): string
    {
        return route('order-products.files', [
            'path' => self::COA_FILE_FOLDER_NAME . '/' . $this->coa_file,
        ]);
    }

    public function getDeclarationForEuropeFileUrlAttribute(): string
    {
        return route('order-products.files', [
            'path' => self::DECLARATION_FOR_EUROPE_FILE_FOLDER_NAME . '/' . $this->declaration_for_europe_file,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::created(function ($record) {
            $record->syncPriceWithRelatedProcess();
        });

        static::updated(function ($record) {
            $record->syncPriceWithRelatedProcess();
        });

        static::deleting(function ($record) {
            // Detach 'productionInvoices'
            $record->productionInvoices()->detach();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWithBasicPLDRelations($query)
    {
        return $query->with([
            'lastComment',
            'serializationType',

            'productionInvoices', // Required when detecting 'payment_completed_date' of invoices

            'order' => function ($orderQuery) {
                $orderQuery->with([ // $orderQuery->withBasicRelations() not used because of redundant/extra relations
                    'country',
                    'currency',

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

                    'products', // Maybe required when detecting 'status' of the order/product
                ]);
            },

            'process' => function ($processQuery) {
                $processQuery->withRelationsForOrderProduct()
                    ->withOnlySelectsForOrderProduct();
            },
        ]);
    }

    public function scopeWithBasicPLDRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
        ]);
    }

    public function scopeWithBasicCMDRelations($query)
    {
        return $query->with([
            'lastComment',
            'serializationType',

            'order' => function ($orderQuery) {
                $orderQuery->with([ // $orderQuery->withBasicRelations() not used because of redundant 'lastComment'
                    'country',
                    'currency',

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

                    'products', // Maybe required when detecting 'status' of the order/product
                ]);
            },

            'process' => function ($processQuery) {
                $processQuery->withRelationsForOrderProduct()
                    ->withOnlySelectsForOrderProduct();
            },
        ]);
    }

    public function scopeWithBasicCMDRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
        ]);
    }

    public function scopeWithBasicDDRelations($query)
    {
        return $query->with([
            'lastComment',

            'order' => function ($orderQuery) {
                $orderQuery->with([ // $orderQuery->withBasicRelations() not used because of redundant relations
                    'country',

                    'manufacturer' => function ($manufacturersQuery) {
                        $manufacturersQuery->select(
                            'manufacturers.id',
                            'manufacturers.name',
                        );
                    },
                ]);
            },

            'process' => function ($processQuery) {
                $processQuery->withRelationsForOrderProduct()
                    ->withOnlySelectsForOrderProduct();
            },
        ]);
    }

    public function scopeWithBasicDDRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
        ]);
    }

    public function scopeWithBasicMDRelations($query)
    {
        return $query->with([
            'lastComment',

            'order' => function ($orderQuery) {
                $orderQuery->with([ // $orderQuery->withBasicRelations() not used because of redundant relations
                    'country',

                    'manufacturer' => function ($manufacturersQuery) {
                        $manufacturersQuery->select(
                            'manufacturers.id',
                            'manufacturers.name',
                        );
                    },
                ]);
            },

            'process' => function ($processQuery) {
                $processQuery->withRelationsForOrderProduct()
                    ->withOnlySelectsForOrderProduct();
            },
        ]);
    }

    public function scopeWithBasicMDRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
        ]);
    }

    public function scopeOnlySentToBdm($query)
    {
        return $query->whereHas('order', function ($orderQuery) {
            $orderQuery->onlySentToBdm();
        });
    }

    public function scopeOnlySentToManufacturer($query)
    {
        return $query->whereHas('order', function ($orderQuery) {
            $orderQuery->onlySentToManufacturer();
        });
    }

    public function scopeOnlyProductionIsEnded($query)
    {
        return $query->whereNotNull('production_end_date');
    }

    public function scopeOnlyReadyForShipmentFromManufacturer($query)
    {
        return $query->whereNotNull('readiness_for_shipment_from_manufacturer_date');
    }

    public function scopeOnlyWithInvoicesSentForPayment($query)
    {
        return $query->whereHas('order', function ($orderQuery) {
            $orderQuery->onlyWithInvoicesSentForPayment();
        });
    }

    public function scopeOnlySerializedByManufacturer($query)
    {
        $serializationTypeId = SerializationType::findByName(SerializationType::BY_MANUFACTURER_TYPE_NAME)->id;

        return $query->where('serialization_type_id', $serializationTypeId);
    }

    /*
    |--------------------------------------------------------------------------
    | Contracts
    |--------------------------------------------------------------------------
    */

    // Implement method declared in HasTitleAttribute Interface
    public function getTitleAttribute(): string
    {
        return $this->process->trademark_en ?: ('#' . $this->id);
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
        $query = self::withBasicCMDRelations()
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
    public static function queryDDRecordsFromRequest(Request $request, string $action = 'paginate', bool $appendAttributes = false)
    {
        $query = self::onlySentToManufacturer()
            ->withBasicDDRelations()
            ->withBasicDDRelationCounts();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest(
            $request,
            'DEFAULT_DD_ORDER_BY',
            'DEFAULT_DD_ORDER_DIRECTION',
            'DEFAULT_DD_PER_PAGE'
        );

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Finalize (sorting & pagination)
        $records = ModelHelper::finalizeQueryForRequest($query, $request, $action);

        // Append attributes unless raw query is requested
        if ($appendAttributes && $action !== 'query') {
            self::appendRecordsBasicDDAttributes($records);
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
    public static function queryMDRecordsFromRequest(Request $request, string $action = 'paginate', bool $appendAttributes = false)
    {
        $query = self::onlyProductionIsEnded()
            ->onlySerializedByManufacturer()
            ->withBasicMDRelations()
            ->withBasicMDRelationCounts();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest(
            $request,
            'DEFAULT_MD_SERIALIZED_BY_MANUFACTURER_ORDER_BY',
            'DEFAULT_MD_SERIALIZED_BY_MANUFACTURER_ORDER_DIRECTION',
            'DEFAULT_MD_SERIALIZED_BY_MANUFACTURER_PER_PAGE'
        );

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Finalize (sorting & pagination)
        $records = ModelHelper::finalizeQueryForRequest($query, $request, $action);

        // Append attributes unless raw query is requested
        if ($appendAttributes && $action !== 'query') {
            self::appendRecordsBasicMDAttributes($records);
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
            'whereEqual' => ['process_id', 'new_layout', 'order_id'],
            'whereIn' => ['id'],
            'dateRange' => [
                'date_of_sending_new_layout_to_manufacturer',
                'date_of_receiving_print_proof_from_manufacturer',
                'layout_approved_date',
                'created_at',
                'updated_at',
            ],

            'relationEqual' => [
                [
                    'inputName' => 'order_manufacturer_bdm_user_id',
                    'relationName' => 'order.manufacturer',
                    'relationAttribute' => 'manufacturers.bdm_user_id',
                ],
            ],

            'relationIn' => [
                [
                    'inputName' => 'order_name',
                    'relationName' => 'order',
                    'relationAttribute' => 'orders.name',
                ],

                [
                    'inputName' => 'order_manufacturer_id',
                    'relationName' => 'order.manufacturer',
                    'relationAttribute' => 'manufacturers.id',
                ],

                [
                    'inputName' => 'process_country_id',
                    'relationName' => 'process',
                    'relationAttribute' => 'processes.country_id',
                ],

                [
                    'inputName' => 'process_marketing_authorization_holder_id',
                    'relationName' => 'process',
                    'relationAttribute' => 'processes.marketing_authorization_holder_id',
                ],

                [
                    'inputName' => 'process_trademark_en',
                    'relationName' => 'process',
                    'relationAttribute' => 'processes.trademark_en',
                ],

                [
                    'inputName' => 'process_trademark_ru',
                    'relationName' => 'process',
                    'relationAttribute' => 'processes.trademark_ru',
                ],
            ],
        ];
    }

    /**
     * Applies a status-based filter to the 'OrderProduct' query.
     *
     * Some filters are delegated to 'Order' model filter.
     */
    public static function applyStatusFilter(Builder $query, Request $request): void
    {
        $status = $request->input('status');

        if (!$status) {
            return;
        }

        match ($status) {
            Order::STATUS_PRODUCTION_IS_STARTED_NAME =>
            $query
                ->whereHas(
                    'order',
                    fn($oq) => $oq->whereNotNull('production_start_date')
                )
                ->whereNull('production_end_date'),

            self::STATUS_PRODUCTION_IS_ENDED_NAME =>
            $query
                ->whereNotNull('production_end_date')
                ->whereNull('readiness_for_shipment_from_manufacturer_date'),

            self::STATUS_IS_READY_FOR_SHIPMENT_FROM_MANUFACTURER_NAME =>
            $query
                ->whereNotNull('readiness_for_shipment_from_manufacturer_date'),

            default =>
            $query->whereHas(
                'order',
                fn($orderQuery) =>
                Order::applyStatusFilter($orderQuery, $request)
            ),
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
    public function updateByPLDFromRequest(PLDOrderProductUpdateRequest $request): void
    {
        $this->update($request->all());

        // HasMany relations
        $this->storeCommentFromRequest($request);
    }

    /**
     * AJAX request by CMD
     */
    public function updateByCMDFromRequest(CMDOrderProductUpdateRequest $request): void
    {
        $this->update($request->all());

        // HasMany relations
        $this->storeCommentFromRequest($request);

        // Upload files
        $this->uploadFile('packing_list_file', self::getPackingListFileFolderPath());
        $this->uploadFile('coa_file', self::getCoaFileFolderPath());
        $this->uploadFile('coo_file', self::getCooFileFolderPath());
        $this->uploadFile('declaration_for_europe_file', self::getDeclarationForEuropeFileFolderPath());
    }

    /**
     * AJAX request by DD
     */
    function updateByDDFromRequest(DDOrderProductUpdateRequest $request): void
    {
        $this->fill($request->all());

        // Validate 'date_of_receiving_print_proof_from_manufacturer' attribute
        if (!$this->new_layout) {
            $this->date_of_receiving_print_proof_from_manufacturer = null;
        }

        $this->save();

        // HasMany relations
        $this->storeCommentFromRequest($request);
    }

    /**
     * AJAX request by MD
     */
    function updateSerializedByManufacturerByMDFromRequest(MDSerializedByManufacturerUpdateRequest $request): void
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
     * End production by CMD
     */
    public function endProduction(): void
    {
        if (!$this->production_is_ended) {
            $this->production_end_date = now();
            $this->save();
        }
    }

    /**
     * AJAX request
     *
     * End production by CMD
     */
    public function setAsReadyForShipmentFromManufacturer(): void
    {
        if (!$this->is_ready_for_shipment_from_manufacturer) {
            $this->readiness_for_shipment_from_manufacturer_date = now();
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

    public static function getCooFileFolderPath(): string
    {
        return storage_path(self::STORAGE_FILES_PATH . '/' . self::COO_FILE_FOLDER_NAME);
    }

    public static function getCoaFileFolderPath(): string
    {
        return storage_path(self::STORAGE_FILES_PATH . '/' . self::COA_FILE_FOLDER_NAME);
    }

    public static function getDeclarationForEuropeFileFolderPath(): string
    {
        return storage_path(self::STORAGE_FILES_PATH . '/' . self::DECLARATION_FOR_EUROPE_FILE_FOLDER_NAME);
    }

    /*
    |--------------------------------------------------------------------------
    | Synchronizations
    |--------------------------------------------------------------------------
    */

    /**
     * Sync the price of the related Process with this model.
     * If the price differs, it updates the 'increased_price' field.
     *
     * Used in models created/updated events.
     */
    public function syncPriceWithRelatedProcess(): void
    {
        if ($this->price && ($this->process->agreed_price != $this->price)) {
            $this->process->update([
                'increased_price' => $this->price
            ]);
        }
    }

    /**
     * Sync the currency of the related Process with this model.
     *
     * Used in related 'Order' models updated event!
     */
    public function syncCurrencyWithRelatedProcess(): void
    {
        $this->refresh();

        if ($this->order->currency_id && ($this->process->currency_id != $this->order->currency_id)) {
            $this->process->update([
                'currency_id' => $this->order->currency_id,
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Table headers
    |--------------------------------------------------------------------------
    */

    public static function getPLDTableHeadersForUser($user): array|null
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_PLD_ORDER_PRODUCTS_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_PLD_ORDER_PRODUCTS_NAME))) {
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

    public static function getCMDTableHeadersForUser($user): array|null
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_CMD_ORDER_PRODUCTS_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_CMD_ORDER_PRODUCTS_NAME))) {
            array_push(
                $columns,
                ['title' => 'Record', 'key' => 'edit', 'width' => 60, 'sortable' => false, 'visible' => 1, 'order' => $order++],
            );
        }

        $additionalColumns = [
            ['title' => 'ID', 'key' => 'id', 'width' => 62, 'sortable' => true],
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

            ['title' => 'dates.Sent to BDM', 'key' => 'order_sent_to_bdm_date', 'width' => 142, 'sortable' => false],
            ['title' => 'fields.PO №', 'key' => 'order_name', 'width' => 136, 'sortable' => false],
            ['title' => 'dates.PO', 'key' => 'order_purchase_date', 'width' => 120, 'sortable' => false],
            ['title' => 'dates.Sent to confirmation', 'key' => 'order_sent_to_confirmation_date', 'width' => 224, 'sortable' => false],
            ['title' => 'dates.Confirmation', 'key' => 'order_confirmation_date', 'width' => 156, 'sortable' => false],
            ['title' => 'dates.Sent to manufacturer', 'key' => 'order_sent_to_manufacturer_date', 'width' => 152, 'sortable' => false],
            ['title' => 'dates.Production start', 'key' => 'order_production_start_date', 'width' => 192, 'sortable' => false],
            ['title' => 'fields.Production status', 'key' => 'production_status', 'width' => 180, 'sortable' => false],
            ['title' => 'dates.Production end', 'key' => 'production_end_date', 'width' => 232, 'sortable' => true],
            ['title' => 'fields.Packing list', 'key' => 'packing_list_file', 'width' => 152, 'sortable' => false],
            ['title' => 'fields.COA', 'key' => 'coa_file', 'width' => 152, 'sortable' => false],
            ['title' => 'fields.COO', 'key' => 'coo_file', 'width' => 152, 'sortable' => false],
            ['title' => 'fields.Declaration for EUR1', 'key' => 'declaration_for_europe_file', 'width' => 160, 'sortable' => false],
            ['title' => 'dates.Ready for shipment', 'key' => 'readiness_for_shipment_from_manufacturer_date', 'width' => 160, 'sortable' => true],

            ['title' => 'fields.Layout status', 'key' => 'new_layout', 'width' => 126, 'sortable' => true],
            ['title' => 'dates.Layout sent', 'key' => 'date_of_sending_new_layout_to_manufacturer', 'width' => 178, 'sortable' => true],
            ['title' => 'dates.Print proof receive', 'key' => 'date_of_receiving_print_proof_from_manufacturer', 'width' => 228, 'sortable' => true],
            ['title' => 'fields.Box article', 'key' => 'box_article', 'width' => 140, 'sortable' => false],
            ['title' => 'dates.Layout approved', 'key' => 'layout_approved_date', 'width' => 188, 'sortable' => true],

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

    public static function getDDTableHeadersForUser($user): array|null
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_DD_ORDER_PRODUCTS_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_DD_ORDER_PRODUCTS_NAME))) {
            array_push(
                $columns,
                ['title' => 'Record', 'key' => 'edit', 'width' => 60, 'sortable' => false, 'visible' => 1, 'order' => $order++],
            );
        }

        $additionalColumns = [
            ['title' => 'ID', 'key' => 'id', 'width' => 62, 'sortable' => true],
            ['title' => 'fields.Manufacturer', 'key' => 'order_manufacturer_id', 'width' => 140, 'sortable' => false],
            ['title' => 'fields.Country', 'key' => 'order_country_id', 'width' => 80, 'sortable' => false],
            ['title' => 'fields.TM Eng', 'key' => 'process_trademark_en', 'width' => 146, 'sortable' => false],
            ['title' => 'fields.TM Rus', 'key' => 'process_trademark_ru', 'width' => 146, 'sortable' => false],
            ['title' => 'fields.MAH', 'key' => 'process_marketing_authorization_holder_id', 'width' => 102, 'sortable' => true],
            ['title' => 'fields.Quantity', 'key' => 'quantity', 'width' => 112, 'sortable' => false],
            ['title' => 'fields.PO №', 'key' => 'order_name', 'width' => 136, 'sortable' => false],
            ['title' => 'dates.Sent to BDM', 'key' => 'order_sent_to_bdm_date', 'width' => 142, 'sortable' => false],
            ['title' => 'dates.Sent to manufacturer', 'key' => 'order_sent_to_manufacturer_date', 'width' => 152, 'sortable' => false],

            ['title' => 'Comments', 'key' => 'comments_count', 'width' => 132, 'sortable' => false],
            ['title' => 'comments.Last', 'key' => 'last_comment_body', 'width' => 200, 'sortable' => false],

            ['title' => 'fields.Layout status', 'key' => 'new_layout', 'width' => 126, 'sortable' => true],
            ['title' => 'dates.Layout sent', 'key' => 'date_of_sending_new_layout_to_manufacturer', 'width' => 178, 'sortable' => true],
            ['title' => 'dates.Print proof receive', 'key' => 'date_of_receiving_print_proof_from_manufacturer', 'width' => 228, 'sortable' => true],
            ['title' => 'fields.Box article', 'key' => 'box_article', 'width' => 140, 'sortable' => false],
            ['title' => 'dates.Layout approved', 'key' => 'layout_approved_date', 'width' => 188, 'sortable' => true],

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

    public static function getMDTableHeadersForUser($user): array|null
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_MD_SERIALIZED_BY_MANUFACTURER_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_MD_SERIALIZED_BY_MANUFACTURER_NAME))) {
            array_push(
                $columns,
                ['title' => 'Record', 'key' => 'edit', 'width' => 60, 'sortable' => false, 'visible' => 1, 'order' => $order++],
            );
        }

        $additionalColumns = [
            ['title' => 'ID', 'key' => 'id', 'width' => 62, 'sortable' => true],
            ['title' => 'Status', 'key' => 'status', 'width' => 144, 'sortable' => false],
            ['title' => 'fields.Manufacturer', 'key' => 'order_manufacturer_id', 'width' => 140, 'sortable' => false],
            ['title' => 'fields.Country', 'key' => 'order_country_id', 'width' => 80, 'sortable' => false],
            ['title' => 'fields.TM Eng', 'key' => 'process_trademark_en', 'width' => 146, 'sortable' => false],
            ['title' => 'fields.TM Rus', 'key' => 'process_trademark_ru', 'width' => 146, 'sortable' => false],
            ['title' => 'fields.Quantity', 'key' => 'quantity', 'width' => 112, 'sortable' => false],

            ['title' => 'dates.Production end', 'key' => 'production_end_date', 'width' => 232, 'sortable' => true],

            ['title' => 'dates.Serialization codes request', 'key' => 'serialization_codes_request_date', 'width' => 262, 'sortable' => true],
            ['title' => 'dates.Serialization codes sent', 'key' => 'serialization_codes_sent_date', 'width' => 268, 'sortable' => true],
            ['title' => 'dates.Serialization report received', 'key' => 'serialization_report_recieved_date', 'width' => 296, 'sortable' => true],
            ['title' => 'dates.Report sent to hub', 'key' => 'report_sent_to_hub_date', 'width' => 208, 'sortable' => true],

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
