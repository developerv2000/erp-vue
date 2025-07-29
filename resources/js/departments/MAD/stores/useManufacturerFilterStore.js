// stores/useMADManufacturerFilterStore.js
import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'

export const useMADManufacturerFilterStore = defineStore('MADManufacturerFilter', {
    state: () => ({
        filter: {
            analystUserId: null,
            countryId: null,
            id: null,
            bdmUserId: null,
        },
    }),

    actions: {
        initFromQuery(query = {}) {
            this.filter.analystUserId = query.analystUserId ?? null
            this.filter.countryId = query.countryId ?? null
            this.filter.id = query.id ?? null
            this.filter.bdmUserId = query.bdmUserId ?? null
        },

        toQuery() {
            return {
                analystUserId: this.filter.analystUserId,
                countryId: this.filter.countryId,
                id: this.filter.id,
                bdmUserId: this.filter.bdmUserId,
            }
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
