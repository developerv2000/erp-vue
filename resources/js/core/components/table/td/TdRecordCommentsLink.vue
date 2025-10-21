<script setup>
import { useI18n } from "vue-i18n";
import TdInertiaLink from "./TdInertiaLink.vue";

defineProps({
    record: Object,
});

const { t } = useI18n();
</script>

<template>
    <TdInertiaLink
        :link="
            route('comments.view-model-comments', {
                commentable_type: record.base_model_class,
                commentable_id: record.id,
            })
        "
    >
        {{ record.comments_count }}
        <span class="text-lowercase">{{ t("Comments") }}</span>
    </TdInertiaLink>
</template>
