<script setup>
import MainFilter from "./MainFilter.vue";
import FilterApplyButton from "@/core/components/filters/buttons/FilterApplyButton.vue";
import FilterResetButton from "@/core/components/filters/buttons/FilterResetButton.vue";

const { store } = defineProps({
    store: Object,
});
</script>

<template>
    <MainFilter>
        <template #resetButton>
            <FilterResetButton
                @click="store.resetFilter"
                :disabled="store.loading"
            />
        </template>

        <slot />

        <template #applyButton>
            <FilterApplyButton
                @click="store.applyFilter"
                :loading="store.loading"
            />
        </template>
    </MainFilter>
</template>
