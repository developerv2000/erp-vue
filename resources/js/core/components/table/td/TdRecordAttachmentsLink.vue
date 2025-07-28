<script setup>
import InertiaLinkedButton from "../../inertia/InertiaLinkedButton.vue";
import { mdiArrowRight } from "@mdi/js";

defineProps({
    record: Object,
});
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
        color="orange"
        class="mb-1 font-weight-regular"
        variant="tonal"
    >
        {{ record.attachments_count }} attachments
    </InertiaLinkedButton>
    <br>
</template>
