<script setup>
import { useI18n } from "vue-i18n";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import InertiaLink from "@/core/components/inertia/InertiaLink.vue";

defineProps({
    records: {
        type: Array,
        required: true,
    },
});

const { t } = useI18n();
</script>

<template>
    <DefaultSheet>
        <DefaultTitle margin-bottom="1">{{
            t("Similar records")
        }}</DefaultTitle>

        <p v-if="records.length == 0">
            {{ t("similar records.Not found") }}
        </p>

        <v-card v-else>
            <v-list lines="1">
                <v-list-item v-for="(record, index) in records" :key="index">
                    <InertiaLink
                        class="text-primary"
                        :link="
                            route('mad.products.index', {
                                'id[]': record.id,
                                'initialize_from_inertia_page': true,
                             })
                        "
                    >
                        #{{ record.id }} {{ record.form.name }}
                        {{ record.dosage }} {{ record.pack }}
                    </InertiaLink>
                </v-list-item>
            </v-list>
        </v-card>
    </DefaultSheet>
</template>
