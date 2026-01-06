<?php

namespace App\Models;

use App\Http\Requests\CMD\CMDInvoiceStoreProductionTypeRequest;
use App\Http\Requests\CMD\CMDInvoiceUpdateProductionTypeRequest;
use App\Http\Requests\PRD\PRDInvoiceUpdateProductionTypeRequest;
use App\Notifications\NewProductionTypeInvoiceForPaymentReceived;
use App\Notifications\ProductionTypeInvoicePaymentCompleted;
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

class Invoice extends Model implements HasTitleAttribute
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
    const STORAGE_FILES_PATH = 'app/private/invoices';
    const PDF_FILES_FOLDER_NAME = 'pdf-files';
    const PAYMENT_CONFIRMATION_DOCUMENTS_FOLDER_NAME = 'payment-confirmation-documents';

    // CMD
    const DEFAULT_CMD_ORDER_BY = 'id';
    const DEFAULT_CMD_ORDER_DIRECTION = 'asc';
    const DEFAULT_CMD_PER_PAGE = 50;

    // PLD
    const DEFAULT_PLD_ORDER_BY = 'id';
    const DEFAULT_PLD_ORDER_DIRECTION = 'asc';
    const DEFAULT_PLD_PER_PAGE = 50;

    // PRD
    const DEFAULT_PRD_PRODUCTION_TYPES_ORDER_BY = 'id';
    const DEFAULT_PRD_PRODUCTION_TYPES_ORDER_DIRECTION = 'asc';
    const DEFAULT_PRD_PRODUCTION_TYPES_PER_PAGE = 50;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    protected $guarded = ['id'];

    protected $casts = [
        'receive_date' => 'datetime',
        'sent_for_payment_date' => 'datetime',
        'accepted_by_financier_date' => 'datetime',
        'payment_request_date_by_financier' => 'datetime',
        'payment_date' => 'datetime',
        'payment_completed_date' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Get the parent invoiceable model.
     *
     * This defines a polymorphic relationship where a invoice can belong to any model.
     */
    public function invoiceable()
    {
        return $this->morphTo();
    }

    public function type()
    {
        return $this->belongsTo(InvoiceType::class, 'type_id');
    }

    public function paymentType()
    {
        return $this->belongsTo(InvoicePaymentType::class, 'payment_type_id');
    }

    /**
     * Only for invoices of 'Production' type
     */
    public function products()
    {
        return $this->belongsToMany(OrderProduct::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Additional attributes & appends
    |--------------------------------------------------------------------------
    */

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
            'pdf_file_url',
            'is_sent_for_payment',
            'is_accepted_by_financier',
        ]);

        $this->invoiceable->append([
            'title',
        ]);

        $this->products->each(fn($product) => $product->process->append('full_english_product_label'));
    }

    public static function appendRecordsBasicPRDProductionTypesAttributes($records): void
    {
        foreach ($records as $record) {
            $record->appendBasicPRDProductionTypesAttributes();
        }
    }

    public function appendBasicPRDProductionTypesAttributes(): void
    {
        $this->append([
            'base_model_class',
            'pdf_file_url',
            'is_sent_for_payment',
            'is_accepted_by_financier',
        ]);

        $this->invoiceable->append([
            'title',
        ]);

        $this->products->each(fn($product) => $product->process->append('full_english_product_label'));
    }

    public function getIsSentForPaymentAttribute(): bool
    {
        return !is_null($this->sent_for_payment_date);
    }

    public function getIsAcceptedByFinancierAttribute(): bool
    {
        return !is_null($this->accepted_by_financier_date);
    }

    public function getPaymentIsCompletedAttribute(): bool
    {
        return !is_null($this->payment_completed_date);
    }

    public function getPdfFileUrlAttribute(): string
    {
        return route('invoices.files', [
            'path' => self::PDF_FILES_FOLDER_NAME . '/' . $this->pdf_file,
        ]);
    }

    public function getPaymentConfirmationDocumentUrlAttribute(): string
    {
        return route('invoices.files', [
            'path' => self::PAYMENT_CONFIRMATION_DOCUMENTS_FOLDER_NAME . '/' . $this->payment_confirmation_document,
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
            $record->products()->detach();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWithBasicCMDRelations($query): Builder
    {
        return $query->with([
            'type',
            'paymentType',

            'invoiceable' => function ($orderQuery) {
                $orderQuery->with([ // ->withBasicRelations not used because of redundant relations
                    'country',

                    'manufacturer' => function ($manufacturersQuery) {
                        $manufacturersQuery->select(
                            'manufacturers.id',
                            'manufacturers.name',
                        );
                    },
                ]);
            },

            'products' => function ($productsQuery) {
                $productsQuery->with([
                    'process' => function ($processQuery) {
                        $processQuery->withRelationsForOrderProduct()
                            ->withOnlySelectsForOrderProduct();
                    },
                ]);
            }
        ]);
    }

    public function scopeWithBasicCMDRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
        ]);
    }

    public function scopeWithBasicPRDProductionTypesRelations($query): Builder
    {
        return $query->with([
            'type',
            'paymentType',

            'invoiceable' => function ($orderQuery) {
                $orderQuery->with([ // ->withBasicRelations not used because of redundant relations
                    'country',

                    'manufacturer' => function ($manufacturersQuery) {
                        $manufacturersQuery->select(
                            'manufacturers.id',
                            'manufacturers.name',
                        );
                    },
                ]);
            },

            'products' => function ($productsQuery) {
                $productsQuery->with([
                    'process' => function ($processQuery) {
                        $processQuery->withRelationsForOrderProduct()
                            ->withOnlySelectsForOrderProduct();
                    },
                ]);
            }
        ]);
    }

    public function scopeWithBasicPRDProductionTypesRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
        ]);
    }

    public function scopeOnlyProductionType($query)
    {
        return $query->where('type_id', InvoiceType::PRODUCTION_TYPE_ID);
    }

    public function scopeOnlyDeliveryToWarehouseType($query)
    {
        return $query->where('type_id', InvoiceType::DELIVERY_TO_WAREHOUSE_TYPE_ID);
    }

    public function scopeOnlyExportType($query)
    {
        return $query->where('type_id', InvoiceType::EXPORT_TYPE_ID);
    }

    public function scopeOnlySentForPayment($query)
    {
        return $query->whereNotNull('sent_for_payment_date');
    }

    /*
    |--------------------------------------------------------------------------
    | Contracts
    |--------------------------------------------------------------------------
    */

    // Implement method declared in HasTitleAttribute Interface
    public function getTitleAttribute(): string
    {
        return $this->paymentType->name . ' ' . ($this->number ?: ('#' . $this->id));
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
    public static function queryPRDProductionTypeRecordsFromRequest(Request $request, string $action = 'paginate', bool $appendAttributes = false)
    {
        $query = self::onlySentForPayment()
            ->withBasicPRDProductionTypesRelations()
            ->withBasicPRDProductionTypesRelationCounts();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest(
            $request,
            'DEFAULT_PRD_PRODUCTION_TYPES_ORDER_BY',
            'DEFAULT_PRD_PRODUCTION_TYPES_ORDER_DIRECTION',
            'DEFAULT_PRD_PRODUCTION_TYPES_PER_PAGE'
        );

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Finalize (sorting & pagination)
        $records = ModelHelper::finalizeQueryForRequest($query, $request, $action);

        // Append attributes unless raw query is requested
        if ($appendAttributes && $action !== 'query') {
            self::appendRecordsBasicPRDProductionTypesAttributes($records);
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
            'whereEqual' => ['order_id'],
            'whereIn' => ['id', 'payment_type_id', 'number'],
            'dateRange' => [
                'receive_date',
                'sent_for_payment_date',
                'created_at',
                'updated_at'
            ],

            'relationEqual' => [
                [
                    'inputName' => 'product_id',
                    'relationName' => 'products',
                    'relationAttribute' => 'order_products.id',
                ],
            ],

            'relationIn' => [
                [
                    'inputName' => 'order_manufacturer_id',
                    'relationName' => 'invoiceable',
                    'relationAttribute' => 'orders.manufacturer_id',
                ],

                [
                    'inputName' => 'order_name',
                    'relationName' => 'invoiceable',
                    'relationAttribute' => 'orders.name',
                ],

                [
                    'inputName' => 'order_country_id',
                    'relationName' => 'invoiceable',
                    'relationAttribute' => 'orders.country_id',
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Store & update
    |--------------------------------------------------------------------------
    */

    /**
     * AJAX request by CMD
     */
    public static function storeProductionTypeByCMDFromRequest(CMDInvoiceStoreProductionTypeRequest $request)
    {
        $order = Order::findorfail($request->input('order_id'));

        // Create invoice
        $record = $order->productionInvoices()->create([
            ...$request->all(),
            'type_id' => InvoiceType::PRODUCTION_TYPE_ID,
        ]);

        // Attach all products of order, for invoice of PREPAYMENT type
        if ($record->payment_type_id == InvoicePaymentType::PREPAYMENT_ID) {
            $allProductIDs = $order->products()->pluck('id')->toArray();
            $record->products()->attach($allProductIDs);
        }

        // Attach only selected products for invoices of
        // 'FULL_PAYMENT' and 'FINAL_PAYMENT' types
        else {
            $selectedProducts = $request->input('products', []);
            $record->products()->attach($selectedProducts);
        }

        // Upload PDF file
        $record->uploadFile('pdf_file', self::getPdfFilesFolderPath());
    }

    /**
     * AJAX request by CMD
     */
    public function updateProductionTypeByCMDFromRequest(CMDInvoiceUpdateProductionTypeRequest $request)
    {
        $this->update($request->all());

        // Sycn products for non-prepayment invoices
        if ($this->payment_type_id != InvoicePaymentType::PREPAYMENT_ID) {
            $selectedProducts = $request->input('products', []);
            $this->products()->sync($selectedProducts);
        }

        // Upload PDF file
        $this->uploadFile('pdf_file', self::getPdfFilesFolderPath());
    }

    /**
     * AJAX request by PRD
     */
    public function updateProductionTypeByPRDFromRequest(PRDInvoiceUpdateProductionTypeRequest $request)
    {
        $this->update($request->all());

        // Validate 'accepted_by_financier_date' attribute
        if (!$this->is_accepted_by_financier) {
            $this->accepted_by_financier_date = now();
            $this->save();
        }

        // Upload SWIFT file
        $this->uploadFile('payment_confirmation_document', self::getPaymentConfirmationDocumentsFolderPath());
    }

    /*
    |--------------------------------------------------------------------------
    | Storage paths
    |--------------------------------------------------------------------------
    */

    public static function getPdfFilesFolderPath(): string
    {
        return storage_path(self::STORAGE_FILES_PATH . '/' . self::PDF_FILES_FOLDER_NAME);
    }

    public static function getPaymentConfirmationDocumentsFolderPath(): string
    {
        return storage_path(self::STORAGE_FILES_PATH . '/' . self::PDF_FILES_FOLDER_NAME);
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    /**
     * AJAX request
     *
     * Send 'Production' type to PRD by CMD
     */
    public function sendProductionTypeForPaymentByCMD(): void
    {
        if (!$this->is_sent_for_payment) {
            $this->sent_for_payment_date = now();
            $this->save();

            $notification = new NewProductionTypeInvoiceForPaymentReceived($this);
            User::notifyUsersBasedOnPermission($notification, 'receive-notification-when-invoice-is-sent-for-payment-by-CMD');
        }
    }

    /**
     * AJAX request
     *
     * Accept invoices by PRD
     */
    public function acceptByPRD(): void
    {
        if (!$this->is_accepted_by_financier) {
            $this->accepted_by_financier_date = now();
            $this->save();
        }
    }

    /**
     * AJAX request
     *
     * Complete payment by PRD
     */
    public function completePaymentByPRD(): void
    {
        if (!$this->payment_is_completed) {
            $this->payment_completed_date = now();
            $this->save();

            switch ($this->type_id) {
                case InvoiceType::PRODUCTION_TYPE_ID:
                    $notificationClass = ProductionTypeInvoicePaymentCompleted::class;
                    $permissionName = 'receive-notification-when-invoice-payment-is-completed-by-PRD';
                    break;
            }

            $notification = new $notificationClass($this);
            User::notifyUsersBasedOnPermission($notification, $permissionName);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Table headers
    |--------------------------------------------------------------------------
    */

    public static function getCMDTableHeadersForUser($user): array|null
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_CMD_INVOICES_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_CMD_INVOICES_NAME))) {
            array_push(
                $columns,
                ['title' => 'Record', 'key' => 'edit', 'width' => 60, 'sortable' => false, 'visible' => 1, 'order' => $order++],
            );
        }

        $additionalColumns = [
            ['title' => 'ID', 'key' => 'id', 'width' => 62, 'sortable' => true],
            ['title' => 'dates.Receive', 'key' => 'receive_date', 'width' => 142, 'sortable' => true],
            ['title' => 'fields.Payment type', 'key' => 'payment_type_id', 'width' => 112, 'sortable' => true],
            ['title' => 'dates.Sent for payment', 'key' => 'sent_for_payment_date', 'width' => 200, 'sortable' => true],
            ['title' => 'dates.Accepted', 'key' => 'accepted_by_financier_date', 'width' => 132, 'sortable' => true],
            ['title' => 'fields.Pdf', 'key' => 'pdf_file', 'width' => 144, 'sortable' => false],

            ['title' => 'Order', 'key' => 'order_title', 'width' => 128, 'sortable' => true],
            ['title' => 'fields.Manufacturer', 'key' => 'order_manufacturer_name', 'width' => 140, 'sortable' => false],
            ['title' => 'Products', 'key' => 'products', 'width' => 180, 'sortable' => false],
            ['title' => 'fields.Country', 'key' => 'order_country_code', 'width' => 64, 'sortable' => false],

            ['title' => 'dates.Payment request', 'key' => 'payment_request_date_by_financier', 'width' => 180, 'sortable' => true],
            ['title' => 'dates.Payment', 'key' => 'payment_date', 'width' => 124, 'sortable' => true],
            ['title' => 'dates.Payment completed', 'key' => 'payment_completed_date', 'width' => 204, 'sortable' => true],
            ['title' => 'fields.Invoie №', 'key' => 'number', 'width' => 120, 'sortable' => true],
            ['title' => 'fields.Swift', 'key' => 'payment_confirmation_document', 'width' => 144, 'sortable' => false],

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

    public static function getPRDProductionTypesTableHeadersForUser($user): array|null
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_PRD_INVOICES_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_PRD_INVOICES_NAME))) {
            array_push(
                $columns,
                ['title' => 'Record', 'key' => 'edit', 'width' => 60, 'sortable' => false, 'visible' => 1, 'order' => $order++],
            );
        }

        $additionalColumns = [
            ['title' => 'ID', 'key' => 'id', 'width' => 62, 'sortable' => true],
            ['title' => 'dates.Receive', 'key' => 'receive_date', 'width' => 142, 'sortable' => true],
            ['title' => 'fields.Payment type', 'key' => 'payment_type_id', 'width' => 112, 'sortable' => true],
            ['title' => 'dates.Sent for payment', 'key' => 'sent_for_payment_date', 'width' => 200, 'sortable' => true],
            ['title' => 'dates.Accepted', 'key' => 'accepted_by_financier_date', 'width' => 132, 'sortable' => true],
            ['title' => 'fields.Pdf', 'key' => 'pdf_file', 'width' => 144, 'sortable' => false],

            ['title' => 'Order', 'key' => 'order_title', 'width' => 128, 'sortable' => true],
            ['title' => 'fields.Manufacturer', 'key' => 'order_manufacturer_name', 'width' => 140, 'sortable' => false],
            ['title' => 'Products', 'key' => 'products', 'width' => 180, 'sortable' => false],
            ['title' => 'fields.Country', 'key' => 'order_country_code', 'width' => 64, 'sortable' => false],

            ['title' => 'dates.Payment request', 'key' => 'payment_request_date_by_financier', 'width' => 180, 'sortable' => true],
            ['title' => 'dates.Payment', 'key' => 'payment_date', 'width' => 124, 'sortable' => true],
            ['title' => 'dates.Payment completed', 'key' => 'payment_completed_date', 'width' => 204, 'sortable' => true],
            ['title' => 'fields.Invoie №', 'key' => 'number', 'width' => 120, 'sortable' => true],
            ['title' => 'fields.Swift', 'key' => 'payment_confirmation_document', 'width' => 144, 'sortable' => false],

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
