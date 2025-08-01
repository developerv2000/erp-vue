import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'
import { resolveSelectedOptions } from '@/core/scripts/utilities';

export const useMADManufacturerTableStore = defineStore('MADManufacturerTable', {
    state: () => ({
        records: [],

        pagination: {
            page: 1,
            per_page: 50,
            order_by: 'updated_at',
            order_direction: 'desc',
            total_records: 0,
        },

        filters: {
            analyst_user_id: null,
            country_id: null,
            id: null,
            bdm_user_id: null,
        },
    }),

    actions: {
        initFromServer(page) {
            this.records = [];
            const query = page.props.query;

            this.pagination.page = query.page ?? 1;
            this.pagination.per_page = query.per_page ?? 50;
            this.pagination.order_by = query.order_by ?? 'updated_at';
            this.pagination.order_direction = query.order_direction ?? 'desc';
            this.pagination.total_records = query.total_records ?? 0;

            this.filters.analyst_user_id = query.analyst_user_id ?? null
            this.filters.bdm_user_id = query.bdm_user_id ?? null

            // Resolve multiple selections
            this.filters.country_id = resolveSelectedOptions(
                query.country_id,
                page.props.smartFilterDependencies.countriesOrderedByName
            )

            this.filters.id = resolveSelectedOptions(
                query.id,
                page.props.smartFilterDependencies.manufacturers
            )
        },

        toQuery() {
            return {
                page: this.pagination.page,
                per_page: this.pagination.per_page,
                order_by: this.pagination.order_by,
                order_direction: this.pagination.order_direction,

                analyst_user_id: this.filters.analyst_user_id,
                bdm_user_id: this.filters.bdm_user_id,

                // Only send IDs
                country_id: this.filters.country_id.map(option => option.id),
                id: this.filters.id.map(option => option.id),
            }
        },

        updateAfterFetch(response) {
            this.records = response.data.data;
            this.pagination.total_records = response.data.total;
        },

        updateUrlWithFilterParams() {
            router.get(route('mad.manufacturers.index'), this.toQuery(), {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: [],
            })
        }
    }
})
