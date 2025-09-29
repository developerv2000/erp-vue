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
});
