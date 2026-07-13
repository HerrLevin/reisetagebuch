<script setup lang="ts">
import { api } from '@/api';
import { Heart } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    postId: string;
    initialLiked: boolean;
    isApPost?: boolean;
}>();

const emit = defineEmits<{
    likeToggled: [liked: boolean, count: number];
}>();

const liked = ref(props.initialLiked);
const loading = ref(false);

const toggleLike = async () => {
    if (loading.value) return;

    loading.value = true;
    const willBeLiked = !liked.value;

    let request;

    if (props.isApPost) {
        request = willBeLiked
            ? api.activitypub.likeApPost(props.postId)
            : api.activitypub.unlikeApPost(props.postId);
    } else {
        request = willBeLiked
            ? api.posts.likePost(props.postId)
            : api.posts.unlikePost(props.postId);
    }

    request
        .then((data) => {
            liked.value = data.data.likedByUser;
            emit('likeToggled', data.data.likedByUser, data.data.likeCount);
        })
        .catch((error) => {
            console.error('Error toggling like:', error);
        })
        .finally(() => {
            loading.value = false;
        });
};
</script>

<template>
    <button
        class="btn btn-sm btn-circle btn-ghost"
        :disabled="loading"
        @click.stop="toggleLike"
    >
        <Heart v-if="!liked" class="size-5" />
        <Heart v-else class="fill-error text-error size-5" />
    </button>
</template>
