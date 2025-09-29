<script setup>
import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import DeleteSelectedButton from "@/core/components/table/toolbar/actions/DeleteSelectedButton.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";
import { useI18n } from "vue-i18n";
import { usePage, router } from "@inertiajs/vue3";
import { useAttachmentsStore } from "@/global/stores/attachments";

const page = usePage();
const { t } = useI18n();
const store = useAttachmentsStore();

const actionAfterSuccessDelete = () => {
    router.reload({
        only: ['attachments'],
    });
}
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ page.props.attachments.length }}
        </template>

        <template #actions>
            <DeleteSelectedButton
                :delete-link="route('attachments.destroy')"
                :store="store"
                :actionOnSuccess="actionAfterSuccessDelete"
            />
        </template>

        <template #moreActions>
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
