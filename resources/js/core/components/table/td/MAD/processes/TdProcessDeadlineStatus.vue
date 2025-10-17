<script setup>
import { useI18n } from "vue-i18n";
import TdChip from "../../TdChip.vue";
import { computed } from "vue";

const props = defineProps({
    record: Object,
});

const { t } = useI18n();

const color = computed(() => {
    switch (props.record.deadline_status) {
        case "Stopped":
            return "grey-lighten-3";
        case "No deadline":
            return "light-green-accent-1";
        case "Not expired":
            return "orange-lighten-3";
        case "Expired":
            return "pink-lighten-3";
    }
});
</script>

<template>
    <TdChip class="mb-1" :color="color">
        {{ record.deadline_status }}
    </TdChip>

    <br />

    <span class="mt-1 text-lowercase">
        <template v-if="record.deadline_status == 'Not expired'">
            {{ record.days_past_since_last_activity }} {{ t("days.past") }}
        </template>

        <template v-if="record.deadline_status == 'Expired'">
            {{ record.days_past_since_last_activity - 15 }} {{ t("Days") }}
        </template>
    </span>
</template>
