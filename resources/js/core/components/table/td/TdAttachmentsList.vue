<script setup>
defineProps({
    attachments: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <template v-for="(attachment, index) in attachments" :key="index">
        <a
            class="attachments-show-link text-primary text-decoration-none"
            target="_blank"
            :href="route('attachments.show', attachment.id)"
        >
            {{ attachment.filename }}
        </a>

        <br />
    </template>
</template>

<style scoped>
.attachments-show-link:hover {
    text-decoration: underline !important;
}
</style>
