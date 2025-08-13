import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'
import { normalizeSingleID, normalizeMultiIDs, cleanQueryParams } from '@/core/scripts/utilities';
import axios from 'axios';

const DEFAULT_PER_PAGE = 50;
const DEFAULT_ORDER_BY = 'updated_at';
const DEFAULT_ORDER_DIRECTION = 'desc';

const API_URL = '/api/manufacturers';

export const useMADManufacturerTableStore = defineStore('MADManufacturerTable', {
    state: () => ({
        records: [],
        loading: false,
        initializedFromInertiaPage: false,
        selected: [],
        isTrashPage: false,

        pagination: {
            page: 1,
            per_page: DEFAULT_PER_PAGE,
            order_by: DEFAULT_ORDER_BY,
            order_direction: DEFAULT_ORDER_DIRECTION,
            total_records: 0,
            last_page: 1,
            navigate_to_page: 1, // Prepended navigation
        },

        filters: {
            // Singular autocompletes
            analyst_user_id: null,
            bdm_user_id: null,

            // Multiple autocompletes
            country_id: null,
            id: null,
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
            this.pagination.per_page = Number(query.per_page ?? DEFAULT_PER_PAGE);
            this.pagination.order_by = query.order_by ?? DEFAULT_ORDER_BY;
            this.pagination.order_direction = query.order_direction ?? DEFAULT_ORDER_DIRECTION;
            this.navigate_to_page = this.pagination.page;

            // Normalize singular autocompletes
            this.filters.analyst_user_id = normalizeSingleID(query.analyst_user_id);
            this.filters.bdm_user_id = normalizeSingleID(query.bdm_user_id);

            // Normalize multiple autocompletes
            this.filters.country_id = normalizeMultiIDs(query.country_id);
            this.filters.id = normalizeMultiIDs(query.id);

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
                analyst_user_id: this.filters.analyst_user_id,
                bdm_user_id: this.filters.bdm_user_id,
                country_id: this.filters.country_id,
                id: this.filters.id,
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

            // Pagination
            this.pagination.page = 1;
            this.pagination.per_page = DEFAULT_PER_PAGE;
            this.pagination.order_by = DEFAULT_ORDER_BY;
            this.pagination.order_direction = DEFAULT_ORDER_DIRECTION;
            this.pagination.total_records = 0;
            this.last_page = 1;
            this.navigate_to_page = 1;

            // Singular autocompletes
            this.filters.analyst_user_id = null;
            this.filters.bdm_user_id = null;

            // Multiple autocompletes
            this.filters.country_id = [];
            this.filters.id = [];
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
