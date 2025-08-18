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
        addSubmitionFailedMessage(t) {
            this.add({
                text: t('messages.Submition failed'),
                color: 'error',
            });
        },
        addSuccessefullyDeletedMessage(t, count) {
            this.add({
                text: t('messages.Successefully deleted', { count: count }),
                color: 'success',
            });
        },
    },
});
