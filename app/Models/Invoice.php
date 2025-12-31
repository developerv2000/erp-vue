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

    // PRD
    const DEFAULT_PRD_ORDER_BY = 'id';
    const DEFAULT_PRD_ORDER_DIRECTION = 'asc';
    const DEFAULT_PRD_PER_PAGE = 50;

    // PLD
    const DEFAULT_PLD_ORDER_BY = 'id';
    const DEFAULT_PLD_ORDER_DIRECTION = 'asc';
    const DEFAULT_PLD_PER_PAGE = 50;

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
    public function orderProducts()
    {
        return $this->belongsToMany(OrderProduct::class);
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
            'path' => self::PDF_FILES_FOLDER_NAME . '/' . $this->pdf,
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
            $record->orderProducts()->detach();
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
            'type',
            'paymentType',

            'order' => function ($orderQuery) {
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
        ]);
    }

    public function scopeWithBasicRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
            'orderProducts',
        ]);
    }

    public function scopeOnlySentForPayment($query)
    {
        return $query->whereNotNull('sent_for_payment_date');
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
    public static function queryPRDRecordsFromRequest(Request $request, string $action = 'paginate', bool $appendAttributes = false)
    {
        $query = self::withBasicRelations()
            ->withBasicRelationCounts();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest(
            $request,
            'DEFAULT_PRD_ORDER_BY',
            'DEFAULT_PRD_ORDER_DIRECTION',
            'DEFAULT_PRD_PER_PAGE'
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

        return $query;
    }

    private static function getFilterConfig(): array
    {
        return [
            'whereEqual' => ['payment_type_id', 'number'],
            'whereIn' => ['id', 'order_id'],
            'dateRange' => [
                'receive_date',
                'sent_for_payment_date',
                'created_at',
                'updated_at'
            ],

            'relationEqual' => [
                [
                    'inputName' => 'order_manufacturer_id',
                    'relationName' => 'invoiceable',
                    'relationAttribute' => 'orders.manufacturer_id',
                ],

                [
                    'inputName' => 'order_country_id',
                    'relationName' => 'invoiceable',
                    'relationAttribute' => 'orders.country_id',
                ],

                [
                    'inputName' => 'order_product_id',
                    'relationName' => 'orderProducts',
                    'relationAttribute' => 'order_products.id',
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
        $record = self::create([
            ...$request->all(),
            'type_id' => InvoiceType::PRODUCTION_TYPE_ID
        ]);

        // Attach all products of order, for invoice of PREPAYMENT type
        if ($record->payment_type_id == InvoicePaymentType::PREPAYMENT_ID) {
            $allProductIDs = $order->products()->pluck('id')->toArray();
            $record->orderProducts()->attach($allProductIDs);
        }

        // Attach only selected products for invoices of
        // 'FULL_PAYMENT' and 'FINAL_PAYMENT' types
        else {
            $selectedProducts = $request->input('order_products', []);
            $record->orderProducts()->attach($selectedProducts);
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

        // Sycn orderProducts
        $selectedOrderProducts = $request->input('order_products', []);
        $this->orderProducts()->sync($selectedOrderProducts);

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
    public function completeProductionTypePaymentByPRD(): void
    {
        if (!$this->payment_is_completed) {
            $this->payment_completed_date = now();
            $this->save();

            $notification = new ProductionTypeInvoicePaymentCompleted($this);
            User::notifyUsersBasedOnPermission($notification, 'receive-notification-when-invoice-payment-is-completed-by-PRD');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Table headers
    |--------------------------------------------------------------------------
    */

    public static function getPRDTableHeadersForUser($user): array|null
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
}
