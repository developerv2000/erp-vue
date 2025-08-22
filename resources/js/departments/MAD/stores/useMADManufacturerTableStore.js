import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'
import { normalizeSingleID, normalizeMultiIDs, cleanQueryParams } from '@/core/scripts/utilities';
import axios from 'axios';

const defaultPaginationOptions = {
    page: 1,
    per_page: 50,
    order_by: 'updated_at',
    order_direction: 'desc',
    total_records: 0,
    last_page: 1,
    navigate_to_page: 1, // Prepended navigation
};

const defaultFilters = {
    // Date ranges
    created_at: null,
    updated_at: null,

    // Singular autocompletes
    analyst_user_id: null,
    bdm_user_id: null,
    region: null,
    category_id: null,
    active: null,
    important: null,

    // Multiple autocompletes
    country_id: [],
    id: [],
    productClasses: [],
    zones: [],
    process_country_id: [],
    blacklists: [],
}

const API_URL = '/api/manufacturers';

export const useMADManufacturerTableStore = defineStore('MADManufacturerTable', {
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
        detectTrashPage() {
            this.isTrashPage = route().current('*.trash');
        },

        initFromInertiaPage(page) {
            this.detectTrashPage(); // Set isTrashPage
            this.records = [];
            const query = page.props.query;

            // Pagination
            this.pagination.page = Number(query.page ?? 1);
            this.pagination.per_page = Number(query.per_page ?? defaultPaginationOptions.per_page);
            this.pagination.order_by = query.order_by ?? defaultPaginationOptions.order_by;
            this.pagination.order_direction = query.order_direction ?? defaultPaginationOptions.order_direction;
            this.navigate_to_page = this.pagination.page;

            // Filters that don`t require normalization
            this.filters.region = query.region;
            this.filters.active = query.active;
            this.filters.important = query.important;
            this.filters.created_at = query.created_at;
            this.filters.updated_at = query.updated_at;

            // Normalize singular id autocompletes
            this.filters.analyst_user_id = normalizeSingleID(query.analyst_user_id);
            this.filters.bdm_user_id = normalizeSingleID(query.bdm_user_id);
            this.filters.category_id = normalizeSingleID(query.category_id);

            // Normalize multiple id autocompletes
            this.filters.country_id = normalizeMultiIDs(query.country_id);
            this.filters.id = normalizeMultiIDs(query.id);
            this.filters.productClasses = normalizeMultiIDs(query.productClasses);
            this.filters.zones = normalizeMultiIDs(query.zones);
            this.filters.process_country_id = normalizeMultiIDs(query.process_country_id);
            this.filters.blacklists = normalizeMultiIDs(query.blacklists);

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
                ...this.filters
            };

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
                only: ['smartFilterDependencies'],
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
    }
})
