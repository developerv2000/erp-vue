<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import { useMADKPIStore } from "@/departments/MAD/stores/kpi";
import { useUserSettingsStore } from "@/core/stores/userSettings";
import {
    defaultTextStyleOptions,
    defaultTitletOptions,
    graphDefaultGridOptions,
    graphDefaultTooltipOptions,
    graphDefaultToolboxOptions,
    graphDefaultXAxisPointerOptions,
    graphBarSeriesDefaultLabelOptions,
} from "@/core/boot/echarts";

const page = usePage();
const { t } = useI18n();
const store = useMADKPIStore();
const userSettings = useUserSettingsStore();
const months = computed(() => page.props.kpiData.months);

const series = computed(() => {
    return {
        type: "bar",
        showBackground: true,
        data: months.value.map((month) => month.active_manufacturers_count),
        label: {
            ...graphBarSeriesDefaultLabelOptions,
            rotate: 0,
        },
    };
});

const option = computed(() => {
    return {
        textStyle: defaultTextStyleOptions,
        title: {
            ...defaultTitletOptions,
            text: t("charts.MAD KPI active manufacturers count"),
        },
        grid: {
            ...graphDefaultGridOptions,
            bottom: 40,
            top: 88,
        },
        toolbox: graphDefaultToolboxOptions,
        tooltip: graphDefaultTooltipOptions,
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
        series: [series.value],
    };
});
</script>

<template>
    <v-sheet height="520px">
        <VChart
            :option="option"
            :loading="store.loading"
            :theme="userSettings.echartsTheme"
            :key="userSettings.echartsTheme"
            autoresize
        />
    </v-sheet>
</template>

