import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'
import { normalizeSingleID, normalizeMultiIDs, cleanQueryParams } from '@/core/scripts/utilities';
import axios from 'axios';

export const useMADManufacturerTableStore = defineStore('MADManufacturerTable', {
    state: () => ({
        records: [],
        loading: false,
        initializedFromInertiaPage: false,
        selected: [],

        pagination: {
            page: 1,
            per_page: 50,
            order_by: 'updated_at',
            order_direction: 'desc',
            total_records: 0,
            last_page: 1,
            navigate_to_page: 1,
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
        initFromInertiaPage(page) {
            this.records = [];
            const query = page.props.query;

            // Pagination
            this.pagination.page = Number(query.page ?? 1);
            this.pagination.per_page = Number(query.per_page ?? 50);
            this.pagination.order_by = query.order_by ?? 'updated_at';
            this.pagination.order_direction = query.order_direction ?? 'desc';
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

            axios.get('/api/manufacturers', {
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
            router.get(route('mad.manufacturers.index'), this.toQuery(), {
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
            this.pagination.per_page = 50;
            this.pagination.order_by = 'updated_at';
            this.pagination.order_direction = 'desc';
            this.pagination.total_records = 0;
            this.last_page = 1;
            this.navigate_to_page = 1,

                // Singular autocompletes
                this.filters.analyst_user_id = null;
            this.filters.bdm_user_id = null;

            // Multiple autocompletes
            this.filters.country_id = [];
            this.filters.id = [];
        },

        resetUrl() {
            router.get(route('mad.manufacturers.index'), {}, {
                only: ['smartFilterDependencies'],
                replace: true,
                preserveState: true,
                preserveScroll: true,
            });
        },
    }
})
