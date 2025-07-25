<script setup>
import TdInertiaLink from './TdInertiaLink.vue';

defineProps({
    record: Object,
});
</script>

<template>
    <TdInertiaLink
        :link="
            route('comments.index', {
                commentable_type: record.base_model_class,
                commentable_id: record.id,
            })
        "
    >
        {{ record.comments_count }} comments
    </TdInertiaLink>
</template>
