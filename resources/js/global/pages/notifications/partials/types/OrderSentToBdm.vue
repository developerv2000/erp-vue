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
        {{ t("notifications.New order received") }}:

        <InertiaLink
            class="text-primary"
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
        {{ t("Products") }}: {{ data.products_count }}<br />
        {{ t("fields.Manufacturer") }}: {{ data.manufacturer }}<br />
        {{ t("fields.Country") }}: {{ data.country }}
    </p>
</template>
