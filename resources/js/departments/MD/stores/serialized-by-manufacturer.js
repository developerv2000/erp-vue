import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'
import axios from 'axios';
import { cleanQueryParams, normalizeDateRangesFromQuery, normalizeDateRangesToQueryFormat, normalizeMultiIDsFromQuery, normalizeNumbersFromQuery } from '@/core/scripts/queryHelper';
import { createRecordActions } from '@/core/stores/helpers/createRecordActions';

const defaultPaginationOptions = {
    page: 1,
    per_page: 50,
    order_by: 'id',
    order_direction: 'asc',
    total_records: 0,
    last_page: 1,
    navigate_to_page: 1, // Prepended navigation on footer of the table
};

const defaultFilters = {
    // Date ranges
    serialization_codes_request_date: null,
    serialization_codes_sent_date: null,
    serialization_report_recieved_date: null,
    report_sent_to_hub_date: null,
    created_at: null,
    updated_at: null,

    // Numbers
    order_id: null,

    // Singular autocompletes
    process_trademark_en: null,
    process_trademark_ru: null,

    // Multiple autocompletes
    id: [],

    // Multiple id-based autocompletes
    order_manufacturer_id: [],
    process_country_id: [],
}

const API_URL = route('api.md.serialized-by-manufacturer.get');

export const useMDSerializedByManufacturerTableStore = defineStore('MDSerializedByManufacturerTable', {
    state: () => ({
        records: [],
        loading: false,
        initializedFromInertiaPage: false,
        selected: [],

        pagination: {
            ...defaultPaginationOptions
        },

        filters: {
            ...defaultFilters
        },
    }),

    actions: {
        ...createRecordActions(this),
        initFromInertiaPage(page) {
            this.records = [];
            const query = page.props.query;

            // Pagination
            this.pagination.page = Number(query.page ?? 1);
            this.pagination.per_page = Number(query.per_page ?? defaultPaginationOptions.per_page);
            this.pagination.order_by = query.order_by ?? defaultPaginationOptions.order_by;
            this.pagination.order_direction = query.order_direction ?? defaultPaginationOptions.order_direction;
            this.navigate_to_page = this.pagination.page;

            // Filters that don`t require normalization
            this.filters.process_trademark_en = query.process_trademark_en;
            this.filters.process_trademark_ru = query.process_trademark_ru;
            this.filters.id = query.id;

            // Normalize filters
            normalizeNumbersFromQuery(this.filters, query, ['order_id']);
            normalizeMultiIDsFromQuery(this.filters, query, ['order_manufacturer_id ', 'process_country_id']);
            normalizeDateRangesFromQuery(this.filters, query, ['serialization_codes_request_date', 'serialization_codes_sent_date', 'serialization_report_recieved_date', 'report_sent_to_hub_date', 'created_at', 'updated_at']);

            // Mark as initialized
            this.initializedFromInertiaPage = true;
        },

        toQuery() {
            const rawQuery = {
                // Pagination
                page: this.pagination.page,
                per_page: this.pagination.per_page,
                order_by: this.pagination.order_by,
                order_direction: this.pagination.order_direction,

                // Filters
                ...this.filters,
                ...normalizeDateRangesToQueryFormat(this.filters, ['serialization_codes_request_date', 'serialization_codes_sent_date', 'serialization_report_recieved_date', 'report_sent_to_hub_date', 'created_at', 'updated_at']),
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
                only: ['query'], // Also update query to trigger active filters class update
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
                only: [],
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
