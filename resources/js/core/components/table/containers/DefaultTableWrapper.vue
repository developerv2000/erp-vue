<script setup>
import { toggleFullscreenClass } from "@/core/scripts/functions";

function handleFullscreenChange() {
    const defaultTableWrapper = document.querySelector(
        ".default-table-wrapper"
    );

    toggleFullscreenClass(defaultTableWrapper);
}
</script>

<template>
    <div
        @fullscreenchange="handleFullscreenChange"
        class="default-table-wrapper d-flex ga-6 align-start"
    >
        <slot />
    </div>
</template>
