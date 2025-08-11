<script setup>
defineProps({
    store: Object,
});
</script>

<template>
    <div class="d-flex align-center ga-2 mr-auto ml-4">
        <span>Go to page:</span>

        <v-text-field
            v-model.number="store.pagination.navigate_to_page"
            autocomplete="off"
            width="72"
            type="number"
            color="orange"
            variant="outlined"
            density="compact"
            hide-details
            :min="1"
            :max="store.pagination.last_page"
            @keyup.enter="store.handleNavigateToPage"
        />
    </div>
</template>
