<script setup lang="ts">
import ContextMenu from '@/Components/Post/ContextMenu.vue';
import LikeButton from '@/Components/Post/LikeButton.vue';
import LikesIndicator from '@/Components/Post/LikesIndicator.vue';
import { BasePost, LocationPost, TransportPost } from '@/types/PostTypes';
import { PropType, ref } from 'vue';

const props = defineProps({
    post: {
        type: Object as PropType<BasePost | TransportPost | LocationPost>,
        required: true,
    },
});

const likesCount = ref(props.post.likesCount);
const likedByUser = ref(props.post.likedByUser);

const handleLikeToggled = (liked: boolean, count: number) => {
    likesCount.value = count;
    likedByUser.value = liked;
};
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <div class="gap-1">
            <LikesIndicator :likes="likesCount" :liked-by-user="likedByUser" />

            <LikeButton
                :post-id="post.id"
                :initial-liked="post.likedByUser"
                @like-toggled="handleLikeToggled"
            />
        </div>

        <ContextMenu :post />
    </div>
</template>
