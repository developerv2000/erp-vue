<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import PageIntro from "@/core/layouts/PageIntro.vue";
import { computed } from "vue";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";
import { usePage } from "@inertiajs/vue3";
import InertiaLinkedButton from "@/core/components/inertia/InertiaLinkedButton.vue";
import { mdiPlus } from "@mdi/js";

const { t } = useI18n();
const title = computed(() => t("pages.EPP"));
const page = usePage();

const headers = page.props.visibleTableColumns;
const records = page.props.records.data;
const totalRecords = page.props.records.total;

const breadcrumbs = computed(() => [
    {
        title: t("pages.Main"),
        link: route("home"),
    },

    {
        title: title.value,
    },
]);
</script>

<template>
    <MainLayout :title="title">
        <PageIntro>
            <template #breadcrumbs>
                <InertiaBreadcrumbs :breadcrumbs="breadcrumbs" />
            </template>

            <template #title>{{ title }}</template>
        </PageIntro>

        <v-data-table :headers="headers" :items="records">
            <template #top>
                <v-toolbar
                    :title="'Filtered records — ' + totalRecords"
                    color="surface"
                    class="border-b-sm pr-5"
                >
                    <InertiaLinkedButton
                        :link="route('mad.manufacturers.create')"
                        :prepend-icon="mdiPlus"
                        color="success"
                        size="default"
                    >
                        New
                    </InertiaLinkedButton>
                </v-toolbar>
            </template>
        </v-data-table>
    </MainLayout>
</template>
