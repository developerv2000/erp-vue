<script setup>
import { useI18n } from "vue-i18n";
import InertiaLinkedButton from "../../inertia/InertiaLinkedButton.vue";
import { mdiArrowRight } from "@mdi/js";

defineProps({
    record: Object,
});

const { t } = useI18n();
</script>

<template>
    <InertiaLinkedButton
        class="mb-1 font-weight-regular"
        color="grey-lighten-2"
        variant="flat"
        size="small"
        :append-icon="mdiArrowRight"
        :link="
            route('attachments.view-model-attachments', {
                attachable_type: record.base_model_class,
                attachable_id: record.id,
            })
        "
    >
        {{ record.attachments_count }}
        <span class="text-lowercase">{{ t("Attachments") }}</span>
    </InertiaLinkedButton>
    <br />
</template>
