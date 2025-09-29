import { defineStore } from "pinia";

export const useAttachmentsStore = defineStore('attachments', {
    state: () => ({
        selected: [],
    }),
});
