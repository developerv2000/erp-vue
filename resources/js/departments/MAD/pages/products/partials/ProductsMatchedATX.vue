<script setup>
import { useI18n } from "vue-i18n";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";

defineProps({
    values: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();
</script>

<template>
    <DefaultSheet>
        <DefaultTitle>{{ t("products.Matched ATX") }}</DefaultTitle>

        <v-row>
            <v-col cols="4">
                <DefaultTextField
                    :label="t('fields.ATX')"
                    v-model="values.atx_name"
                    :error-messages="errors.atx_name"
                    required
                />
            </v-col>

            <v-col cols="4">
                <DefaultTextField
                    :label="t('fields.Our ATX')"
                    v-model="values.atx_short_name"
                />
            </v-col>
        </v-row>
    </DefaultSheet>
</template>
