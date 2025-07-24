<template>
    <div class="table-wrapper">
        <slot />
    </div>
</template>

<style scoped>
.table-wrapper::v-deep(.v-table__wrapper table) {
    table-layout: fixed;
}
</style>
