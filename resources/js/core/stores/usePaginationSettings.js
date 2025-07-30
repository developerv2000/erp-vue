import { defineStore } from "pinia";

export const usePaginationSettingsStore = defineStore('paginationSettings', {
    state: () => ({
        page: null,
        per_page: null,
        order_by: null,
        order_direction: null,
        total_records: null,
    }),
    actions: {
        initFromQuery(query = {}) {
            this.page = query.page ?? null;
            this.per_page = query.per_page ?? null;
            this.order_by = query.order_by ?? null;
            this.order_direction = query.order_direction ?? null;
        },

        toQuery() {
            return {
                page: this.page,
                per_page: this.per_page,
                order_by: this.order_by,
                order_direction: this.order_direction,
            };
        },

        reest() {
            $this.page = null;
            $this.per_page = null;
            $this.order_by = null;
            $this.order_direction = null;
            $this.total_records = null;
        },
    },
});
