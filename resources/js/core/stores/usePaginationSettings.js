import { defineStore } from "pinia";

export const usePaginationSettingsStore = defineStore('paginationSettings', {
    state: () => ({
        currentPage: null,
        perPage: null,
        totalRecords: null,
        orderBy: null,
        orderDirection: null,
    }),
    actions: {
        initFromQuery(query = {}) {
            this.perPage = query.perPage ?? null;
            this.currentPage = query.currentPage ?? null;
            this.totalRecords = query.totalRecords ?? null;
            this.orderBy = query.orderBy ?? null;
            this.orderDirection = query.orderDirection ?? null;
        },

        toQuery() {
            return {
                perPage: this.perPage,
                currentPage: this.currentPage,
                totalRecords: this.totalRecords,
                orderBy: this.orderBy,
                orderDirection: this.orderDirection,
            };
        },

        reest() {
            $this.perPage = null;
            $this.currentPage = null;
            $this.totalRecords = null;
            $this.orderBy = null;
            $this.orderDirection = null;
        },
    },
});
