<script setup>
import { useI18n } from "vue-i18n";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";

defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();
</script>

<template>
    <DefaultSheet>
        <DefaultTitle>{{ t("product.About") }}</DefaultTitle>

        <v-table density="compact" striped="odd">
            <tbody>
                <tr>
                    <td>{{ t("fields.Manufacturer") }}</td>
                    <td>{{ product.manufacturer.name }}</td>
                </tr>

                <tr>
                    <td>{{ t("fields.Manufacturer country") }}</td>
                    <td>{{ product.manufacturer.country.name }}</td>
                </tr>

                <tr>
                    <td>{{ t("fields.Generic") }}</td>
                    <td>{{ product.inn.name }}</td>
                </tr>

                <tr>
                    <td>{{ t("fields.Form") }}</td>
                    <td>{{ product.form.name }}</td>
                </tr>

                <tr>
                    <td>{{ t("fields.Dosage") }}</td>
                    <td>{{ product.dosage }}</td>
                </tr>

                <tr>
                    <td>{{ t("fields.Pack") }}</td>
                    <td>{{ product.pack }}</td>
                </tr>

                <tr>
                    <td>{{ t("fields.Analyst") }}</td>
                    <td>{{ product.manufacturer.analyst.name }}</td>
                </tr>

                <tr>
                    <td>{{ t("fields.BDM") }}</td>
                    <td>{{ product.manufacturer.bdm.name }}</td>
                </tr>
            </tbody>
        </v-table>
    </DefaultSheet>
</template>
