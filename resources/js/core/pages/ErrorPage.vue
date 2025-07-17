<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import {
    mdiUpdate,
    mdiAlertCircle,
    mdiMagnifyClose,
    mdiLockAlert,
    mdiAlert,
} from "@mdi/js";

const props = defineProps({
    status: Number,
});

const title = computed(() => {
    return (
        {
            503: "503: Service Unavailable",
            500: "500: Server Error",
            404: "404: Page Not Found",
            403: "403: Forbidden",
        }[props.status] || `${props.status}: Error`
    );
});

const description = computed(() => {
    return (
        {
            503: "Sorry, we are doing some maintenance. Please check back soon.",
            500: "Whoops, something went wrong on our servers.",
            404: "Sorry, the page you are looking for could not be found.",
            403: "Sorry, you are forbidden from accessing this page.",
        }[props.status] || "An unexpected error occurred."
    );
});

const icon = computed(() => {
    return (
        {
            503: mdiUpdate,
            500: mdiAlertCircle,
            404: mdiMagnifyClose,
            403: mdiLockAlert,
        }[props.status] || mdiAlert
    );
});
</script>

<template>
    <v-app>
        <v-main>
            <div
                class="d-flex align-center justify-center h-100 pa-4"
                style="background: linear-gradient(135deg, #f5f7fa, #c3cfe2)"
            >
                <v-card class="elevation-10 pa-6 rounded-xl" max-width="500">
                    <div class="text-center">
                        <v-icon
                            :icon="icon"
                            size="64"
                            color="primary"
                            class="mb-4"
                        />

                        <h1 class="text-h5 font-weight-bold mb-2">
                            {{ title }}
                        </h1>
                        
                        <p class="text-body-1 mb-6">{{ description }}</p>

                        <Link href="/">
                            <v-btn color="primary" variant="flat" rounded>
                                Go to Home
                            </v-btn>
                        </Link>
                    </div>
                </v-card>
            </div>
        </v-main>
    </v-app>
</template>
