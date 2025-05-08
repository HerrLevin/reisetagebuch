<script setup lang="ts">
import ContextMenu from '@/Components/Post/ContextMenu.vue';
import ArrowUpOnSquare from '@/Icons/ArrowUpOnSquare.vue';
import Heart from '@/Icons/Heart.vue';
import {
    BasePost,
    isLocationPost,
    isTransportPost,
    LocationPost,
    TransportPost,
} from '@/types/PostTypes';
import { PropType } from 'vue';

const props = defineProps({
    post: {
        type: Object as PropType<BasePost | TransportPost | LocationPost>,
        required: true,
    },
});

function sharePost(): void {
    let postText = `Check out ${props.post.user.name}'s post!`;
    if (isLocationPost(props.post)) {
        postText = `Check out ${props.post.user.name}'s post at ${props.post.location.name}!`;
    } else if (isTransportPost(props.post)) {
        postText = `Check out ${props.post.user.name}'s travel from ${props.post.start.name} to ${props.post.stop.name}`;
    }
    const shareData = {
        title: 'Post',
        text: postText,
        url: route('posts.show', props.post.id),
    };

    if (navigator.canShare && navigator.canShare(shareData)) {
        navigator.share(shareData).then().catch();
    } else {
        // copy the link to the clipboard
        navigator.clipboard
            .writeText(shareData.url)
            .then(() => {
                alert('Post link copied to clipboard');
            })
            .catch();
    }
}
</script>

<template>
    <div class="flex items-center justify-between">
        <button class="btn btn-ghost btn-sm btn-circle hidden">
            <Heart />
        </button>

        <button
            class="btn btn-ghost btn-sm btn-circle"
            @click.prevent="sharePost()"
        >
            <ArrowUpOnSquare />
        </button>

        <ContextMenu :post />
    </div>
</template>

<style scoped></style>
