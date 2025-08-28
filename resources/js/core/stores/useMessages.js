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
        addCreatedSuccessfullyMessage() {
            this.add({
                text: i18n.global.t('messages.Created successfully'),
                color: 'success',
            });
        },
        addUpdatedSuccessfullyMessage() {
            this.add({
                text: i18n.global.t('messages.Updated successfully'),
                color: 'success',
            });
        },
        addDeletedSuccessefullyMessage(count) {
            this.add({
                text: i18n.global.t('messages.Successefully deleted', { count: count }),
                color: 'success',
            });
        },
        addRestoredSuccessefullyMessage(count) {
            this.add({
                text: i18n.global.t('messages.Successefully restored', { count: count }),
                color: 'success',
            });
        },
        addSubmitionFailedMessage() {
            this.add({
                text: i18n.global.t('messages.Submition failed'),
                color: 'error',
            });
        },
        addFixErrorsMessage() {
            this.add({
                text: i18n.global.t('messages.Fix errors'),
                color: 'error',
            });
        },
    },
});
