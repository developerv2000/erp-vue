import { defineStore } from "pinia";

export const useProcessStatusHistoryStore = defineStore('processStatusHistory', {
    state: () => ({
        selected: [],
    }),
});
