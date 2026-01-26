<script setup>
import { useI18n } from "vue-i18n";
import InertiaLink from "@/core/components/inertia/InertiaLink.vue";

const props = defineProps({
    data: Object,
});

const { t } = useI18n();
</script>

<template>
    <p>
        {{ t("notifications.New import type invoice for payment received") }}:

        <InertiaLink
            class="text-primary"
            :link="
                route('import.invoices.index', {
                    'id[]': data.invoice_id,
                    initialize_from_inertia_page: true,
                })
            "
        >
            <strong># {{ data.invoice_id }}</strong>
        </InertiaLink>
    </p>

    <p>
        {{ t("Shipment") }}: <strong>{{ data.shipment_title }}</strong
        ><br />
        {{ t("Products") }}: {{ data.shipment_products_count }}<br />
    </p>
</template>
