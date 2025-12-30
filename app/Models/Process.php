<?php

namespace App\Models;

use App\Http\Requests\MAD\ProcessDuplicateRequest;
use App\Http\Requests\MAD\ProcessStoreRequest;
use App\Http\Requests\MAD\ProcessUpdateRequest;
use App\Notifications\ProcessStageChangedToContract;
use App\Support\Contracts\Model\ExportsProductSelection;
use App\Support\Contracts\Model\ExportsRecordsAsExcel;
use App\Support\Contracts\Model\GeneratesBreadcrumbs;
use App\Support\Contracts\Model\HasTitleAttribute;
use App\Support\Contracts\Model\PreparesFetchedRecordsForExport;
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
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class Process extends Model implements
    HasTitleAttribute,
    GeneratesBreadcrumbs,
    ExportsRecordsAsExcel,
    PreparesFetchedRecordsForExport,
    ExportsProductSelection
{
    /** @use HasFactory<\Database\Factories\ProcessFactory> */
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

    // MAD
    const DEFAULT_ORDER_BY = 'updated_at';
    const DEFAULT_ORDER_DIRECTION = 'desc';
    const DEFAULT_PER_PAGE = 50;

    const LIMITED_RECORDS_COUNT_ON_EXPORT_TO_EXCEL = 15;
    const STORAGE_PATH_OF_EXCEL_TEMPLATE_FILE_FOR_EXPORT = 'app/private/excel/export-templates/vps.xlsx';
    const STORAGE_PATH_FOR_EXPORTING_EXCEL_FILES = 'app/private/excel/exports/vps';

    const DEADLINE_EXPIRED_STATUS_NAME = 'Expired';
    const DEADLINE_NOT_EXPIRED_STATUS_NAME = 'Not expired';
    const DEADLINE_STOPPED_STATUS_NAME = 'Stopped';
    const NO_DEADLINE_STATUS_NAME = 'No deadline';

    // PLD
    const DEFAULT_READY_FOR_ORDER_ORDER_BY = 'readiness_for_order_date';
    const DEFAULT_READY_FOR_ORDER_ORDER_TYPE = 'desc';
    const DEFAULT_READY_FOR_ORDER_PAGINATION_LIMIT = 50;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'manufacturer_first_offered_price' => 'float',
            'manufacturer_followed_offered_price' => 'float',
            'our_first_offered_price' => 'float',
            'our_followed_offered_price' => 'float',
            'agreed_price' => 'float',
            'increased_price' => 'float',

            'forecast_year_1_update_date' => 'date',
            'increased_price_date' => 'date',
            'responsible_person_update_date' => 'date',
            'readiness_for_order_date' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    // Slow relation, use only when necessary.
    // On eager loading use $processes->product->manufacturer || with('product.manufacturer')
    // On filtering use whereHas('manufacturer', ...)
    public function manufacturer()
    {
        return $this->hasOneThrough(
            Manufacturer::class,
            Product::class,
            'id', // Foreign key on the Products table
            'id', // Foreign key on the Manufacturers table
            'product_id', // Local key on the Processes table
            'manufacturer_id' // Local key on the Products table
        )->withTrashedParents()->withTrashed();
    }

    public function status()
    {
        return $this->belongsTo(ProcessStatus::class, 'status_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(ProcessStatusHistory::class)->orderBy('start_date', 'asc');
    }

    public function activeStatusHistory()
    {
        return $this->hasOne(ProcessStatusHistory::class)
            ->whereNull('end_date');
    }

    public function searchCountry()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function mah()
    {
        return $this->belongsTo(MarketingAuthorizationHolder::class, 'marketing_authorization_holder_id');
    }

    public function responsiblePerson()
    {
        return $this->belongsTo(ProcessResponsiblePerson::class, 'responsible_person_id');
    }

    public function clinicalTrialCountries()
    {
        return $this->belongsToMany(Country::class, 'clinical_trial_country_process');
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
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
            'deadline_status',
            'is_ready_for_asp_contract',
            'is_ready_for_asp_registration',
            'can_be_marked_as_ready_for_order',
            'is_ready_for_order',
            'manufacturer_offered_price_in_usd',
            'increased_price_percentage',
            'days_past',
        ]);
    }

    public static function appendRecordsBasicOrderAttributes($records): void
    {
        foreach ($records as $record) {
            $record->appendBasicOrderAttributes();
        }
    }

    public function appendBasicOrderAttributes(): void
    {
        $this->append([]);
    }

    public function getDeadlineStatusAttribute()
    {
        if ($this->days_past_since_last_activity == -1) {
            return self::DEADLINE_STOPPED_STATUS_NAME;
        } else if (!$this->status->hasDeadline()) {
            return self::NO_DEADLINE_STATUS_NAME;
        } else if ($this->days_past_since_last_activity <= ProcessStatus::MAX_PROCESS_ACTIVITY_DELAY_DAYS) {
            return self::DEADLINE_NOT_EXPIRED_STATUS_NAME;
        } else {
            return self::DEADLINE_EXPIRED_STATUS_NAME;
        }
    }

    /**
     * Also used while exporting product selection.
     */
    public function getMatchedProductSearchesAttribute(): Collection
    {
        return ProductSearch::where([
            'inn_id' => $this->product->inn_id,
            'form_id' => $this->product->form_id,
            'dosage' => $this->product->dosage,
            'country_id' => $this->country_id,
        ])
            ->select('id', 'country_id', 'status_id')
            ->get();
    }

    /**
     * Get the number of days past since the 'responsible_person_update_date'.
     *
     * @return int|null Number of days past since the update date, or null if the date is not set.
     */
    public function getDaysPastAttribute()
    {
        if ($this->responsible_person_update_date) {
            return round($this->responsible_person_update_date->diffInDays(now(), true));
        }

        return null;
    }

    /**
     * Get the manufacturer's offered price in USD.
     *
     * This accessor calculates the manufacturer's offered price in USD by
     * considering the followed offered price if available; otherwise,
     * it defaults to the first offered price. If a price exists, it
     * converts the value to USD using the associated currency.
     *
     * @return float|null The manufacturer's offered price in USD, or null if no price is available.
     */
    public function getManufacturerOfferedPriceInUsdAttribute()
    {
        // Determine the manufacturer's final offered price:
        // Use the followed price if it exists; otherwise, use the first price.
        $finalPrice = $this->manufacturer_followed_offered_price
            ?: $this->manufacturer_first_offered_price;

        return $finalPrice
            ? round(Currency::convertPriceToUSD($finalPrice, $this->currency), 2)
            : null;
    }

    /**
     * Get the percentage increase of the price.
     *
     * The calculation compares the increased price to the original/base price
     * (using followed_offered_price if available, otherwise our_first_offered_price).
     *
     * @return float|null
     */
    public function getIncreasedPricePercentageAttribute()
    {
        // Final increased price
        $increasedPrice = $this->increased_price;

        // Choose the correct base price: priority -> followed → first offered
        $basePrice = $this->followed_offered_price ?: $this->our_first_offered_price;

        // Validation: cannot calculate without both prices or if base is zero
        if (!$increasedPrice || !$basePrice || $basePrice == 0) {
            return null;
        }

        // Calculate how much the new price is in % of the base price
        // Example: if increased price = 120 and base = 100 → 120% of base
        $percentOfBase = GeneralHelper::calculatePercentageOfTotal($basePrice, $increasedPrice);

        // Convert to “increase percentage” (e.g. 120% → 20%)
        $increasePercentage = $percentOfBase - 100;

        // Truncate to 2 decimals (NO rounding)
        return floor($increasePercentage * 100) / 100;
    }


    /**
     * CHeck whether process can be added to ASP (СПГ) as contracted
     */
    public function getIsReadyForAspContractAttribute()
    {
        return $this->status->generalStatus->stage >= 5;
    }

    /**
     * CHeck whether process can be added to ASP (СПГ) as registered
     */
    public function getIsReadyForAspRegistrationAttribute()
    {
        return $this->status->generalStatus->stage >= 7;
    }

    public function getCanBeMarkedAsReadyForOrderAttribute(): bool
    {
        return $this->status->generalStatus->stage >= 8;
    }

    public function getIsReadyForOrderAttribute(): bool
    {
        return $this->readiness_for_order_date ? true : false;
    }

    /**
     * Check wether current status of process can be edited by authenticated user or not.
     *
     * Used in processes.edit page.
     */
    public function getCurrentStatusCanBeEditedForAuthUserAttribute(): bool
    {
        return Gate::allows('upgrade-MAD-VPS-status-after-contract-stage')
            || !$this->status->generalStatus->requires_permission;
    }

    /**
     * Used on order pages.
     */
    public function getFullEnglishProductLabelAttribute()
    {
        return collect([
            $this->trademark_en,
            $this->product->form->name,
            $this->product->dosage,
            $this->product->pack,
        ])
            ->filter()
            ->implode(' ');
    }

    /**
     * Used on order pages.
     */
    public function getFullEnglishProductLabelWithIdAttribute()
    {
        return collect([
            $this->trademark_en,
            $this->product->form->name,
            $this->product->dosage,
            $this->product->pack,
            ' — #' . $this->id,
        ])
            ->filter()
            ->implode(' ');
    }

    /**
     * Used on order pages.
     */
    public function getFullRussianProductLabelAttribute()
    {
        return collect([
            $this->trademark_ru,
            $this->product->form->name,
            $this->product->dosage,
            $this->product->pack,
        ])
            ->filter()
            ->implode(' ');
    }

    /**
     * Used on order pages.
     */
    public function getMahNameWithIdAttribute()
    {
        return $this->mah->name . ' — #' . $this->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($record) {
            $record->responsible_person_update_date = now();
            $record->created_at = $record->created_at ?: now();
        });

        static::created(function ($record) {
            $record->addStatusHistoryForCurrentStatus($record->created_at);
            $record->recalculateDaysPastSinceLastActivity(true); // Must be executed only AFTER creating status_history!
        });

        static::updating(function ($record) {
            $record->updateStatusHistory();
            $record->notifyUsersOnContractStage();
            $record->handleResponsiblePersonUpdateDate();
        });

        static::updated(function ($record) {
            // Recalculate 'days_past_since_last_activity' after updating event, because records status maybe be updated.
            $record->recalculateDaysPastSinceLastActivity(true);
        });

        static::saving(function ($record) {
            $record->trademark_en = mb_strtoupper($record->trademark_en ?: '');
            $record->trademark_ru = mb_strtoupper($record->trademark_ru ?: '');

            $record->handleForecastUpdateDate();
            $record->handleIncreasedPriceDate();
        });

        static::restoring(function ($record) {
            if ($record->product->trashed()) {
                $record->product->restore();
            }
        });

        static::forceDeleting(function ($record) {
            $record->clinicalTrialCountries()->detach();

            foreach ($record->statusHistory as $history) {
                $history->delete();
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
            'searchCountry',
            'currency',
            'mah',
            'clinicalTrialCountries',
            'responsiblePerson',
            'lastComment',

            'statusHistory' => function ($historyQuery) {
                $historyQuery->with(['status']);
            },

            'status' => function ($statusQuery) {
                $statusQuery->with('generalStatus');
            },

            'product' => function ($productQuery) {
                $productQuery->select(
                    'id',
                    'manufacturer_id',
                    'inn_id',
                    'atx_id',
                    'class_id',
                    'form_id',
                    'dosage',
                    'pack',
                    'moq',
                    'shelf_life_id'
                )
                    ->with([
                        'inn',
                        'atx',
                        'shelfLife',
                        'class',
                        'form',
                        'zones',

                        'manufacturer' => function ($manufQuery) {
                            $manufQuery->select(
                                'id',
                                'name',
                                'category_id',
                                'country_id',
                                'bdm_user_id',
                                'analyst_user_id'
                            )
                                ->with([
                                    'category',
                                    'country',
                                    'analyst:id,name,photo',
                                    'bdm:id,name,photo',
                                ]);
                        },
                    ]);
            },
        ]);
    }

    public function scopeWithBasicRelationCounts($query): Builder
    {
        return $query->withCount([
            'comments',
        ]);
    }

    /**
     * Add 'product_manufacturer_name' attribute.
     *
     * Used when ordering processes by 'product_manufacturer_name'
     */
    public function scopeWithProductsManufacturerNameAttribute($query): Builder
    {
        return $query
            ->join('products', 'products.id', '=', 'processes.product_id')
            ->join('manufacturers', 'manufacturers.id', '=', 'products.manufacturer_id')
            ->selectRaw('manufacturers.name as product_manufacturer_name');
    }

    /**
     * Add 'product_inn_name' attribute.
     *
     * Used when ordering processes by 'product_inn_name'
     */
    public function scopeWithProductsInnNameAttribute($query): Builder
    {
        return $query
            ->join('products', 'products.id', '=', 'processes.product_id')
            ->join('inns', 'inns.id', '=', 'products.inn_id')
            ->selectRaw('inns.name as product_inn_name');
    }

    /**
     * Add 'product_form_name' attribute.
     *
     * Used when ordering processes by 'product_form_name'
     */
    public function scopeWithProductsFormNameAttribute($query): Builder
    {
        return $query
            ->join('products', 'products.id', '=', 'processes.product_id')
            ->join('product_forms', 'product_forms.id', '=', 'products.form_id')
            ->selectRaw('product_forms.name as product_form_name');
    }

    /**
     * Add 'product_dosage' attribute.
     *
     * Used when ordering processes by 'product_dosage'
     */
    public function scopeWithProductsDosageAttribute($query): Builder
    {
        return $query
            ->join('products', 'products.id', '=', 'processes.product_id')
            ->selectRaw('products.dosage as product_dosage');
    }

    /**
     * Scope the query to include basic relation for process status history pages.
     */
    public function scopeWithRelationsForHistoryPage($query): Builder
    {
        return $query->with([
            'statusHistory' => function ($historyQuery) {
                $historyQuery->with([
                    'status' => function ($statusQuery) {
                        $statusQuery->with('generalStatus');
                    },
                ]);
            },

            'product' => function ($productQuery) {
                $productQuery->select(
                    'id',
                    'manufacturer_id',
                    'inn_id',
                    'form_id',
                    'dosage',
                    'pack',
                )
                    ->with([
                        'inn',
                        'form',

                        'manufacturer' => function ($manufQuery) {
                            $manufQuery->select(
                                'id',
                                'name',
                                'country_id',
                                'bdm_user_id',
                                'analyst_user_id'
                            )
                                ->with([
                                    'country',
                                    'analyst:id,name,photo',
                                    'bdm:id,name,photo',
                                ]);
                        },
                    ]);
            },
        ]);
    }

    public function scopeOnlyReadyForOrder($query): Builder
    {
        return $query->whereNotNull('readiness_for_order_date');
    }

    public function scopeWithRelationsForOrder($query): Builder
    {
        return $query->with([
            'searchCountry',
            'mah',

            'product' => function ($productQuery) {
                $productQuery->select(
                    'products.id',
                    'products.manufacturer_id',
                    'inn_id',
                    'form_id',
                    'dosage',
                    'pack',
                )
                    ->with([
                        'inn',
                        'form',

                        'manufacturer' => function ($manufQuery) {
                            $manufQuery->select(
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
        ]);
    }

    public function scopeWithOnlySelectsForOrder($query): Builder
    {
        return $query->select(
            'processes.id',
            'readiness_for_order_date',
            'trademark_en',
            'trademark_ru',
            'processes.product_id',
            'processes.country_id',
            'processes.marketing_authorization_holder_id',
        );
    }

    public function scopeWithRelationCountsForOrder($query): Builder
    {
        return $query->withCount([
            // 'orderProducts',
        ]);
    }

    public function scopeWithRelationsForOrderProduct($query)
    {
        return $query->with([
            'searchCountry',
            'mah',

            'product' => function ($productQuery) {
                $productQuery->select(
                    'products.id',
                    'inn_id',
                    'form_id',
                    'dosage',
                    'pack',
                )
                    ->with([
                        'inn',
                        'form',
                    ]);
            },
        ]);
    }

    public function scopeWithOnlySelectsForOrderProduct($query)
    {
        return $query->select(
            'processes.id',
            'trademark_en',
            'trademark_ru',
            'processes.product_id',
            'processes.country_id',
            'processes.marketing_authorization_holder_id',
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Contracts
    |--------------------------------------------------------------------------
    */

    // Implement method declared in HasTitleAttribute Interface
    public function getTitleAttribute(): string
    {
        return __('Process') . ' #' . $this->id . ' — ' . $this->searchCountry->code;
    }

    // Implement method declared in GeneratesBreadcrumbs Interface
    public function generateBreadcrumbs($department = null): array
    {
        $lowercasedDepartment = strtolower($department);

        // Index page
        $breadcrumbs = [
            ['title' => __('pages.VPS'), 'link' => route($lowercasedDepartment . '.processes.index')],
        ];

        // Trash page
        if ($this->trashed()) {
            $breadcrumbs[] = ['title' => __('pages.Trash'), 'link' => route($lowercasedDepartment . '.processes.trash')];
        }

        // Edit page
        $breadcrumbs[] = ['title' => $this->title, 'link' => route($lowercasedDepartment . '.processes.edit', $this->id)];

        return $breadcrumbs;
    }

    /**
     * Implement method declared in ExportsRecordsAsExcel Interface
     *
     * Removes 'ordering by table joins' for performance.
     */
    public static function queryRecordsForExportFromRequest(Request $request): Builder
    {
        $query = self::withBasicRelations()
            ->withBasicRelationCounts()
            ->with('comments'); // Important

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest($request);

        // Remove 'ordering by table joins' for perfomance
        self::removeOrderingByTableJoinsFromRequest($request);

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Merge 'order_by_days_past_since_last_activity' into request if missing
        self::ensureOrderByActivityFlag($request);

        // Add ordering by 'days_past_since_last_activity' before
        // default ordering via ModelHelper::finalizeQueryForRequest
        self::orderQueryByActivityIfRequested($query, $request);

        // Finalize (sorting)
        ModelHelper::finalizeQueryForRequest($query, $request, 'query');

        return $query;
    }

    // Implement method declared in PreparesFetchedRecordsForExport Interface
    public static function prepareFetchedRecordsForExport($records): void
    {
        self::addGeneralStatusPeriodsForRecords($records);
    }

    // Implement method declared in ExportsRecordsAsExcel Interface
    public function getExcelColumnValuesForExport(): array
    {
        return [
            $this->id,
            $this->statusHistory->last()->start_date,
            $this->deadline_status,
            $this->searchCountry->code,
            $this->status->name,
            $this->status->generalStatus->name_for_analysts,
            $this->status->generalStatus->name,
            $this->product->manufacturer->category->name,
            $this->product->manufacturer->name,
            $this->product->manufacturer->country->name,
            $this->product->manufacturer->bdm->name,
            $this->product->manufacturer->analyst->name,
            $this->product->inn->name,
            $this->product->form->name,
            $this->product->dosage,
            $this->product->pack,
            $this->product->atx?->name,
            $this->product->atx?->short_name,
            $this->mah?->name,
            $this->comments->pluck('plain_text')->implode(' / '),
            $this->lastComment?->created_at,
            $this->manufacturer_first_offered_price,
            $this->manufacturer_followed_offered_price,
            $this->currency?->name,
            $this->manufacturer_offered_price_in_usd,
            $this->agreed_price,
            $this->our_followed_offered_price,
            $this->our_first_offered_price,
            $this->increased_price,
            $this->increased_price_percentage,
            $this->increased_price_date,
            $this->product->shelfLife->name,
            $this->product->moq,
            $this->dossier_status,
            $this->clinical_trial_year,
            $this->clinicalTrialCountries->pluck('name')->implode(' '),
            $this->clinical_trial_ich_country,
            $this->product->zones->pluck('name')->implode(' '),
            $this->down_payment_1,
            $this->down_payment_2,
            $this->down_payment_condition,
            $this->forecast_year_1_update_date,
            $this->forecast_year_1,
            $this->forecast_year_2,
            $this->forecast_year_3,
            $this->responsiblePerson->name,
            $this->responsible_people_update_date,
            $this->days_past,
            $this->trademark_en,
            $this->trademark_ru,
            $this->created_at,
            $this->updated_at,
            $this->product->class->name,
            $this->general_statuses_with_periods[0]->start_date,
            $this->general_statuses_with_periods[1]->start_date,
            $this->general_statuses_with_periods[2]->start_date,
            $this->general_statuses_with_periods[3]->start_date,
            $this->general_statuses_with_periods[4]->start_date,
            $this->general_statuses_with_periods[5]->start_date,
            $this->general_statuses_with_periods[6]->start_date,
            $this->general_statuses_with_periods[7]->start_date,
            $this->general_statuses_with_periods[8]->start_date,
            $this->general_statuses_with_periods[9]->start_date,
        ];
    }

    // Implement method declared in ExportsProductSelection Interface
    public function scopeWithRelationsForProductSelection($query)
    {
        return $query->with([
            'product' => function ($productQuery) {
                $productQuery->withRelationsForProductSelection();
            },
            'status',
            'currency',
            'searchCountry',
        ])
            ->select(
                'processes.id',
                'product_id',
                'country_id',
                'status_id',
                'currency_id',
                'agreed_price',
                'manufacturer_first_offered_price',
                'our_first_offered_price',
                'forecast_year_1',
                'forecast_year_2',
                'forecast_year_3',
            );
    }

    // Implement method declared in ExportsProductSelection Interface
    public static function queryRecordsForProductSelection(Request $request): Builder
    {
        $query = self::withRelationsForProductSelection();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest($request);

        // Remove 'ordering by table joins' for perfomance
        self::removeOrderingByTableJoinsFromRequest($request);

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Merge 'order_by_days_past_since_last_activity' into request if missing
        self::ensureOrderByActivityFlag($request);

        // Add ordering by 'days_past_since_last_activity' before
        // default ordering via ModelHelper::finalizeQueryForRequest
        self::orderQueryByActivityIfRequested($query, $request);

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

        // Add table joins for query ordering
        self::addTableJoinsForQueryOrdering($query, $request);

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Merge 'order_by_days_past_since_last_activity' into request if missing
        self::ensureOrderByActivityFlag($request);

        // Add ordering by 'days_past_since_last_activity' before
        // default ordering via ModelHelper::finalizeQueryForRequest
        self::orderQueryByActivityIfRequested($query, $request);

        // Finalize (sorting & pagination)
        $records = ModelHelper::finalizeQueryForRequest($query, $request, $action);

        // Append attributes unless raw query is requested
        if ($appendAttributes && $action !== 'query') {
            self::appendRecordsBasicAttributes($records);

            // Append 'general_statuses_with_periods'
            self::addGeneralStatusPeriodsForRecords($records);
        }

        return $records;
    }

    /**
     * Build and execute a model query for processes ready for order,
     * based on request parameters.
     *
     * Steps:
     *  - Apply default relations & counts for order
     *  - Normalize query params (pagination, sorting, etc.)
     *  - Apply filters (without permissions filter)
     *  - Finalize query with sorting & pagination
     *
     * @param $action  ('paginate', 'get' or 'query')
     * @return mixed
     */
    public static function queryReadyForOrderRecordsFromRequest(Request $request, string $action = 'paginate')
    {
        $query = self::onlyReadyForOrder()
            ->withRelationsForOrder()
            ->withOnlySelectsForOrder()
            ->withRelationCountsForOrder();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest(
            $request,
            'DEFAULT_READY_FOR_ORDER_ORDER_BY',
            'DEFAULT_READY_FOR_ORDER_ORDER_TYPE',
            'DEFAULT_READY_FOR_ORDER_PAGINATION_LIMIT'
        );

        // Apply filters
        self::filterQueryForRequest($query, $request, applyPermissionsFilter: false);

        // Finalize (sorting & pagination)
        $records = ModelHelper::finalizeQueryForRequest($query, $request, $action);

        return $records;
    }

    /*
    |--------------------------------------------------------------------------
    | Filtering
    |--------------------------------------------------------------------------
    */

    public static function filterQueryForRequest($query, $request, $applyPermissionsFilter = true): Builder
    {
        // Apply base filters using helper
        $query = QueryFilterHelper::applyFilters($query, $request, self::getFilterConfig());

        // Additional filters
        if ($applyPermissionsFilter) {
            self::applyPermissionsFilter($query);
        }

        self::applyManufacturerRegionFilter($query, $request);
        self::applyActiveStatusStartDateRangeFilter($query, $request);
        self::applyDeadlineStatusFilter($query, $request);
        self::applyContractedOnSpecificMonthFilter($query, $request); // if redirected from KPI/ASP pages
        self::applyRegisteredOnSpecificMonthFilter($query, $request); // if redirected from KPI/ASP pages
        self::applyGeneralStatusHistoryFilter($query, $request); // if redirected from KPI page

        return $query;
    }

    /**
     * Filter the query based on user permissions and responsibilities.
     *
     * This static method applies filtering to the provided query based on the user's permissions,
     * their responsible countries, and whether they are the assigned analyst for a manufacturer.
     * If the user does not have the "view-MAD-VPS-of-all-analysts" permission, restrictions are applied.
     */
    public static function applyPermissionsFilter($query): Builder
    {
        // Get the authenticated user
        $user = auth()->user();

        // Retrieve the list of country IDs the user is responsible for
        $responsibleCountryIDs = $user->responsibleCountries->pluck('id');

        // Apply filters only if the user is restricted from viewing all analysts' MAD VPS records
        if (Gate::denies('view-MAD-VPS-of-all-analysts')) {
            $query->where(function ($subquery) use ($user, $responsibleCountryIDs) {
                $subquery
                    // Filter by countries the user is responsible for
                    ->whereIn('country_id', $responsibleCountryIDs)
                    // Or filter by manufacturers where the user is the assigned analyst
                    ->orWhereHas('manufacturer', function ($manufacturersQuery) use ($user) {
                        $manufacturersQuery->where('analyst_user_id', $user->id);
                    });
            });
        }

        return $query;
    }

    /**
     * Apply filters to the query based on the manufacturer's country.
     *
     * 'manufacturer_region' input is used for this filter.
     *
     * This function filters the processes based on the manufacturer’s country,
     * delegating the filtering logic to the Manufacturer model.
     */
    public static function applyManufacturerRegionFilter($query, $request): Builder
    {
        $query->whereHas('manufacturer', function ($manufacturersQuery) use ($request) {
            return Manufacturer::applyRegionFilter($manufacturersQuery, $request);
        });

        return $query;
    }

    /**
     * Apply date range filtering based on the active status start date.
     */
    public static function applyActiveStatusStartDateRangeFilter($query, $request): Builder
    {
        $filterConfig = [
            'relationDateRange' => [
                [
                    'inputName' => 'active_status_start_date_range',
                    'relationName' => 'activeStatusHistory',
                    'relationAttribute' => 'process_status_histories.start_date',
                ]
            ],
        ];

        return QueryFilterHelper::applyFilters($query, $request, $filterConfig);
    }

    /**
     * Filter only processes which have been contracted,
     * for the requested month and year.
     *
     * IMPORTANT: Only 'Kk' ignore 'SKk' and 'Nkk'.
     */
    public static function applyContractedOnSpecificMonthFilter($query, $request): Builder
    {
        if ($request->filled('contracted_on_specific_month')) {
            return $query->whereHas('statusHistory', function ($historyQuery) use ($request) {
                $historyQuery
                    ->whereYear('start_date', $request->input('contracted_on_year'))
                    ->whereMonth('start_date', $request->input('contracted_on_month'))
                    ->where('status_id', ProcessStatus::CONTRACTED_RECORD_ID);
            });
        }

        if ($request->filled('contracted_on_specific_months')) {
            return $query->whereHas('statusHistory', function ($historyQuery) use ($request) {
                $historyQuery
                    ->whereYear('start_date', $request->input('contracted_on_year'))
                    ->whereIn(DB::raw('MONTH(start_date)'), $request->input('contracted_on_months'))
                    ->where('status_id', ProcessStatus::CONTRACTED_RECORD_ID);
            });
        }

        return $query;
    }

    /**
     * Filter only processes which have been registered,
     * for the requested month and year.
     *
     * IMPORTANT: Only 'Пцр' ignore 'SПцр'.
     */
    public static function applyRegisteredOnSpecificMonthFilter($query, $request): Builder
    {
        if ($request->filled('registered_on_specific_month')) {
            return $query->whereHas('statusHistory', function ($historyQuery) use ($request) {
                $historyQuery
                    ->whereYear('start_date', $request->input('registered_on_year'))
                    ->whereMonth('start_date', $request->input('registered_on_month'))
                    ->where('status_id', ProcessStatus::REGISTERED_RECORD_ID);
            });
        }

        if ($request->filled('registered_on_specific_months')) {
            return $query->whereHas('statusHistory', function ($historyQuery) use ($request) {
                $historyQuery
                    ->whereYear('start_date', $request->input('registered_on_year'))
                    ->whereIn(DB::raw('MONTH(start_date)'), $request->input('registered_on_months'))
                    ->where('status_id', ProcessStatus::REGISTERED_RECORD_ID);
            });
        }

        return $query;
    }

    /**
     * Filter only processes which have specific general status history,
     * for the requested month and year.
     */
    public static function applyGeneralStatusHistoryFilter($query, $request): Builder
    {
        if ($request->filled('has_general_status_history')) {
            return $query->whereHas('statusHistory', function ($historyQuery) use ($request) {
                $historyQuery
                    ->whereYear('start_date', $request->input('has_general_status_for_year'))
                    ->whereMonth('start_date', $request->input('has_general_status_for_month'))
                    ->whereHas('status.generalStatus', function ($statusQuery) use ($request) {
                        $statusQuery->where('id', $request->input('has_general_status_id'));
                    });
            });
        }

        return $query;
    }

    /**
     * Filter only processes which have specific deadline status
     */
    public static function applyDeadlineStatusFilter($query, $request)
    {
        if (!$request->filled('deadline_status')) return;

        switch ($request->input('deadline_status')) {
            case self::DEADLINE_STOPPED_STATUS_NAME:
                return $query->where('days_past_since_last_activity', -1);
                break;

            case self::NO_DEADLINE_STATUS_NAME:
                return $query->where('days_past_since_last_activity', 0);
                break;

            case self::DEADLINE_NOT_EXPIRED_STATUS_NAME:
                return $query->where(function ($subquery) {
                    $subquery->where('days_past_since_last_activity', '>', 0)
                        ->where('days_past_since_last_activity', '<=', ProcessStatus::MAX_PROCESS_ACTIVITY_DELAY_DAYS);
                });
                break;

            case self::DEADLINE_EXPIRED_STATUS_NAME:
                return $query->where('days_past_since_last_activity', '>', ProcessStatus::MAX_PROCESS_ACTIVITY_DELAY_DAYS);
                break;
        }
    }

    public static function getFilterConfig(): array
    {
        return [
            'whereEqual' => ['contracted_in_asp', 'registered_in_asp', 'responsible_person_id'],
            'whereIn' => ['id', 'status_id', 'marketing_authorization_holder_id'],
            'whereInAmbigious' => [
                [
                    'inputName' => 'country_id',
                    'tableName' => 'processes',
                ]
            ],
            'like' => ['trademark_en', 'trademark_ru'],
            'dateRange' => ['created_at', 'updated_at'],

            'relationEqual' => [
                [
                    'inputName' => 'product_dosage',
                    'relationName' => 'product',
                    'relationAttribute' => 'products.dosage',
                ],

                [
                    'inputName' => 'product_pack',
                    'relationName' => 'product',
                    'relationAttribute' => 'products.pack',
                ],

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
                ],
            ],

            'relationIn' => [
                [
                    'inputName' => 'general_status_name_for_analysts',
                    'relationName' => 'status.generalStatus',
                    'relationAttribute' => 'process_general_statuses.name_for_analysts',
                ],

                [
                    'inputName' => 'status_general_status_id',
                    'relationName' => 'status',
                    'relationAttribute' => 'process_statuses.general_status_id',
                ],

                [
                    'inputName' => 'product_inn_id',
                    'relationName' => 'product',
                    'relationAttribute' => 'products.inn_id',
                ],

                [
                    'inputName' => 'product_form_id',
                    'relationName' => 'product',
                    'relationAttribute' => 'products.form_id',
                ],

                [
                    'inputName' => 'product_class_id',
                    'relationName' => 'product',
                    'relationAttribute' => 'products.class_id',
                ],

                [
                    'inputName' => 'product_brand',
                    'relationName' => 'product',
                    'relationAttribute' => 'products.brand',
                ],

                [
                    'inputName' => 'manufacturer_id',
                    'relationName' => 'manufacturer',
                    'relationAttribute' => 'manufacturers.id',
                ],

                [
                    'inputName' => 'manufacturer_country_id',
                    'relationName' => 'manufacturer',
                    'relationAttribute' => 'manufacturers.country_id',
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Store, Update and Duplicate
    |--------------------------------------------------------------------------
    */

    /**
     * Create multiple instances of the model from the request data.
     *
     * AJAX request.
     *
     * This method processes an array of countries from the request,
     * merges specific forecast year data for each country, and creates
     * model instances with the combined data. It also attaches related
     * clinical trial countries and responsible people, and stores comments.
     */
    public static function storeMultipleRecordsByMADFromRequest(ProcessStoreRequest $request): void
    {
        $countries = $request->input('countries');

        foreach ($countries as $country) {
            // Merge 'country_id' and 'forecasts 1-3' into the request array
            $mergedData = $request->merge([...$country])->all();

            // Create a new instance of the model
            $record = self::create($mergedData);

            // BelongsToMany relations
            $record->clinicalTrialCountries()->attach($request->input('clinical_trial_country_ids'));

            // HasMany relations
            $record->storeCommentFromRequest($request);
        }
    }

    /**
     * AJAX request
     */
    public function updateByMADFromRequest(ProcessUpdateRequest $request): void
    {
        $this->update($request->all());

        // BelongsToMany relations
        $this->clinicalTrialCountries()->sync($request->input('clinical_trial_country_ids'));

        // HasMany relations
        $this->storeCommentFromRequest($request);
    }

    /**
     * AJAX request
     */
    public static function duplicateByMADFromRequest(ProcessDuplicateRequest $request): void
    {
        $record = self::create($request->all());

        // BelongsToMany relations
        $record->clinicalTrialCountries()->attach($request->input('clinicalTrialCountries'));

        // HasMany relations
        $record->storeCommentFromRequest($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Date validations of model on saving & updating events
    |--------------------------------------------------------------------------
    */

    /**
     * Handle validation and automatic updates for the 'forecast_year_1_update_date' attribute.
     *
     * This method ensures that the 'forecast_year_1_update_date' is set appropriately
     * whenever the 'forecast_year_1' attribute is modified during the saving event.
     */
    private function handleForecastUpdateDate()
    {
        // forecast_year_1 is available from stage 2
        if ($this->isDirty('forecast_year_1') && $this->forecast_year_1) {
            $this->forecast_year_1_update_date = now();
        }
    }

    /**
     * Handle validation and automatic updates for the 'increased_price_date' attribute.
     *
     * This method ensures that the 'increased_price_date' is set appropriately
     * whenever the 'increased_price' attribute is modified during the saving event.
     */
    private function handleIncreasedPriceDate()
    {
        // The 'increased_price' attribute is relevant starting from stage 4
        // If 'increased_price' is not set, clear the 'increased_price_date' attribute
        if (!$this->increased_price) {
            $this->increased_price_date = null;
        }
        // If 'increased_price' is set and has been modified, update 'increased_price_date'
        elseif ($this->isDirty('increased_price')) {
            $this->increased_price_date = now();
        }
    }

    /**
     * Handle updates for the 'responsible_person_update_date' attribute.
     *
     * This method updates the 'responsible_person_update_date'
     * attribute whenever the 'responsible_person_id' attribute is modified during
     * the updating event.
     */
    private function handleResponsiblePersonUpdateDate()
    {
        // Set the timestamp to the current date when the model is created
        if ($this->isDirty('responsible_person_id')) {
            $this->responsible_person_update_date = now();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Status history helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Handle status updates for the process model.
     *
     * This method ensures that any changes to the 'status_id' attribute
     * are properly recorded in the process status history. It closes the
     * current status history entry (if applicable) and creates a new one.
     *
     * Used during updating event of the model.
     */
    private function updateStatusHistory()
    {
        // Check if the 'status_id' attribute has been modified
        if ($this->isDirty('status_id')) {
            // Close the current status history entry
            $this->activeStatusHistory->close();

            // Create a new status history entry
            $this->addStatusHistoryForCurrentStatus();
        }
    }

    /**
     * Add a new status history record for the current process status.
     *
     * This method creates a new entry in the status history table, storing the
     * current 'status_id'. If no specific start date is provided, it defaults
     * to the current timestamp.
     *
     * This function is typically used during the creation or update of the model,
     * but it can be called elsewhere when needed.
     *
     * @param string|null $startDate Optional start date for the status history.
     */
    private function addStatusHistoryForCurrentStatus($startDate = null)
    {
        // If no start date is provided, use the current timestamp.
        $startDate = $startDate ?? now();

        // Create a new status history entry with the current status.
        $this->statusHistory()->create([
            'status_id' => $this->status_id,
            'start_date' => $startDate,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Ordering by 'days_past_since_last_activity' and related helpers
    |--------------------------------------------------------------------------
    */

    public static function ensureOrderByActivityFlag(Request $request, bool $default = true): void
    {
        // Disable ordering by default for specific user (e.g., Фирдавс Киличбеков)
        if (auth()->id() === 1 && !$request->has('order_by_days_past_since_last_activity')) {
            $default = false;
        }

        $request->mergeIfMissing([
            'order_by_days_past_since_last_activity' => $request->boolean('order_by_days_past_since_last_activity', $default),
        ]);
    }

    private static function orderQueryByActivityIfRequested($query, $request): void
    {
        $query->when($request->input('order_by_days_past_since_last_activity'), function ($q) {
            return $q->orderBy('days_past_since_last_activity', 'desc');
        });
    }

    /**
     * Validates and sets the 'days_past_since_last_activity' attribute of the record.
     *
     * This method is typically called on model 'created'/'updated' events,
     * after storing comments and on 'updating' event of ProcessStatusHistory model.
     *
     * It assigns 'days_past_since_last_activity' based on the following logic:
     * - **-1**: For records with a 'stopped' status.
     * - **0**: For records that have no deadline.
     * - **[0 to 15]**: For records whose deadline is coming but has not yet expired. it's set to the number of days past the last activity.
     * - **[16 and bigger]**: For records with an expired deadline, it's set to the number of days past the last activity.
     */
    public function recalculateDaysPastSinceLastActivity(bool $refresh = true): void
    {
        // Refresh model to ensure related data (status history, comments) is up to date.
        if ($refresh) {
            $this->refresh();
        }

        // Escape errors while processes activeStatusHistory can be null,
        // right after closing active status history on ProcessStatusHistory updated event.
        if (!$this->activeStatusHistory) {
            return;
        }

        // If the record's status is "stopped", set priority to -1 and exit.
        if ($this->status->is_stopped_status) {
            $this->days_past_since_last_activity = -1;
            $this->timestamps = false;
            $this->saveQuietly();
            return;
        }

        // Initialize days_past_since_last_activity to 0, as a default value,
        // for non-stopped statuses which don`t have deadline.
        $this->days_past_since_last_activity = 0;

        // If the status has a deadline, calculate 'days_past_since_last_activity'.
        if ($this->status->hasDeadline()) {
            $lastActivityDate = $this->getLastActivityDateByStatusUpdateOrCommentCreate();
            $daysPast = $lastActivityDate->diffInDays(now());
            $this->days_past_since_last_activity = $daysPast;
        }

        // Save silently without touching timestamps.
        $this->timestamps = false;
        $this->saveQuietly();
    }


    /**
     * Get the latest activity date between the last status update and the last comment creation.
     *
     * Used when recalculating 'days_past_since_last_activity' of process.
     *
     * This function compares two dates:
     * 1. The start_date from the associated activeStatusHistory (which is always expected to exist).
     * 2. The created_at date from the last associated comment (which may or may not exist).
     *
     * @return Carbon|null The latest Carbon date, or null if neither date is available (though activeStatusHistory should always provide one).
     */
    public function getLastActivityDateByStatusUpdateOrCommentCreate()
    {
        $lastStatusUpdateDate = $this->activeStatusHistory->start_date;
        $lastCommentCreateDate = $this->lastComment?->created_at;

        // Compare the two dates and return the latest one.
        if ($lastCommentCreateDate && $lastStatusUpdateDate->lessThan($lastCommentCreateDate)) {
            return $lastCommentCreateDate;
        }

        // If lastCommentCreateDate is null or older than lastStatusUpdateDate,
        // or if only lastStatusUpdateDate exists, return lastStatusUpdateDate.
        return $lastStatusUpdateDate;
    }

    /**
     * Executed by scheduler daily.
     */
    public static function recalculateAllDaysPastSinceLastActivity(): void
    {
        self::withTrashed()->with(['activeStatusHistory', 'lastComment'])->chunk(1000, function ($records) {
            foreach ($records as $record) {
                $record->recalculateDaysPastSinceLastActivity(false); // false => don`t refresh records
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    /**
     * Notify users if status has been updated to contact stage.
     *
     * Used during the updating event of the model.
     */
    private function notifyUsersOnContractStage(): void
    {
        if ($this->isDirty('status_id')) {
            $status = ProcessStatus::find($this->status_id);

            if ($status->generalStatus->stage == 5) {
                $notification = new ProcessStageChangedToContract($this, $status->name);
                User::notifyUsersBasedOnPermission($notification, 'receive-notification-on-MAD-VPS-contract');
            }
        }
    }

    /**
     * Check wether current process can be edited by authenticated user or not.
     *
     * Used in processes.edit & processes.duplicate pages.
     */
    public function ensureAuthUserHasAccessToProcess($request)
    {
        $userID = $request->user()->id;

        if ($this->product->manufacturer->analyst_user_id != $userID) {
            Gate::authorize('edit-MAD-VPS-of-all-analysts');
        }
    }

    /**
     * Used on processes.index/trash pages for query ordering.
     *
     * Important:
     * Don`t forget to update 'removeOrderingByTableJoinsFromRequest()'
     * when adding/removing table joins.
     */
    private static function addTableJoinsForQueryOrdering($query, $request): void
    {
        $orderBy = $request->input('order_by');

        match ($orderBy) {
            'product_manufacturer_name' => $query->withProductsManufacturerNameAttribute(),
            'product_inn_name'          => $query->withProductsInnNameAttribute(),
            'product_form_name'         => $query->withProductsFormNameAttribute(),
            'product_dosage'            => $query->withProductsDosageAttribute(),
            default                     => null,
        };
    }

    /**
     * Remove 'ordering by table joins' from request
     * when exporting records or product selection for performance.
     */
    private static function removeOrderingByTableJoinsFromRequest($request): void
    {
        $orderBy = $request->input('order_by');

        $joinFields = [
            'product_manufacturer_name',
            'product_inn_name',
            'product_form_name',
            'product_dosage',
        ];

        if (in_array($orderBy, $joinFields)) {
            $request->merge(['order_by' => 'id']);
        }
    }

    // Used on filters.
    public static function pluckAllEnTrademarks(): SupportCollection
    {
        return self::whereNotNull('trademark_en')
            ->select('trademark_en')
            ->distinct()
            ->orderBy('trademark_en', 'asc')
            ->pluck('trademark_en');
    }

    // Used on filters.
    public static function pluckAllRuTrademarks(): SupportCollection
    {
        return self::whereNotNull('trademark_ru')
            ->select('trademark_ru')
            ->distinct()
            ->orderBy('trademark_ru', 'asc')
            ->pluck('trademark_ru');
    }

    // Used on filters.
    public static function getDeadlineStatusOptions(): array
    {
        return [
            self::DEADLINE_STOPPED_STATUS_NAME,
            self::NO_DEADLINE_STATUS_NAME,
            self::DEADLINE_NOT_EXPIRED_STATUS_NAME,
            self::DEADLINE_EXPIRED_STATUS_NAME,
        ];
    }

    /**
     * Add general statuses with periods for a collection of records.
     *
     * @param \Illuminate\Database\Eloquent\Collection $records The collection of records to process.
     *
     * @return void
     */
    public static function addGeneralStatusPeriodsForRecords($records)
    {
        // Get all general statuses
        $generalStatuses = ProcessGeneralStatus::all();

        foreach ($records as $record) {
            // Clone general statuses to avoid modifying the original collection
            $clonedGeneralStatuses = $generalStatuses->map(function ($item) {
                return clone $item;
            });

            // Add general statuses with periods
            $record->addGeneralStatusPeriods($clonedGeneralStatuses);
        }
    }

    /**
     * Get self with all its similar records for order.
     */
    public function getSelfWithSimilarRecordsForOrder($appendMAHNameWithID = false)
    {
        $processes = self::onlyReadyForOrder()
            ->where('product_id', $this->product_id)
            ->where('country_id', $this->country_id)
            ->with('mah')
            ->get();

        if ($appendMAHNameWithID) {
            $processes->each->append('mah_name_with_id');
        }

        return $processes;
    }

    /**
     * Add general statuses with periods for record.
     *
     * This method processes a collection of records and adds general status periods
     * based on the status history of each record. It clones the general statuses to
     * avoid modifying the original collection, calculates the start and end dates,
     * duration days, and duration days ratio for each general status.
     *
     * @return void
     */
    public function addGeneralStatusPeriods($generalStatuses = null): void
    {
        if (!$generalStatuses) {
            $generalStatuses = ProcessGeneralStatus::all();
        }

        foreach ($generalStatuses as $generalStatus) {
            // Filter status histories related to the current general status
            $histories = $this->statusHistory->filter(function ($history) use ($generalStatus) {
                return $history->status->general_status_id === $generalStatus->id;
            });

            // Skip if no histories found
            if ($histories->isEmpty()) continue;

            // Sort histories by ID to find the first and last history
            $firstHistory = $histories->sortBy('id')->first();
            $lastHistory = $histories->sortByDesc('id')->first();

            // Set the start_date of the general status
            $generalStatus->start_date = $firstHistory->start_date;

            // Set the end_date of the general status
            $generalStatus->end_date = $lastHistory->end_date ?: now();

            // Calculate duration_days for not closed status history
            // Process current status last history must not be closed logically
            $lastHistory->duration_days = $lastHistory->duration_days ?? round($lastHistory->start_date->diffInDays(now(), true));

            // Then calculate the total duration_days
            $generalStatus->duration_days = $histories->sum('duration_days');
        }

        // Calculate the highest duration_days among all general statuses
        $highestPeriod = $generalStatuses->max('duration_days') ?: 1;

        // Calculate duration_days_ratio for each general status
        foreach ($generalStatuses as $generalStatus) {
            $generalStatus->duration_days_ratio = $generalStatus->duration_days
                ? round($generalStatus->duration_days * 100 / $highestPeriod)
                : 0;
        }

        // Assign general statuses to the record
        $this->general_statuses_with_periods = $generalStatuses;
    }

    public static function getMADTableHeadersForUser($user): array|null
    {
        if (Gate::forUser($user)->denies(Permission::extractAbilityName(Permission::CAN_VIEW_MAD_VPS_NAME))) {
            return null;
        }

        $order = 1;
        $columns = array();

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_MAD_VPS_NAME))) {
            array_push(
                $columns,
                ['title' => 'Record', 'key' => 'edit', 'width' => 60, 'sortable' => false, 'visible' => 1, 'order' => $order++],
                ['title' => 'actions.Duplicate_short', 'key' => 'duplicate', 'width' => 60, 'sortable' => false, 'visible' => 1, 'order' => $order++],
            );
        }

        array_push(
            $columns,
            ['title' => 'ID', 'key' => 'id', 'width' => 62, 'sortable' => true, 'visible' => 1, 'order' => $order++],
            ['title' => 'status.Date', 'key' => 'last_status_date', 'width' => 100, 'sortable' => false, 'visible' => 1, 'order' => $order++],
            ['title' => 'Deadline', 'key' => 'deadline_status', 'width' => 132, 'sortable' => false, 'visible' => 1, 'order' => $order++],
        );

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_CONTROL_MAD_ASP_PROCESSES))) {
            array_push(
                $columns,
                ['title' => 'statuses.5Кк', 'key' => 'contracted_in_asp', 'width' => 60, 'sortable' => true, 'visible' => 1, 'order' => $order++],
                ['title' => 'statuses.7НПР', 'key' => 'registered_in_asp', 'width' => 70, 'sortable' => true, 'visible' => 1, 'order' => $order++],
            );
        }

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_MARK_MAD_VPS_AS_READY_FOR_ORDER))) {
            array_push(
                $columns,
                ['title' => 'statuses.8Р', 'key' => 'readiness_for_order_date', 'width' => 60, 'sortable' => true, 'visible' => 1, 'order' => $order++],
            );
        }

        $additionalColumns = [
            ['title' => 'Status', 'key' => 'status_id', 'width' => 76, 'sortable' => true],
            ['title' => 'status.An*', 'key' => 'general_status_name_for_analysts', 'width' => 80, 'sortable' => false],
            ['title' => 'status.General', 'key' => 'general_status_name', 'width' => 106, 'sortable' => false],

            ['title' => 'fields.BDM', 'key' => 'manufacturer_bdm', 'width' => 146, 'sortable' => false],
            ['title' => 'fields.Analyst', 'key' => 'manufacturer_analyst', 'width' => 146, 'sortable' => false],
            ['title' => 'fields.Search country', 'key' => 'country_id', 'width' => 130, 'sortable' => true],

            ['title' => 'fields.Manufacturer category', 'key' => 'manufacturer_category_name', 'width' => 110, 'sortable' => false],
            ['title' => 'fields.Manufacturer country', 'key' => 'manufacturer_country_name', 'width' => 140, 'sortable' => false],
            ['title' => 'fields.Manufacturer', 'key' => 'product_manufacturer_name', 'width' => 140, 'sortable' => true],

            ['title' => 'fields.Generic', 'key' => 'product_inn_name', 'width' => 180, 'sortable' => true],
            ['title' => 'fields.Form', 'key' => 'product_form_name', 'width' => 120, 'sortable' => true],
            ['title' => 'fields.Dosage', 'key' => 'product_dosage', 'width' => 120, 'sortable' => true],
            ['title' => 'fields.Pack', 'key' => 'product_pack', 'width' => 120, 'sortable' => false],
            ['title' => 'fields.MOQ', 'key' => 'product_moq', 'width' => 140, 'sortable' => false],
            ['title' => 'fields.Shelf life', 'key' => 'product_shelf_life', 'width' => 112, 'sortable' => false],

            ['title' => 'fields.Manufacturer price 1', 'key' => 'manufacturer_first_offered_price', 'width' => 106, 'sortable' => true],
            ['title' => 'fields.Manufacturer price 2', 'key' => 'manufacturer_followed_offered_price', 'width' => 106, 'sortable' => true],
            ['title' => 'fields.Currency', 'key' => 'currency_id', 'width' => 86, 'sortable' => true],
            ['title' => 'fields.Price in USD', 'key' => 'manufacturer_offered_price_in_usd', 'width' => 94, 'sortable' => false],
            ['title' => 'fields.Agreed price', 'key' => 'agreed_price', 'width' => 104, 'sortable' => true],
            ['title' => 'fields.Our price 2', 'key' => 'our_followed_offered_price', 'width' => 118, 'sortable' => true],
            ['title' => 'fields.Our price 1', 'key' => 'our_first_offered_price', 'width' => 118, 'sortable' => true],
            ['title' => 'fields.Increased price', 'key' => 'increased_price', 'width' => 158, 'sortable' => true],
            ['title' => 'fields.Increased price %', 'key' => 'increased_price_percentage', 'width' => 154, 'sortable' => false],
            ['title' => 'fields.Increased price date', 'key' => 'increased_price_date', 'width' => 146, 'sortable' => false],

            ['title' => 'fields.Product class', 'key' => 'product_class', 'width' => 80, 'sortable' => false],
            ['title' => 'fields.ATX', 'key' => 'product_atx_name', 'width' => 190, 'sortable' => false],
            ['title' => 'fields.Our ATX', 'key' => 'product_atx_short_name', 'width' => 150, 'sortable' => false],
            ['title' => 'fields.MAH', 'key' => 'marketing_authorization_holder_id', 'width' => 102, 'sortable' => true],
            ['title' => 'fields.TM Eng', 'key' => 'trademark_en', 'width' => 110, 'sortable' => true],
            ['title' => 'fields.TM Rus', 'key' => 'trademark_ru', 'width' => 110, 'sortable' => true],

            ['title' => 'fields.Date of forecast', 'key' => 'forecast_year_1_update_date', 'width' => 106, 'sortable' => true],
            ['title' => 'fields.Forecast 1 year', 'key' => 'forecast_year_1', 'width' => 130, 'sortable' => true],
            ['title' => 'fields.Forecast 2 year', 'key' => 'forecast_year_2', 'width' => 130, 'sortable' => true],
            ['title' => 'fields.Forecast 3 year', 'key' => 'forecast_year_3', 'width' => 130, 'sortable' => true],

            ['title' => 'fields.Dossier status', 'key' => 'dossier_status', 'width' => 120, 'sortable' => true],
            ['title' => 'fields.Year Cr/Be', 'key' => 'clinical_trial_year', 'width' => 180, 'sortable' => true],
            ['title' => 'fields.Countries Cr/Be', 'key' => 'clinical_trial_countries_name', 'width' => 116, 'sortable' => false],
            ['title' => 'fields.Country ich', 'key' => 'clinical_trial_ich_country', 'width' => 108, 'sortable' => true],
            ['title' => 'fields.Zones', 'key' => 'product_zones_name', 'width' => 54, 'sortable' => false],
            ['title' => 'fields.Down payment 1', 'key' => 'down_payment_1', 'width' => 124, 'sortable' => true],
            ['title' => 'fields.Down payment 2', 'key' => 'down_payment_2', 'width' => 124, 'sortable' => true],
            ['title' => 'fields.Down payment condition', 'key' => 'down_payment_condition', 'width' => 110, 'sortable' => true],

            ['title' => 'fields.Responsible', 'key' => 'responsible_person_id', 'width' => 132, 'sortable' => true],
            ['title' => 'fields.Responsible update date', 'key' => 'responsible_person_update_date', 'width' => 250, 'sortable' => true],
            ['title' => 'fields.Days have passed', 'key' => 'days_past', 'width' => 110, 'sortable' => false],

            ['title' => 'dates.Date of creation', 'key' => 'created_at', 'width' => 130, 'sortable' => true],
            ['title' => 'dates.Update date', 'key' => 'updated_at', 'width' => 150, 'sortable' => true],

            ['title' => 'Comments', 'key' => 'comments_count', 'width' => 132, 'sortable' => false],
            ['title' => 'comments.Last', 'key' => 'last_comment_body', 'width' => 240, 'sortable' => false],
            ['title' => 'comments.Date', 'key' => 'last_comment_created_at', 'width' => 116, 'sortable' => false],
        ];

        foreach ($additionalColumns as $column) {
            array_push($columns, [
                ...$column,
                'visible' => 1,
                'order' => $order++,
            ]);
        }

        if (Gate::forUser($user)->allows(Permission::extractAbilityName(Permission::CAN_EDIT_MAD_VPS_STATUS_HISTORY_NAME))) {
            array_push(
                $columns,
                ['title' => 'History', 'key' => 'status_history', 'width' => 72, 'sortable' => true, 'visible' => 1, 'order' => $order++],
            );
        }

        $statusColumns = [
            ['title' => 'statuses.ВП', 'key' => 'general_status_periods_1'],
            ['title' => 'statuses.ПО', 'key' => 'general_status_periods_2'],
            ['title' => 'statuses.АЦ', 'key' => 'general_status_periods_3'],
            ['title' => 'statuses.СЦ', 'key' => 'general_status_periods_4'],
            ['title' => 'statuses.Кк', 'key' => 'general_status_periods_5'],
            ['title' => 'statuses.КД', 'key' => 'general_status_periods_6'],
            ['title' => 'statuses.НПР', 'key' => 'general_status_periods_7'],
            ['title' => 'statuses.Р', 'key' => 'general_status_periods_8'],
            ['title' => 'statuses.Зя', 'key' => 'general_status_periods_9'],
            ['title' => 'statuses.Отмена', 'key' => 'general_status_periods_10'],
        ];

        foreach ($statusColumns as $column) {
            array_push($columns, [
                ...$column,
                'width' => 174,
                'sortable' => false,
                'visible' => 1,
                'order' => $order++,
            ]);
        }

        return $columns;
    }
}
