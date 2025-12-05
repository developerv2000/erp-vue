<script setup>
import { useI18n } from "vue-i18n";
import InertiaLink from "@/core/components/inertia/InertiaLink.vue";

defineProps({
    data: Object,
});

const { t } = useI18n();
</script>

<template>
    <div>
        {{
            t("notifications.Process stage updated to contract", {
                process_id: data.process_id,
                status_name: data.status_name,
            })
        }}
    </div>

    <InertiaLink
        class="text-primary"

        :link="
            route('mad.processes.index', {
                'id[]': data.process_id,
                initialize_from_inertia_page: true,
            })
        "
    >
        {{ t("actions.Check") }} #{{ data.process_id }}
    </InertiaLink>
</template>
