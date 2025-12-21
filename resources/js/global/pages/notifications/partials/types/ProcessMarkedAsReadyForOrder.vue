<script setup>
import { useI18n } from "vue-i18n";
import InertiaLink from "@/core/components/inertia/InertiaLink.vue";

defineProps({
    data: Object,
});

const { t } = useI18n();
</script>

<template>
    <p>
        {{ t("notifications.New product for order received") }}:

        <InertiaLink
            class="text-primary"
            :link="
                route('pld.ready-for-order-processes.index', {
                    'id[]': data.process_id,
                    initialize_from_inertia_page: true,
                })
            "
        >
            <strong>{{ data.full_english_product_label }}</strong>
        </InertiaLink>
    </p>

    <p>
        {{ t("Manufacturer") }}: {{ data.manufacturer }}<br />
        {{ t("Country") }}: {{ data.country }}<br />
        {{ t("Marketing Authorization Holder") }}:
        {{ data.marketing_authorization_holder }}
    </p>
</template>
