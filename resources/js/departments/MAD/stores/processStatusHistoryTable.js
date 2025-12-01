import { defineStore } from "pinia";

export const useProcessStatusHistoryStore = defineStore('processStatusHistory', {
    state: () => ({
        selected: [],

        // Edit form
        editDialog: false,
        activeRecord: undefined,
    }),

    actions: {
        resetState() {
            this.selected = [];
            this.editDialog = false;
            this.activeRecord = undefined;
        },
    }
});
