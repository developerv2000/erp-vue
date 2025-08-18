import { defineStore } from "pinia";
import i18n from "../boot/i18n";

export const useMessagesStore = defineStore('messages', {
    state: () => ({
        queue: [],
    }),
    actions: {
        add(message) {
            this.queue.push(message);
        },
        addUpdatedSuccessfullyMessage() {
            this.add({
                text: i18n.global.t('messages.Updated successfully'),
                color: 'success',
            });
        },
        addSubmitionFailedMessage() {
            this.add({
                text: i18n.global.t('messages.Submition failed'),
                color: 'error',
            });
        },
        addSuccessefullyDeletedMessage(count) {
            this.add({
                text: i18n.global.t('messages.Successefully deleted', { count: count }),
                color: 'success',
            });
        },
    },
});
