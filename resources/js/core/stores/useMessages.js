import { defineStore } from "pinia";

export const useMessagesStore = defineStore('messages', {
    state: () => ({
        queue: [],
    }),
    actions: {
        add(message) {
            this.queue.push(message);
        },
        addUpdatedSuccessfullyMessage(t) {
            this.add({
                text: t('messages.Updated successfully'),
                color: 'success',
            });
        },
        addSubmititionFailedMessage(t) {
            this.add({
                text: t('messages.Submition failed'),
                color: 'error',
            });
        },
    },
});
