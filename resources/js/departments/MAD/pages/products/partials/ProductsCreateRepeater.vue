<script setup>
import { useI18n } from "vue-i18n";
import { debounce } from "@/core/scripts/utilities";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import DefaultNumberInput from "@/core/components/form/inputs/DefaultNumberInput.vue";
import { mdiClose, mdiPlus } from "@mdi/js";

const props = defineProps({
    fields: { type: Array, required: true },
    push: { type: Function, required: true },
    remove: { type: Function, required: true },
});

const { t } = useI18n();

// Default product structure
const newProduct = () => ({
    dosage: "",
    pack: "",
    moq: null,
});

// Add a new product row
const addProduct = () => props.push(newProduct());

// Validation & formatting function
function validateInput(value) {
    if (!value) return "";

    return (
        value
            // Add spaces before and after certain symbols
            .replace(/([+%/*])/g, " $1 ")
            // Replace consecutive whitespaces with a single space
            .replace(/\s+/g, " ")
            // Separate letters from numbers
            .replace(/(\d+)([a-zA-Z]+)/g, "$1 $2")
            .replace(/([a-zA-Z]+)(\d+)/g, "$1 $2")
            // Remove non-English characters
            .replace(/[^a-zA-Z0-9\s!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/g, "")
            // Replace symbols ',' with '.'
            .replace(/,/g, ".")
            // Trim spaces
            .trim()
            // Convert the entire string to uppercase
            .toUpperCase()
    );
}

// Debounced version to reduce reactivity overhead
const validateInputDebounced = debounce((value, field, key) => {
    field.value[key] = validateInput(value);
}, 300);
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
                        @update:modelValue="
                            (val) =>
                                validateInputDebounced(val, field, 'dosage')
                        "
                    />
                </v-col>

                <v-col>
                    <DefaultTextField
                        v-model="field.value.pack"
                        :label="t('fields.Pack')"
                        required
                        @update:modelValue="
                            (val) => validateInputDebounced(val, field, 'pack')
                        "
                    />
                </v-col>

                <v-col>
                    <DefaultNumberInput
                        v-model="field.value.moq"
                        :label="t('fields.MOQ')"
                        :min="0"
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
