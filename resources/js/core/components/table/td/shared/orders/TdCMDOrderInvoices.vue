<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import TdInertiaLink from "../../TdInertiaLink.vue";
import TdArrowedInertiaLink from "../../TdArrowedInertiaLink.vue";

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    paymentTypesByName: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();

/**
 * All possible attachable invoice actions
 */
const invoiceActions = computed(() => [
    {
        enabled: props.item.can_attach_production_prepayment_invoice,
        paymentTypeName: "Prepayment",
        label: t("actions.Add prepayment"),
    },
    {
        enabled: props.item.can_attach_production_final_payment_invoice,
        paymentTypeName: "Final payment",
        label: t("actions.Add final payment"),
    },
    {
        enabled: props.item.can_attach_production_full_payment_invoice,
        paymentTypeName: "Full payment",
        label: t("actions.Add full payment"),
    },
]);

/**
 * Filtered & ready-to-render actions
 */
const availableInvoiceActions = computed(() =>
    invoiceActions.value.filter(
        (action) =>
            action.enabled && props.paymentTypesByName[action.paymentTypeName]
    )
);
</script>

<template>
    <TdArrowedInertiaLink
        :link="
            route('cmd.invoices.index', {
                'order_id[]': item.id,
                initialize_from_inertia_page: true,
            })
        "
    >
        <span class="text-lowercase">
            {{ item.production_invoices_count }}
            {{ t("Invoices") }}
        </span>
    </TdArrowedInertiaLink>

    <div
        v-if="item.can_attach_any_production_invoice"
        class="d-flex flex-column ga-1"
    >
        <TdInertiaLink
            v-for="action in availableInvoiceActions"
            :key="action.paymentTypeName"
            :link="
                route('cmd.invoices.create', {
                    order_id: item.id,
                    payment_type_id:
                        paymentTypesByName[action.paymentTypeName].id,
                })
            "
        >
            {{ action.label }}
        </TdInertiaLink>
    </div>
</template>
