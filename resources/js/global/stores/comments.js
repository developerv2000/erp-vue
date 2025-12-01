import { defineStore } from "pinia";

export const useCommentsStore = defineStore('comments', {
    state: () => ({
        editDialog: false,
        destroyDialog: false,

        activeRecord: {
            id: null,
            body: null
        },
    }),

    actions: {
        resetState() {
            this.editDialog = false;
            this.destroyDialog = false;

            this.activeRecord.id = null;
            this.activeRecord.body = null;
        },
    },
});
