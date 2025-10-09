<script setup>
import { useI18n } from "vue-i18n";
import InertiaLink from "../../inertia/InertiaLink.vue";
import { mdiArrowRight } from "@mdi/js";

defineProps({
    record: Object,
});

const { t } = useI18n();
</script>

<template>
    <InertiaLink
        class="d-flex ga-2 mb-2 text-high-emphasis"
        :link="
            route('attachments.view-model-attachments', {
                attachable_type: record.base_model_class,
                attachable_id: record.id,
            })
        "
    >
        <span class="text-lowercase"
            >{{ record.attachments_count }} {{ t("Attachments") }}</span
        >
        <v-icon :icon="mdiArrowRight" />
    </InertiaLink>
</template>
