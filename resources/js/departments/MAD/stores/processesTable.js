import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'
import axios from 'axios';
import { cleanQueryParams, normalizeNumbersFromQuery, normalizeDateRangesFromQuery, normalizeDateRangesToQueryFormat, normalizeMultiIDsFromQuery, normalizeSingleIDsFromQuery } from '@/core/scripts/queryHelper';
import { createRecordActions } from '@/core/stores/helpers/createRecordActions';

const defaultPaginationOptions = {
    page: 1,
    per_page: 50,
    order_by: 'updated_at',
    order_direction: 'desc',
    total_records: 0,
    last_page: 1,
    navigate_to_page: 1, // Prepended navigation on footer of the table
};

const defaultFilters = {
    // Readonly filters
    contracted_on_specific_month: null, // Number
    contracted_on_year: null,
    contracted_on_month: null,
    registered_on_specific_month: null, // Number
    registered_on_year: null,
    registered_on_month: null,
    has_general_status_history: null, // Number
    has_general_status_for_year: null,
    has_general_status_for_month: null,
    has_general_status_id: null,

    // Boolean
    order_by_days_past_since_last_activity: null,
    contracted_in_asp: null,
    registered_in_asp: null,

    // Text fields
    product_dosage: null,
    product_pack: null,
    trademark_en: null,
    trademark_ru: null,

    // Date ranges
    active_status_start_date_range: null,
    created_at: null,
    updated_at: null,

    // Singular autocompletes
    deadline_status: null,
    manufacturer_region: null,

    // Singular id-based autocompletes
    manufacturer_analyst_user_id: null,
    manufacturer_bdm_user_id: null,
    responsible_person_id: null,
    manufacturer_category_id: null,

    // Multiple autocompletes
    general_status_name_for_analysts: [],
    product_brand: [],
    id: [],

    // Multiple id-based autocompletes
    product_inn_id: [],
    manufacturer_id: [],
    product_form_id: [],
    country_id: [],
    status_id: [],
    status_general_status_id: [],
    manufacturer_country_id: [],
    product_class_id: [],
}

const API_URL = route('api.processes.get');

export const useMADProcessesTableStore = defineStore('MADProcessesTable', {
    state: () => ({
        records: [],
        loading: false,
        initializedFromInertiaPage: false,
        selected: [],
        isTrashPage: false,

        pagination: {
            ...defaultPaginationOptions
        },

        filters: {
            ...defaultFilters
        },
    }),

    actions: {
        ...createRecordActions(this),
        detectCurrentPage() {
            this.isTrashPage = route().current('*.trash'); // Used to differ index/trash pages
        },

        initFromInertiaPage(page) {
            this.records = [];
            const query = page.props.query;

            // Pagination
            this.pagination.page = Number(query.page ?? 1);
            this.pagination.per_page = Number(query.per_page ?? defaultPaginationOptions.per_page);
            this.pagination.order_by = query.order_by ?? defaultPaginationOptions.order_by;
            this.pagination.order_direction = query.order_direction ?? defaultPaginationOptions.order_direction;
            this.navigate_to_page = this.pagination.page;

            // Readonly
            this.filters.contracted_on_specific_month = query.contracted_on_specific_month ? Number(query.contracted_on_specific_month) : null;
            this.filters.contracted_on_year = query.contracted_on_year;
            this.filters.contracted_on_month = query.contracted_on_month;
            this.filters.registered_on_specific_month = query.registered_on_specific_month ? Number(query.registered_on_specific_month) : null;
            this.filters.registered_on_year = query.registered_on_year;
            this.filters.registered_on_month = query.registered_on_month;
            this.filters.has_general_status_history = query.has_general_status_history ? Number(query.has_general_status_history) : null;
            this.filters.has_general_status_for_year = query.has_general_status_for_year;
            this.filters.has_general_status_for_month = query.has_general_status_for_month;
            this.filters.has_general_status_id = query.has_general_status_id;
            
            // Filters that don`t require normalization:
            // Text fields
            this.filters.product_dosage = query.product_dosage;
            this.filters.product_pack = query.product_pack;
            this.filters.trademark_en = query.trademark_en;
            this.filters.trademark_ru = query.trademark_ru;
            // Singular autocompletes
            this.filters.deadline_status = query.deadline_status;
            this.filters.manufacturer_region = query.manufacturer_region;
            // Multiple autocompletes
            this.filters.general_status_name_for_analysts = query.general_status_name_for_analysts;
            this.filters.product_brand = query.product_brand;
            this.filters.id = query.id;

            // Normalize filters
            normalizeNumbersFromQuery(this.filters, query, ['order_by_days_past_since_last_activity', 'contracted_in_asp', 'registered_in_asp']);
            normalizeSingleIDsFromQuery(this.filters, query, ['manufacturer_analyst_user_id', 'manufacturer_bdm_user_id', 'responsible_person_id', 'manufacturer_category_id']);
            normalizeMultiIDsFromQuery(this.filters, query, ['product_inn_id', 'manufacturer_id', 'product_form_id', 'country_id', 'status_id', 'status_general_status_id', 'manufacturer_country_id', 'product_class_id']);
            normalizeDateRangesFromQuery(this.filters, query, ['active_status_start_date_range', 'created_at', 'updated_at']);

            // Mark as initialized
            this.initializedFromInertiaPage = true;
        },

        toQuery() {
            const rawQuery = {
                // Only trashed
                only_trashed: this.isTrashPage ? true : null, // null -> remove from query

                // Pagination
                page: this.pagination.page,
                per_page: this.pagination.per_page,
                order_by: this.pagination.order_by,
                order_direction: this.pagination.order_direction,

                // Filters
                ...this.filters,
                ...normalizeDateRangesToQueryFormat(this.filters, ['active_status_start_date_range', 'created_at', 'updated_at']),
            };

            // Remove default pagination params if same as default
            if (rawQuery.page === defaultPaginationOptions.page) delete rawQuery.page;
            if (rawQuery.per_page === defaultPaginationOptions.per_page) delete rawQuery.per_page;
            if (rawQuery.order_by === defaultPaginationOptions.order_by) delete rawQuery.order_by;
            if (rawQuery.order_direction === defaultPaginationOptions.order_direction) delete rawQuery.order_direction;

            return cleanQueryParams(rawQuery);
        },

        fetchRecords({ updateUrl = true } = {}) {
            this.loading = true;

            axios.get(API_URL, {
                params: this.toQuery(),
            })
                .then(response => {
                    this.records = response.data.data;

                    this.pagination.total_records = response.data.total;
                    this.pagination.last_page = response.data.last_page;
                    this.pagination.navigate_to_page = response.data.current_page;

                    if (updateUrl) {
                        this.updateUrlAfterFetch();
                    }
                })
                .finally(() => {
                    this.loading = false;
                    this.selected = [];
                })
        },

        fetchRecordsIfOptionsChanged(options) {
            const sortBy = options.sortBy?.[0] ?? {};

            const hasChanged =
                options.page !== this.pagination.page ||
                options.itemsPerPage !== this.pagination.per_page ||
                sortBy.key !== this.pagination.order_by ||
                sortBy.order !== this.pagination.order_direction;

            if (!hasChanged) return;

            this.pagination.page = options.page;
            this.pagination.per_page = options.itemsPerPage;
            this.pagination.order_by = sortBy.key ?? this.pagination.order_by;
            this.pagination.order_direction = sortBy.order ?? this.pagination.order_direction;

            this.fetchRecords({ updateUrl: true });
        },

        updateUrlAfterFetch() {
            router.get(route(route().current()), this.toQuery(), {
                only: ['smartFilterDependencies', 'query'], // Also update query to trigger active filters class update
                replace: true,
                preserveState: true,
                preserveScroll: true,
            });
        },

        handleNavigateToPage() {
            this.pagination.page = this.pagination.navigate_to_page;
            this.fetchRecords({ updateUrl: true });
        },

        resetState() {
            this.records = [];
            this.loading = false;
            this.selected = [];

            this.pagination = {
                ...defaultPaginationOptions
            };

            this.filters = {
                ...defaultFilters
            };
        },

        resetUrl() {
            router.get(route(route().current()), {}, {
                only: ['smartFilterDependencies'],
                replace: true,
                preserveState: true,
                preserveScroll: true,
            });
        },

        applyFilter() {
            this.pagination.page = 1;
            this.fetchRecords();
        },

        resetFilter() {
            this.resetState();
            this.resetUrl();
            this.fetchRecords();
        },
    }
})
