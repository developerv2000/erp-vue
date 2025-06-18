<script setup>
defineProps({
    label: String,
    required: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div>
        <label class="d-block text-subtitle-2 font-weight-medium">
            <div class="mb-1">
                {{ label }}
                <span v-if="required" class="text-error ms-1">*</span>
            </div>

            <slot />
        </label>
    </div>
</template>
