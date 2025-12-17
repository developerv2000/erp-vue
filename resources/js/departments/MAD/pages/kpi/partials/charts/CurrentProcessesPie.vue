<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import { useMADKPIStore } from "@/departments/MAD/stores/kpi";
import { useUserSettingsStore } from "@/core/stores/userSettings";
import {
    defaultTextStyleOptions,
    defaultTitletOptions,
    defaulLegendOptions,
    arcPieDefaultSeriesOptions,
    arcPieDefaultToolboxOptions,
    arcPieDefaultEmphasisOptions,
    arcPieDefaultTooltipOptions,
} from "@/core/boot/echarts";

const page = usePage();
const { t } = useI18n();
const store = useMADKPIStore();
const userSettings = useUserSettingsStore();

const option = computed(() => {
    return {
        textStyle: defaultTextStyleOptions,
        title: {
            ...defaultTitletOptions,
            text: t("charts.MAD KPI current processes count"),
        },
        toolbox: arcPieDefaultToolboxOptions,
        tooltip: arcPieDefaultTooltipOptions,
        emphasis: arcPieDefaultEmphasisOptions,
        label: {
            formatter: (params) => {
                return `${params.name}: ${params.value} (${params.percent}%)`;
            },
        },
        legend: defaulLegendOptions,
        series: [
            {
                ...arcPieDefaultSeriesOptions,
                data: page.props.kpiData.generalStatuses.map((status) => ({
                    name: status.name,
                    value: status.year_current_processes_count,
                })),
            },
        ],
    };
});
</script>

<template>
    <v-sheet width="50%" height="400px">
        <VChart
            :option="option"
            :loading="store.loading"
            :theme="userSettings.echartsTheme"
            :key="userSettings.echartsTheme"
            autoresize
        />
    </v-sheet>
</template>
