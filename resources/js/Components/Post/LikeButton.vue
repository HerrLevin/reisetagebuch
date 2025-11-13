<script setup lang="ts">
import axios from 'axios';
import { Heart } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    postId: string;
    initialLiked: boolean;
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

    axios
        .request({
            url: route(
                willBeLiked ? 'posts.like' : 'posts.unlike',
                props.postId,
            ),
            method: willBeLiked ? 'POST' : 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        })
        .then((data) => {
            liked.value = data.data.liked;
            emit('likeToggled', data.data.liked, data.data.likes_count);
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
