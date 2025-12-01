<script setup>
import MadNav from "./partials/MadNav.vue";
import AdministrationNav from "./partials/AdministrationNav.vue";
</script>

<template>
    <div class="leftbar-nav">
        <MadNav />
        <AdministrationNav />
    </div>
</template>

<style scoped>
.leftbar-nav::v-deep(.v-list-item__spacer) {
    width: 20px;
}

.leftbar-nav::v-deep(.v-list-item-title) {
    font-size: 14px;
}

.leftbar-nav::v-deep(.v-list-subheader__text) {
    font-size: 13px;
    font-weight: bold;
}
</style>
