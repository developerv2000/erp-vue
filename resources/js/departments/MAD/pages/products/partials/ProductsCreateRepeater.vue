<script setup>
import { useI18n } from "vue-i18n";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import { mdiClose, mdiPlus } from "@mdi/js";

const props = defineProps({
    fields: {
        type: Array,
        required: true,
    },
    push: {
        type: Function,
        required: true,
    },
    remove: {
        type: Function,
        required: true,
    },
});

const { t } = useI18n();

// Default product structure
const newProduct = () => ({
    dosage: "",
    pack: "",
    moq: "",
});

// Add a new product row
const addProduct = () => {
    props.push(newProduct());
};
</script>

<template>
    <DefaultSheet>
        <DefaultTitle>{{ t("Multiple records") }}</DefaultTitle>

        <div v-if="fields.length" class="mb-5">
            <v-row v-for="(field, index) in fields" :key="field.key">
                <v-col>
                    <DefaultTextField
                        v-model="field.value.dosage"
                        :label="t('fields.Dosage')"
                        required
                    />
                </v-col>

                <v-col>
                    <DefaultTextField
                        v-model="field.value.pack"
                        :label="t('fields.Pack')"
                        required
                    />
                </v-col>

                <v-col>
                    <DefaultTextField
                        v-model="field.value.moq"
                        :label="t('fields.MOQ')"
                    />
                </v-col>

                <v-col cols="1" class="d-flex align-center justify-center">
                    <v-btn
                        class="mt-4"
                        color="error"
                        size="small"
                        :icon="mdiClose"
                        @click="remove(index)"
                    />
                </v-col>
            </v-row>
        </div>

        <DefaultButton
            color="indigo"
            :prepend-icon="mdiPlus"
            @click="addProduct"
        >
            {{ t("actions.Add") }}
        </DefaultButton>
    </DefaultSheet>
</template>
