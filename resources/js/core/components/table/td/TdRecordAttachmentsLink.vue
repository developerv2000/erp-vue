<script setup>
import { useI18n } from "vue-i18n";
import TdArrowedInertiaLink from "./TdArrowedInertiaLink.vue";

defineProps({
    record: Object,
});

const { t } = useI18n();
</script>

<template>
    <TdArrowedInertiaLink
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
    </TdArrowedInertiaLink>
</template>
