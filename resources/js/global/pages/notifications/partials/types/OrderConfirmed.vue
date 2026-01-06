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
        {{ t("notifications.Order confirmed") }}:

        <InertiaLink
            class="text-primary"z
            :link="
                route('cmd.orders.index', {
                    'id[]': data.order_id,
                    initialize_from_inertia_page: true,
                })
            "
        >
            <strong># {{ data.order_id }}</strong>
        </InertiaLink>
    </p>

    <p>
        {{ t("fields.PO №") }}: <strong>{{ data.order_name }}</strong
        ><br />
        {{ t("dates.PO") }}: {{ data.purchase_date }}<br />
        {{ t("Products") }}: {{ data.products_count }}
    </p>
</template>
