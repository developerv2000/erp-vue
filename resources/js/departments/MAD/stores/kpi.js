import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3';
import { cleanQueryParams, normalizeNumbersFromQuery, normalizeMultiIDsFromQuery, normalizeSingleIDsFromQuery } from '@/core/scripts/queryHelper';

const defaultFilters = {
    // Integers
    year: null,

    // Boolean
    extensive_version: null,

    // Singular autocompletes
    manufacturer_region: null,

    // Singular id-based autocompletes
    manufacturer_analyst_user_id: null,
    manufacturer_bdm_user_id: null,

    // Multiple id-based autocompletes
    months: [],
    country_id: [],
}

export const useMADKPIStore = defineStore('MADKPI', {
    state: () => ({
        loading: false,
        initializedFromInertiaPage: false,

        filters: {
            ...defaultFilters
        },
    }),

    actions: {
        initFromInertiaPage(page) {
            this.records = [];
            const query = page.props.query;

            // Filters that don`t require normalization:
            // Singular autocompletes
            this.filters.manufacturer_region = query.manufacturer_region;

            // Normalize filters
            this.filters.year = Number(page.props.kpiData.year);
            normalizeNumbersFromQuery(this.filters, query, ['extensive_version']);
            normalizeSingleIDsFromQuery(this.filters, query, ['manufacturer_analyst_user_id', 'manufacturer_bdm_user_id']);
            normalizeMultiIDsFromQuery(this.filters, query, ['months', 'country_id']);

            // Mark as initialized
            this.initializedFromInertiaPage = true;
        },

        toQuery() {
            const rawQuery = {
                // Filters
                ...this.filters,
            };

            return cleanQueryParams(rawQuery);
        },

        applyFilter() {
            router.get(route(route().current()), this.toQuery(), {
                only: ['kpiData', 'query'], // Also update query to trigger active filters class update
                replace: true,
                preserveState: true,
                preserveScroll: true,
                onStart: () => (this.loading = true),
                onFinish: () => (this.loading = false),
            })
        },

        resetState() {
            this.loading = false;
            this.filters = {
                ...defaultFilters
            };
        },

        resetFilter() {
            this.resetState();
            this.applyFilter();
        },
    },
})
