<script setup>
import { useI18n } from "vue-i18n";
import { usePage } from "@inertiajs/vue3";
import { debounce } from "@/core/scripts/utilities";
import { normalizeSpecificInput } from "@/core/scripts/utilities";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultNumberInput from "@/core/components/form/inputs/DefaultNumberInput.vue";

defineProps({
    values: { type: Object, required: true },
    errors: { type: Object, required: true },
});

const page = usePage();
const { t } = useI18n();

// Dosage & pack normalization
const normalizeInputDebounced = debounce((newValue, values, key) => {
    values[key] = normalizeSpecificInput(newValue);
}, 300);
</script>

<template>
    <DefaultSheet>
        <DefaultTitle>{{ t("Product") }}</DefaultTitle>

        <v-row>
            <v-col cols="4">
                <DefaultAutocomplete
                    :label="t('fields.Form')"
                    :items="page.props.productForms"
                    v-model="values.product_form_id"
                    :error-messages="errors.product_form_id"
                    required
                />
            </v-col>

            <v-col cols="4">
                <DefaultTextField
                    :label="t('fields.Dosage')"
                    v-model="values.product_dosage"
                    :error-messages="errors.product_dosage"
                    @update:modelValue="
                        (newValue) => normalizeInputDebounced(newValue, values, 'product_dosage')
                    "
                />
            </v-col>

            <v-col cols="4">
                <DefaultTextField
                    :label="t('fields.Pack')"
                    v-model="values.product_pack"
                    :error-messages="errors.product_pack"
                    @update:modelValue="
                        (newValue) => normalizeInputDebounced(newValue, values, 'product_pack')
                    "
                />
            </v-col>

            <v-col cols="4">
                <DefaultAutocomplete
                    :label="t('fields.Shelf life')"
                    :items="page.props.shelfLifes"
                    v-model="values.product_shelf_life_id"
                    :error-messages="errors.product_shelf_life_id"
                    required
                />
            </v-col>

            <v-col cols="4">
                <DefaultAutocomplete
                    :label="t('fields.Product class')"
                    :items="page.props.productClasses"
                    v-model="values.product_class_id"
                    :error-messages="errors.product_class_id"
                    required
                />
            </v-col>

            <v-col cols="4">
                <DefaultNumberInput
                    :label="t('fields.MOQ')"
                    v-model="values.product_moq"
                    :error-messages="errors.product_moq"
                    :min="0"
                />
            </v-col>
        </v-row>
    </DefaultSheet>
</template>
