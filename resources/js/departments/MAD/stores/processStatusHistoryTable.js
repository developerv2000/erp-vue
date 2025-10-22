import { defineStore } from "pinia";

export const useProcessStatusHistoryStore = defineStore('processStatusHistory', {
    state: () => ({
        selected: [],

        // Edit form
        editDialog: false,
        activeRecord: undefined,
    }),
});
