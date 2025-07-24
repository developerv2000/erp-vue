<script setup>
import TableTop from "./partials/TableTop.vue";
import { usePage } from "@inertiajs/vue3";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import MiniAva from "@/core/components/misc/MiniAva.vue";

const page = usePage();

const headers = page.props.tableVisibleHeaders;
const records = page.props.records.data;
</script>

<template>
    <v-data-table
        :headers="headers"
        :items="records"
        :show-select="true"
        :cell-props="{ class: 'pa-2 text-break', style: {'vertical-align' : 'top'} }"
        :header-props="{ class: 'pa-2 text-truncate' }"
    >
        <!-- Top slot -->
        <template #top>
            <TableTop />
        </template>

        <!-- Loading slot -->
        <template v-slot:loading>
            <v-skeleton-loader type="table-row@10"></v-skeleton-loader>
        </template>

        <!-- Item slots -->
        <template v-slot:item.edit="{ item }">
            <TdEditButton :link="route('mad.manufacturers.edit', item.id)" />
        </template>

        <template v-slot:item.bdm.name="{ item }">
            <MiniAva :user="item.bdm" />
        </template>

        <template v-slot:item.analyst.name="{ item }">
            <MiniAva :user="item.analyst" />
        </template>

        <template v-slot:item.status="{ item }">
            <span>{{ item.active ? "Active" : "Inactive" }}</span>
        </template>

        <template v-slot:item.zones.name="{ item }">
            <span>{{ item.zones.map((zone) => zone.name).join(", ") }}</span>
        </template>
    </v-data-table>
</template>
