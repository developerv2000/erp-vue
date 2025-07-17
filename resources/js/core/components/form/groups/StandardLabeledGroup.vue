<script setup>
defineProps({
    label: String,
    labelFor: {
        type: String,
        default: null,
    },
    required: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div>
        <label class="d-block mb-1 text-subtitle-1" :for="labelFor">
            {{ label }}
            <span v-if="required" class="text-error ms-1">*</span>
        </label>

        <slot />
    </div>
</template>
