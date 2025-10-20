<script setup>
import { useI18n } from "vue-i18n";
import { useDateFormat } from "@vueuse/core";

defineProps({
    record: Object,
    arrayKey: Number,
});

const { t } = useI18n();

const backgroundColors = [
    "blue",
    "indigo",
    "purple",
    "pink",
    "yellow",
    "orange",
    "green-accent-4",
    "purple-darken-4",
    "brown-darken-1",
    "grey",
];
</script>

<template>
    <div
        v-if="record.general_statuses_with_periods[arrayKey].duration_days"
        class="d-flex flex-column ga-2"
    >
        <span class="text-lowercase">
            {{ record.general_statuses_with_periods[arrayKey].duration_days }}
            {{ t("Days") }}
        </span>

        <hr
            :style="{
                height: '4px',
                width:
                    record.general_statuses_with_periods[arrayKey]
                        .duration_days_ratio + '%',
            }"
            :class="'border-0 bg-' + backgroundColors[arrayKey]"
        />

        <span
            v-if="
                record.general_statuses_with_periods[arrayKey].start_date &&
                record.general_statuses_with_periods[arrayKey].end_date
            "
        >
            {{
                useDateFormat(
                    record.general_statuses_with_periods[arrayKey].start_date,
                    "DD/MM/YYYY"
                )
            }}
            -
            {{
                useDateFormat(
                    record.general_statuses_with_periods[arrayKey].end_date,
                    "DD/MM/YYYY"
                )
            }}
        </span>
    </div>
</template>
