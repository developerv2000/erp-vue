<script setup>
import InertiaLinkedButton from "../../inertia/InertiaLinkedButton.vue";
import { mdiArrowRight } from "@mdi/js";
import { useI18n } from "vue-i18n";

defineProps({
    record: Object,
});

const { t } = useI18n();
</script>

<template>
    <InertiaLinkedButton
        :append-icon="mdiArrowRight"
        :link="
            route('attachments.view-model-attachments', {
                attachable_type: record.base_model_class,
                attachable_id: record.id,
            })
        "
        size="small"
        class="mb-1 font-weight-regular"
        color="grey-lighten-2"
        variant="flat"
    >
        {{ record.attachments_count }}
        <span class="text-lowercase">{{ t("Attachments") }}</span>
    </InertiaLinkedButton>
    <br>
</template>
