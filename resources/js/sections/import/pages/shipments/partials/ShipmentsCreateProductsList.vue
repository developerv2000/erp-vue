<script setup>
import { useI18n } from "vue-i18n";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultNumberInput from "@/core/components/form/inputs/DefaultNumberInput.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";

defineProps({
    fields: { type: Array, required: true },
    errors: { type: Object, required: true },
    replace: { type: Function, required: true },
});

const { t } = useI18n();
</script>

<template>
    <DefaultSheet v-if="fields.length > 0">
        <DefaultTitle>{{ t("Products") }}</DefaultTitle>

        <div>
            <v-row v-for="field in fields" :key="field.key">
                <div class="d-flex align-center mr-2">
                    <v-checkbox-btn
                        v-model="field.value.checked"
                        color="primary"
                    />
                </div>

                <v-col>
                    <DefaultTextField
                        :label="t('fields.TM Eng')"
                        v-model="field.value.label"
                        :clearable="false"
                        readonly
                    />
                </v-col>

                <v-col>
                    <DefaultNumberInput
                        :label="t('fields.Quantity')"
                        v-model="field.value.produced_by_manufacturer_quantity"
                        :min="0"
                        :required="field.value.checked"
                    />
                </v-col>
            </v-row>
        </div>
    </DefaultSheet>
</template>
