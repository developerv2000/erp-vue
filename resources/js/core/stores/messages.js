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
        addDeletedSuccessfullyMessage(count) {
            this.add({
                text: i18n.global.t('messages.Deleted successfully', { count: count }),
                color: 'success',
            });
        },
        addRestoredSuccessfullyMessage(count) {
            this.add({
                text: i18n.global.t('messages.Restored successfully', { count: count }),
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
        addSmartFiltersUpdatedSuccessfullyMessage() {
            this.add({
                text: i18n.global.t('messages.Smart filters updated successfully'),
                color: 'success',
                timeout: 1200
            });
        },
        addSmartFiltersUpdateFailedMessage() {
            this.add({
                text: i18n.global.t('messages.Smart filters update failed'),
                color: 'error',
                timeout: 1200
            });
        },
        addSimilarRecordsUpdatedSuccessfullyMessage() {
            this.add({
                text: i18n.global.t('messages.Similar records updated successfully'),
                color: 'success',
                timeout: 2500
            });
        },
        addSimilarRecordsUpdateFailedMessage() {
            this.add({
                text: i18n.global.t('messages.Similar records update failed'),
                color: 'error',
                timeout: 2500
            });
        },
    },
});
