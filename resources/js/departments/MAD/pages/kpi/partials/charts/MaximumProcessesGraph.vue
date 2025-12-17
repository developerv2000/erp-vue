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
    graphDefaultGridOptions,
    graphDefaultTooltipOptions,
    graphDefaultToolboxOptions,
    graphDefaultXAxisPointerOptions,
    gprahLineSeriesDefaultLabelOptions,
    graphBarSeriesDefaultLabelOptions,
} from "@/core/boot/echarts";

const page = usePage();
const { t } = useI18n();
const store = useMADKPIStore();
const userSettings = useUserSettingsStore();
const months = computed(() => page.props.kpiData.months);
const generalStatuses = computed(() => page.props.kpiData.generalStatuses);

const lineSeries = computed(() => {
    return [
        {
            name: t("calculations.Sum"),
            type: "line",
            z: 1, // set lines under the bars
            data: months.value.map(
                (month) => month.sum_of_all_maximum_process_count
            ),
            label: gprahLineSeriesDefaultLabelOptions,
        },
    ];
});

const barSeries = computed(() => {
    return generalStatuses.value.map((status) => ({
        name: status.name,
        type: "bar",
        z: 2, // set bars over the lines
        data: Object.values(status.months).map(
            (month) => month.maximum_processes_count
        ),
        label: graphBarSeriesDefaultLabelOptions,
    }));
});

const option = computed(() => {
    return {
        textStyle: defaultTextStyleOptions,
        title: {
            ...defaultTitletOptions,
            text: t("charts.MAD KPI maximum processes count"),
        },
        grid: graphDefaultGridOptions,
        toolbox: graphDefaultToolboxOptions,
        tooltip: graphDefaultTooltipOptions,
        legend: defaulLegendOptions,
        xAxis: [
            {
                type: "category",
                data: months.value.map((month) => t(month.name)),
                axisPointer: graphDefaultXAxisPointerOptions,
            },
        ],
        yAxis: [
            {
                type: "value",
            },
        ],
        series: [...barSeries.value, ...lineSeries.value],
    };
});
</script>

<template>
    <v-sheet height="720px">
        <VChart
            :option="option"
            :loading="store.loading"
            :theme="userSettings.echartsTheme"
            :key="userSettings.echartsTheme"
            autoresize
        />
    </v-sheet>
</template>
