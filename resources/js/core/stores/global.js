import { defineStore } from "pinia";
import { useNotificationsTableStore } from "@/global/stores/notifications";
import { useMADProcessesTableStore } from "@/departments/MAD/stores/processesTable";
import { useProcessStatusHistoryStore } from "@/departments/MAD/stores/processStatusHistoryTable";
import { useMADProductsTableStore } from "@/departments/MAD/stores/productsTable";
import { useAttachmentsStore } from "@/global/stores/attachments";
import { useCommentsStore } from "@/global/stores/comments";
import { useMADManufacturersTableStore } from "@/departments/MAD/stores/manufacturersTable";
import { useAdministrationUsersTableStore } from "@/administration/stores/usersTable";
import { useMADKPIStore } from "@/departments/MAD/stores/kpi";
import useAuth from "../composables/useAuth";
import axios from "axios";

const { canReceiveNotifications } = useAuth();

export const useGlobalStore = defineStore("global", {
    state: () => ({
        loading: false,
        unreadNotificationsCount: 0,
        initialized: false,
    }),

    actions: {
        initFromInertiaPage(page) {
            // Return if already initialized
            if (this.initialized) return

            // Mark as initialized
            this.initialized = true;

            if (canReceiveNotifications()) {
                // Check for unread notifications
                this.checkForUnreadNotifications();

                // Check for unread notifications each 2 minutes
                this._interval = setInterval(() => {
                    this.checkForUnreadNotifications()
                }, 120 * 1000);
            }
        },

        checkForUnreadNotifications() {
            axios
                .get(route("api.notifications.unread-count"))
                .then((response) => {
                    const newCount = response.data;

                    // Detect newly arrived notifications
                    if (newCount > this.unreadNotificationsCount) {
                        this.playNotificationSound();
                    }

                    this.unreadNotificationsCount = newCount
                });
        },
        getAllResetableStores() {
            // Fetch all resetable Pinia stores:

            // Global
            const notificationsTableStore = useNotificationsTableStore();
            const attachmentsStore = useAttachmentsStore();
            const commentsStore = useCommentsStore();

            // Administration
            const usersTableStore = useAdministrationUsersTableStore();

            // MAD
            const manufacturersTableStore = useMADManufacturersTableStore();
            const productsTableStore = useMADProductsTableStore();
            const processesTableStore = useMADProcessesTableStore();
            const processStatusHistoryStore = useProcessStatusHistoryStore();
            const kpiStore = useMADKPIStore();

            return [
                // Global
                notificationsTableStore,
                attachmentsStore,
                commentsStore,

                // Administration
                usersTableStore,

                // MAD
                manufacturersTableStore,
                productsTableStore,
                processesTableStore,
                processStatusHistoryStore,
                kpiStore,
            ];
        },

        resetAllStores() {
            // Reset all resetable Pinia stores
            const stores = this.getAllResetableStores();

            stores.forEach((store) => {
                store.resetState();
            });

            // Reset self
            this.resetState();
        },

        resetState() {
            this.unreadNotificationsCount = 0;
            this.initialized = false;
        },

        playNotificationSound() {
            const audio = new Audio('/audio/notification.mp3')
            audio.play()
        }
    }
})
