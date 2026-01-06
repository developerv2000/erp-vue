<script setup>
import MadNav from "./partials/MadNav.vue";
import AdministrationNav from "./partials/AdministrationNav.vue";
import PldNav from "./partials/PldNav.vue";
import CmdNav from "./partials/CmdNav.vue";
import PrdNav from "./partials/PrdNav.vue";
import DdNav from "./partials/DdNav.vue";
import MdNav from "./partials/MdNav.vue";
</script>

<template>
    <div class="leftbar-nav">
        <MadNav />
        <PldNav />
        <CmdNav />
        <PrdNav />
        <DdNav />
        <MdNav />
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
