<script setup lang="ts">
import ContextMenu from '@/Components/Post/ContextMenu.vue';
import LikeButton from '@/Components/Post/LikeButton.vue';
import LikesIndicator from '@/Components/Post/LikesIndicator.vue';
import { PropType, ref } from 'vue';
import { BasePost, LocationPost, TransportPost } from '../../../types/Api.gen';

const props = defineProps({
    post: {
        type: Object as PropType<BasePost | TransportPost | LocationPost>,
        required: true,
    },
});

const likesCount = ref(props.post.likesCount);
const likedByUser = ref(props.post.likedByUser);
const emit = defineEmits(['delete:post']);

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

        <ContextMenu :post @delete:post="emit('delete:post')" />
    </div>
</template>
