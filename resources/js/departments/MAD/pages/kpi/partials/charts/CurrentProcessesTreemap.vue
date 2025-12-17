<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import { useMADKPIStore } from "@/departments/MAD/stores/kpi";
import { useUserSettingsStore } from "@/core/stores/userSettings";
import {
    defaultTextStyleOptions,
    defaultTitletOptions,
    treemapDefaultSeriesOptions,
    treemapDefaultToolboxOptions,
} from "@/core/boot/echarts";

const page = usePage();
const { t } = useI18n();
const store = useMADKPIStore();
const userSettings = useUserSettingsStore();
const countries = computed(() => page.props.kpiData.countries);

const series = computed(() => {
    return {
        ...treemapDefaultSeriesOptions,
        breadcrumb: { show: false }, // Hide the breadcrumbs
        data: countries.value.map((country) => {
            country.value = country.year_processes_count;
            return country;
        }),
    };
});

const option = computed(() => {
    return {
        textStyle: defaultTextStyleOptions,
        title: {
            ...defaultTitletOptions,
            text: t("charts.MAD KPI processes counts of countries"),
        },
        toolbox: treemapDefaultToolboxOptions,
        tooltip: {
            formatter: ({ name, value, data }) =>
                `<strong>${name}</strong><br>${t("calculations.Sum")}: ${value}<br>` +
                Object.entries(data.statuses)
                    .map(([key, val]) => `${key}: ${val.processes_count}`)
                    .join("<br>"),
        },
        series: [series.value],
    };
});
</script>

<template>
    <v-sheet height="580px">
        <VChart
            :option="option"
            :loading="store.loading"
            :theme="userSettings.echartsTheme"
            :key="userSettings.echartsTheme"
            autoresize
        />
    </v-sheet>
</template>

