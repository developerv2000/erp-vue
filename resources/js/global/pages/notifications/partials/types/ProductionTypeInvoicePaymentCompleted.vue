<script setup>
import { useI18n } from "vue-i18n";
import InertiaLink from "@/core/components/inertia/InertiaLink.vue";
import useAuth from "@/core/composables/useAuth";

const props = defineProps({
    data: Object,
});

const { t } = useI18n();
const { can } = useAuth();

const detectLink = () => {
    if (can("view-PLD-invoices")) {
        return route("pld.invoices.index", {
            "id[]": props.data.invoice_id,
            initialize_from_inertia_page: true,
        });
    } else if (can("view-CMD-invoices")) {
        return route("cmd.invoices.index", {
            "id[]": props.data.invoice_id,
            initialize_from_inertia_page: true,
        });
    } else {
        return undefined;
    }
};

const link = detectLink();
</script>

<template>
    <p>
        {{ t("notifications.Invoice payment completed") }}:

        <InertiaLink class="text-primary" :link="link">
            <strong># {{ data.invoice_id }}</strong>
        </InertiaLink>
    </p>

    <p>
        {{ t("fields.PO №") }}: <strong>{{ data.order_name }}</strong
        ><br />
        {{ t("Products") }}: {{ data.products_count }}<br />
        {{ t("fields.Manufacturer") }}: {{ data.order_manufacturer_name }}<br />
        {{ t("fields.Country") }}: {{ data.order_country_code }}
    </p>
</template>
