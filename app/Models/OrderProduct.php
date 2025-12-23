<?php

namespace App\Models;

use App\Http\Requests\CMD\CMDOrderProductUpdateRequest;
use App\Http\Requests\DD\DDOrderProductUpdateRequest;
use App\Http\Requests\MSD\MSDOrderProductUpdateRequest;
use App\Http\Requests\PLD\PLDOrderProductStoreRequest;
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
    const PACKING_LIST_FOLDER_NAME = 'packing-lists';
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

    // MSD
    const DEFAULT_MSD_SERIALIZED_BY_MANUFACTURER_ORDER_BY = 'id';
    // const DEFAULT_MSD_SERIALIZED_BY_MANUFACTURER_ORDER_DIRECTION = 'order_production_start_date';
    const DEFAULT_MSD_SERIALIZED_BY_MANUFACTURER_ORDER_DIRECTION = 'asc';
    const DEFAULT_MSD_SERIALIZED_BY_MANUFACTURER_PER_PAGE = 50;

    // Statuses
    const STATUS_PRODUCTION_IS_FINISHED_NAME = 'Production is finished';
    const STATUS_IS_READY_FOR_SHIPMENT_FROM_MANUFACTURER_NAME = 'Ready for shipment from manufacturer';

    // Serialization statuses
    const STATUS_REPORT_SENT_TO_HUB_NAME = 'Report sent to hub';
    const STATUS_SERIALIZATION_CODES_REQUESTED_NAME = 'Serialization codes requested';
    const STATUS_SERIALIZATION_CODES_SENT_NAME = 'Serialization codes sent';
    const STATUS_SERIALIZATION_REPORT_RECEIVED_NAME = 'Serialization report received';

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

    public function getProductionPrepaymentInvoiceAttribute()
    {
        return $this->productionInvoices
            ->where('payment_type_id', InvoicePaymentType::PREPAYMENT_ID)
            ->first();
    }

    public function getProductionFinalOrFullPaymentInvoiceAttribute()
    {
        return $this->productionInvoices
            ->whereIn('payment_type_id', [
                InvoicePaymentType::FINAL_PAYMENT_ID,
                InvoicePaymentType::FULL_PAYMENT_ID,
            ])
            ->first();
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

    public function getProductionIsFinishedAttribute(): bool
    {
        return !is_null($this->production_end_date);
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

            $this->production_is_finished
            => self::STATUS_PRODUCTION_IS_FINISHED_NAME,

            default
            => $this->order->status,
        };
    }

    public function getSerializationStatusAttribute(): string
    {
        return match (true) {
            $this->report_sent_to_hub_date
            => self::STATUS_REPORT_SENT_TO_HUB_NAME,

            $this->serialization_report_recieved_date
            => self::STATUS_SERIALIZATION_REPORT_RECEIVED_NAME,

            $this->serialization_codes_sent_date
            => self::STATUS_SERIALIZATION_CODES_SENT_NAME,

            $this->serialization_codes_request_date
            => self::STATUS_SERIALIZATION_CODES_REQUESTED_NAME,

            default
            => Order::STATUS_PRODUCTION_IS_STARTED_NAME,
        };
    }

    public function getCanBePreparedForShippingFromManufacturerAttribute(): bool
    {
        return $this->productionInvoices()
            ->whereIn('payment_type_id', [
                InvoicePaymentType::FULL_PAYMENT_ID,
                InvoicePaymentType::FINAL_PAYMENT_ID,
            ])
            ->whereNotNull('sent_for_payment_date')
            ->exists();
    }

    public function getCanBeMarkedAsReadyForShipmentFromManufacturerAttribute(): bool
    {
        return $this->production_is_finished
            && !is_null($this->packing_list_file);
    }

    public function getPackingListUrlAttribute(): string
    {
        return route('order-products.files', [
            'path' => self::PACKING_LIST_FOLDER_NAME . '/' . $this->packing_list_file,
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
            // // Detach 'productionInvoices'
            // $record->productionInvoices()->detach();
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
                ]);
            },

            'process' => function ($processQuery) {
                $processQuery->withRelationsForOrderProduct()
                    ->withOnlySelectsForOrderProduct();
            },

            // 'productionInvoices' => function ($invoicesQuery) {
            //     $invoicesQuery->with([
            //         'paymentType',
            //     ]);
            // },
        ]);
    }

    public function scopeWithBasicRelationCounts($query): Builder
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

    public function scopeOnlyProductionIsStarted($query)
    {
        return $query->whereHas('order', function ($orderQuery) {
            $orderQuery->onlyProductionIsStarted();
        });
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

            self::STATUS_PRODUCTION_IS_FINISHED_NAME =>
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
    public static function storeByPLDFromRequest(PLDOrderProductStoreRequest $request): void
    {
        $record = self::create($request->safe());

        // HasMany relations
        $record->storeCommentFromRequest($request);
    }

    /**
     * AJAX request by PLD
     */
    public function updateByPLDFromRequest(PLDOrderProductUpdateRequest $request): void
    {
        $this->update($request->safe());

        // HasMany relations
        $this->storeCommentFromRequest($request);
    }

    /**
     * AJAX request by CMD
     */
    public function updateByCMDFromRequest(CMDOrderProductUpdateRequest $request): void
    {
        $this->update($request->safe());

        // HasMany relations
        $this->storeCommentFromRequest($request);

        // Upload files
        $this->uploadFile('packing_list_file', self::getPackingListFolderPath());
        $this->uploadFile('coa_file', self::getCoaFileFolderPath());
        $this->uploadFile('coo_file', self::getCooFileFolderPath());
        $this->uploadFile('declaration_for_europe_file', self::getDeclarationForEuropeFileFolderPath());
    }

    /**
     * AJAX request by DD
     */
    function updateByDDFromRequest(DDOrderProductUpdateRequest $request): void
    {
        $this->fill($request->safe()->all());

        // Validate 'date_of_receiving_print_proof_from_manufacturer' attribute
        if (!$this->new_layout) {
            $this->date_of_receiving_print_proof_from_manufacturer = null;
        }

        $this->save();

        // HasMany relations
        $this->storeCommentFromRequest($request);
    }

    /**
     * AJAX request by MSD
     */
    function updateByMSDFromRequest(MSDOrderProductUpdateRequest $request): void
    {
        $this->update($request->safe());

        // HasMany relations
        $this->storeCommentFromRequest($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Storage paths
    |--------------------------------------------------------------------------
    */

    public static function getPackingListFolderPath(): string
    {
        return storage_path(self::STORAGE_FILES_PATH . '/' . self::PACKING_LIST_FOLDER_NAME);
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
    | Action availability
    |--------------------------------------------------------------------------
    */

    public function canAttachNewProductionInvoice(): bool
    {
        return $this->canAttachProductionInvoiceOFPrepaymentType()
            || $this->canAttachProductionInvoiceOfFinalPaymentType()
            || $this->canAttachProductionInvoiceOfFullPaymentType();
    }

    public function canAttachProductionInvoiceOFPrepaymentType(): bool
    {
        return $this->productionInvoices->count() == 0;
    }

    public function canAttachProductionInvoiceOfFinalPaymentType(): bool
    {
        return $this->productionInvoices
            ->where('payment_type_id', InvoicePaymentType::PREPAYMENT_ID)
            ->count() > 0

            && $this->productionInvoices
            ->where('payment_type_id', InvoicePaymentType::FINAL_PAYMENT_ID)
            ->count() == 0;
    }

    public function canAttachProductionInvoiceOfFullPaymentType(): bool
    {
        return $this->productionInvoices->count() == 0;
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
