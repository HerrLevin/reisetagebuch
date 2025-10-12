<script setup lang="ts">
import ContextMenu from '@/Components/Post/ContextMenu.vue';
import { BasePost, LocationPost, TransportPost } from '@/types/PostTypes';
import { Heart } from 'lucide-vue-next';
import { PropType } from 'vue';

defineProps({
    post: {
        type: Object as PropType<BasePost | TransportPost | LocationPost>,
        required: true,
    },
});
</script>

<template>
    <div class="flex items-center justify-end">
        <button class="btn btn-ghost btn-sm btn-circle hidden">
            <Heart />
        </button>

        <ContextMenu :post />
    </div>
</template>
