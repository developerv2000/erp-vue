import { defineStore } from "pinia";

import { useMADProcessesTableStore } from "@/departments/MAD/stores/processesTable";
import { useProcessStatusHistoryStore } from "@/departments/MAD/stores/processStatusHistoryTable";
import { useMADProductsTableStore } from "@/departments/MAD/stores/productsTable";
import { useAttachmentsStore } from "@/global/stores/attachments";
import { useCommentsStore } from "@/global/stores/comments";
import { useMADManufacturersTableStore } from "@/departments/MAD/stores/manufacturersTable";

export const useGlobalStore = defineStore("global", {
    state: () => ({
        loading: false,
    }),

    actions: {
        getAllResetableStores() {
            // Fetch all resetable Pinia stores
            const attachmentsStore = useAttachmentsStore();
            const commentsStore = useCommentsStore();
            const manufacturersTableStore = useMADManufacturersTableStore();
            const processesTableStore = useMADProcessesTableStore();
            const processStatusHistoryStore = useProcessStatusHistoryStore();
            const productsTableStore = useMADProductsTableStore();

            return [
                attachmentsStore,
                commentsStore,
                manufacturersTableStore,
                processesTableStore,
                processStatusHistoryStore,
                productsTableStore,
            ];
        },

        resetAllStores() {
            // Reset all resetable Pinia stores
            const stores = this.getAllResetableStores();

            stores.forEach((store) => {
                store.resetState();
            });
        },
    }
})
