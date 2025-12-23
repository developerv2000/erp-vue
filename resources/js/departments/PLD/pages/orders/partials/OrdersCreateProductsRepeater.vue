<script setup>
import { useI18n } from "vue-i18n";
import { usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import axios from "axios";
import { useMessagesStore } from "@/core/stores/messages";
import { useGlobalStore } from "@/core/stores/global";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import DefaultNumberInput from "@/core/components/form/inputs/DefaultNumberInput.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import { mdiClose, mdiPlus } from "@mdi/js";

const props = defineProps({
    formValues: { type: Object, required: true },
    productsFields: { type: Array, required: true },
    push: { type: Function, required: true },
    remove: { type: Function, required: true },
});

const page = usePage();
const { t } = useI18n();
const readyForOrderProcesses = ref([]);
const messages = useMessagesStore();
const globalStore = useGlobalStore();

/**
 * Default product structure
 */
const newProduct = () => ({
    ready_for_order_process_id: null,
    process_id: null,
    mah_options: [],
    quantity: null,
    comment: null,
    serialization_type_id: null,
});

/**
 * Fetch 'ready for order processes' for 'TM Eng' field
 */
const fetchReadyForOrderProcesses = async () => {
    if (globalStore.loading) return;

    globalStore.loading = true;

    try {
        const response = await axios.get(
            route("pld.get-ready-for-order-processes-of-manufacturer", {
                manufacturer_id: props.formValues.manufacturer_id,
                country_id: props.formValues.country_id,
            })
        );

        readyForOrderProcesses.value = response.data ?? [];
    } catch (error) {
        readyForOrderProcesses.value = [];
        messages.addSubmitionFailedMessage();
    } finally {
        globalStore.loading = false;
    }
};

/**
 * Add a new product row
 */
const addProduct = async () => {
    try {
        // Fetch only once (first row)
        if (props.productsFields.length === 0) {
            await fetchReadyForOrderProcesses();
        }

        if (readyForOrderProcesses.value.length === 0) {
            messages.add({
                text: t(
                    "messages.No products found for the given manufacturer and country"
                ),
                color: "error",
            });
            return;
        }

        props.push(newProduct());
    } catch (error) {
        messages.addSubmitionFailedMessage();
    }
};

/**
 * Update MAH options of selected product
 */
const updateMAHOptions = async (field, processId) => {
    if (!processId) {
        field.value.mah_options = [];
        return;
    }

    globalStore.loading = true;

    try {
        const response = await axios.get(
            route("pld.get-process-with-it-similar-records-for-order", {
                process_id: processId,
            })
        );

        field.value.mah_options = response.data ?? [];
    } catch (error) {
        field.value.mah_options = [];
        messages.addSubmitionFailedMessage();
    } finally {
        globalStore.loading = false;
    }
};
</script>

<template>
    <DefaultSheet>
        <DefaultTitle>{{ t("Products") }}</DefaultTitle>

        <div v-if="productsFields.length > 0" class="mb-5">
            <v-slide-y-transition tag="div" group>
                <v-row
                    v-for="(field, index) in productsFields"
                    :key="field.key"
                >
                    <v-col>
                        <DefaultAutocomplete
                            :label="t('fields.TM Eng')"
                            v-model="field.value.ready_for_order_process_id"
                            :items="readyForOrderProcesses"
                            item-title="full_english_product_label_with_id"
                            @update:modelValue="
                                (processId) =>
                                    updateMAHOptions(field, processId)
                            "
                            required
                        />
                    </v-col>

                    <v-col>
                        <DefaultAutocomplete
                            :label="t('fields.MAH')"
                            v-model="field.value.process_id"
                            :items="field.value.mah_options"
                            item-title="mah_name_with_id"
                            required
                        />
                    </v-col>

                    <v-col>
                        <DefaultNumberInput
                            :label="t('fields.Quantity')"
                            v-model="field.value.quantity"
                            :min="0"
                        />
                    </v-col>

                    <v-col>
                        <DefaultTextField
                            :label="t('Comment')"
                            v-model="field.value.comment"
                        />
                    </v-col>

                    <v-col>
                        <DefaultAutocomplete
                            :label="t('fields.Serialization type')"
                            v-model="field.value.serialization_type_id"
                            :items="page.props.serializationTypes"
                            required
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
            :disabled="!formValues.manufacturer_id || !formValues.country_id"
        >
            {{ t("actions.Add") }}
        </DefaultButton>
    </DefaultSheet>
</template>
