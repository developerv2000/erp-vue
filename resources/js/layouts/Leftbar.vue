<script setup>
import { ref } from "vue";
import useAuth from "@/composables/useAuth";
import { mdiChevronLeft } from "@mdi/js";

const rail = ref(false);
</script>

<template>
    <v-navigation-drawer :rail="rail" @click="rail = false" permanent>
        <v-list>
            <v-list-item
                prepend-avatar="/images/main/favicon-large.png"
                class="logo-item"
                title="EVOLET"
            >
                <template #append>
                    <v-btn
                        :icon="mdiChevronLeft"
                        @click.stop="rail = !rail"
                        variant="text"
                    >
                    </v-btn>
                </template>
            </v-list-item>
        </v-list>
    </v-navigation-drawer>
</template>

<style scoped>
.logo-item::v-deep(.v-avatar) {
    border-radius: 0 !important;
}

.logo-item::v-deep(.v-list-item-title) {
    font-size: 22px;
    font-weight: 500;
    letter-spacing: 2px;
}

.v-navigation-drawer:not(.v-navigation-drawer--rail)
    .logo-item::v-deep(.v-avatar.v-avatar--density-default) {
    width: 30px;
    height: 30px;
}
</style>
