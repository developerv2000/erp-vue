import { defineStore } from "pinia";
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
