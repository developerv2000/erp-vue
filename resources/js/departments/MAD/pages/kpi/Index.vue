<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { usePage } from "@inertiajs/vue3";
import { useMADKPIStore } from "@/departments/MAD/stores/kpi";
import { computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import useQueryParams from "@/core/composables/useQueryParams";
import KPIFilter from "./partials/KPIFilter.vue";

const page = usePage();
const { t } = useI18n();
const store = useMADKPIStore();
const { get } = useQueryParams();

const title = computed(() => t("pages.KPI") + " — " + page.props.kpiData.year);

onMounted(() => {
    // Init store from inertia page if needed
    if (
        !store.initializedFromInertiaPage ||
        get("initialize_from_inertia_page")
    ) {
        store.initFromInertiaPage(page);
    }
});
</script>

<template>
    <MainLayout :title="title" :display-title-at-header="true">
        <div class="d-flex ga-6 align-start">
            <div class="d-flex flex-column flex-grow-1 ga-6"></div>

            <KPIFilter />
        </div>
    </MainLayout>
</template>
