<script setup>
import GuestLayout from "@/layouts/GuestLayout.vue";
import { mdiCheckDecagram } from "@mdi/js";
import WrappedLabelGroup from "@/components/form/groups/WrappedLabelGroup.vue";

function login() {
    alert("Login");
}
</script>

<template>
    <GuestLayout>
        <v-sheet width="460" elevation="1" class="px-8 pt-8 pb-10">
            <v-img
                :width="180"
                src="/images/main/logo-dark.svg"
                class="mx-auto mb-2"
            ></v-img>

            <v-card
                class="card"
                variant="text"
                title="Sign in"
                subtitle="Please sign in to your account"
                :prepend-icon="mdiCheckDecagram"
            >
                <v-form @submit.prevent="login" class="d-flex flex-column mt-4">
                    <WrappedLabelGroup label="Email" :required="true">
                        <v-text-field
                            color="lime-accent-4"
                            name="email"
                            variant="outlined"
                            density="compact"
                            type="email"
                            required
                        />
                    </WrappedLabelGroup>

                    <WrappedLabelGroup label="Password" :required="true">
                        <v-text-field
                            class="ml-auto"
                            color="lime-accent-4"
                            name="password"
                            variant="outlined"
                            density="compact"
                            type="password"
                            required
                        />
                    </WrappedLabelGroup>

                    <v-btn class="mt-2" color="lime-accent-4" type="submit"> Login </v-btn>
                </v-form>
            </v-card>
        </v-sheet>
    </GuestLayout>
</template>

<style scoped>
.card ::v-deep(.v-card-item) {
    padding: 0 !important;
}
</style>
