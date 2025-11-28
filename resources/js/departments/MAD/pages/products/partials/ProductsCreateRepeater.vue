<script setup>
import { useI18n } from "vue-i18n";
import { debounce } from "@/core/scripts/utilities";
import { normalizeSpecificInput } from "@/core/scripts/utilities";

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

// Dosage & pack normalization
const normalizeInputDebounced = debounce((value, field, key) => {
    field.value[key] = normalizeSpecificInput(value);
}, 300);
</script>

<template>
    <DefaultSheet>
        <DefaultTitle>{{ t("Multiple records") }}</DefaultTitle>

        <div v-if="fields.length" class="mb-5">
            <v-slide-y-transition tag="div" group>
                <v-row v-for="(field, index) in fields" :key="field.key">
                    <v-col>
                        <DefaultTextField
                            v-model="field.value.dosage"
                            :label="t('fields.Dosage')"
                            @update:modelValue="
                                (val) =>
                                    normalizeInputDebounced(
                                        val,
                                        field,
                                        'dosage'
                                    )
                            "
                            required
                        />
                    </v-col>

                    <v-col>
                        <DefaultTextField
                            v-model="field.value.pack"
                            :label="t('fields.Pack')"
                            @update:modelValue="
                                (val) =>
                                    normalizeInputDebounced(val, field, 'pack')
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
                            class="mt-6"
                            color="error"
                            size="small"
                            :icon="mdiClose"
                            @click="remove(index)"
                        />
                    </v-col>
                </v-row>
            </v-slide-y-transition>
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
