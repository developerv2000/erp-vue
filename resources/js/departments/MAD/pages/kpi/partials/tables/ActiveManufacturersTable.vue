<script setup>
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";

const page = usePage();
const { t } = useI18n();

const months = computed(() => page.props.kpiData.months);
</script>

<template>
    <div>
        <DefaultTableToolbar>
            <template #title>
                {{ t("charts.MAD KPI active manufacturers count") }}
            </template>
        </DefaultTableToolbar>

        <v-table density="compact">
            <thead>
                <tr>
                    <th v-for="month in months" :key="month.id">
                        {{ t(month.name) }}
                    </th>

                    <th>{{ t("calculations.Sum") }}</th>
                </tr>
            </thead>

            <tbody>
                <!-- General status rows -->
                <tr>
                    <td v-for="month in months" :key="month.id">
                        {{ month.active_manufacturers_count }}
                    </td>

                    <td>{{ page.props.kpiData.activeManufacturersOfYear }}</td>
                </tr>
            </tbody>
        </v-table>
    </div>
</template>
