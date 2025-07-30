import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'

export const useMADManufacturerTableStore = defineStore('MADManufacturerTable', {
    state: () => ({
        pagination: {
            page: 1,
            per_page: 50,
            order_by: 'updated_at',
            order_direction: 'desc',
            total_records: 0,
        },

        filter: {
            analyst_user_id: null,
            country_id: null,
            id: null,
            bdm_user_id: null,
        },
    }),

    actions: {
        initFromQuery(query = {}) {
            this.pagination.page = query.page ?? 1;
            this.pagination.per_page = query.per_page ?? 50;
            this.pagination.order_by = query.order_by ?? 'updated_at';
            this.pagination.order_direction = query.order_direction ?? 'desc';
            this.pagination.total_records = query.total_records ?? 0;

            this.filter.analyst_user_id = query.analyst_user_id ?? null
            this.filter.country_id = query.country_id ?? null
            this.filter.id = query.id ?? null
            this.filter.bdm_user_id = query.bdm_user_id ?? null
        },

        toQuery() {
            return {
                page: this.pagination.page,
                per_page: this.pagination.per_page,
                order_by: this.pagination.order_by,
                order_direction: this.pagination.order_direction,

                analyst_user_id: this.filter.analyst_user_id,
                country_id: this.filter.country_id,
                id: this.filter.id,
                bdm_user_id: this.filter.bdm_user_id,
            }
        },

        updateAfterFetch(response) {
            this.pagination.total_records = response.data.total;
        },

        applyFilters() {
            router.get(route('mad.manufacturers.index'), this.toQuery(), {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            })
        },
    }
})
