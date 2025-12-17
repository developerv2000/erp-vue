<script setup>
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";

const page = usePage();
const { t } = useI18n();

const months = computed(() => page.props.kpiData.months);
const generalStatuses = computed(() => page.props.kpiData.generalStatuses);
</script>

<template>
    <div>
        <DefaultTableToolbar>
            <template #title>
                {{ t("charts.MAD KPI maximum processes count") }}
            </template>
        </DefaultTableToolbar>

        <v-table density="compact">
            <thead>
                <tr>
                    <th>{{ t("Status") }}</th>

                    <th v-for="month in months" :key="month.id">
                        {{ t(month.name) }}
                    </th>

                    <th>{{ t("calculations.Sum") }}</th>
                </tr>
            </thead>

            <tbody>
                <!-- General status rows -->
                <tr v-for="status in generalStatuses" :key="status.id">
                    <td>{{ status.name }}</td>

                    <td v-for="month in status.months" :key="month.id">
                        {{ month.maximum_processes_count }}
                    </td>

                    <td>{{ status.year_maximum_processes_count }}</td>
                </tr>

                <!-- Total row -->
                <tr>
                    <td>{{ t("calculations.Sum") }}</td>

                    <td v-for="month in months" :key="month.id">
                        {{ month.sum_of_all_maximum_process_count }}
                    </td>

                    <td>
                        {{ page.props.kpiData.maximumProcessesCountOfYear }}
                    </td>
                </tr>
            </tbody>
        </v-table>
    </div>
</template>
