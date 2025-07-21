<script setup>
</script>

<template>
    <div class="d-flex justify-space-between align-center mb-8">
        <div>
            <slot name="breadcrumbs"></slot>

            <h5 class="text-h5 font-weight-bold">
                <slot name="title"></slot>
            </h5>
        </div>

        <slot name="actions"></slot>
    </div>
</template>
