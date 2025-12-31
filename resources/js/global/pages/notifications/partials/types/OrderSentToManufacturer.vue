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
    if (can("view-PLD-orders")) {
        return route("pld.orders.index", {
            "id[]": props.data.order_id,
            initialize_from_inertia_page: true,
        });
    } else if (can("view-DD-order-products")) {
        return route("dd.order-products.index", {
            "order_name[]": props.data.order_name,
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
        {{ t("notifications.Order sent to manufacturer") }}:

        <InertiaLink v-if="link" class="text-primary" :link="link">
            <strong># {{ data.order_id }}</strong>
        </InertiaLink>

        <strong v-else># {{ data.order_id }}</strong>
    </p>

    <p>
        {{ t("fields.PO №") }}: <strong>{{ data.order_name }}</strong
        ><br />
        {{ t("dates.PO") }}: {{ data.purchase_date }}<br />
        {{ t("Products") }}: {{ data.products_count }}
    </p>
</template>
