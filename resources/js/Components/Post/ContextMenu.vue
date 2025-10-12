<script setup lang="ts">
import { BasePost, isLocationPost, isTransportPost } from '@/types/PostTypes';
import { useForm, usePage } from '@inertiajs/vue3';
import { Ellipsis, Share, SquarePen, Trash2 } from 'lucide-vue-next';
import { PropType, useTemplateRef } from 'vue';

const props = defineProps({
    post: {
        type: Object as PropType<BasePost>,
        required: true,
    },
});

const emit = defineEmits(['delete:post']);

const deleteModal = useTemplateRef('deleteModal');
const form = useForm({});

function deletePost() {
    form.delete(route('posts.destroy', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
            deleteModal.value?.close();
            emit('delete:post', props.post.id);
        },
        onFinish: () => {
            form.reset();
        },
    });
}

const isSameUser = () => {
    return props.post.user.id === usePage().props.auth?.user?.id;
};

function sharePost(): void {
    blur();

    let postText = `Check out ${props.post.user.name}'s post!`;
    if (isLocationPost(props.post)) {
        postText = `Check out ${props.post.user.name}'s post at ${props.post.location.name}!`;
    } else if (isTransportPost(props.post)) {
        postText = `Check out ${props.post.user.name}'s travel from ${props.post.originStop.location.name} to ${props.post.originStop.location.name}`;
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

function blur() {
    (document.activeElement as HTMLElement)?.blur();
}
</script>

<template>
    <div class="dropdown dropdown-bottom dropdown-end" @click.prevent>
        <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle">
            <Ellipsis />
        </div>
        <ul
            tabindex="-1"
            class="menu dropdown-content bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
        >
            <li>
                <a @click.prevent="sharePost()">
                    <Share class="size-4" />
                    Share
                </a>
            </li>
            <template v-if="isSameUser()">
                <li>
                    <a>
                        <SquarePen class="size-4" />
                        Edit
                    </a>
                </li>
                <li class="mx-0 border-b-1"></li>
                <li>
                    <a
                        @click.prevent="
                            blur();
                            deleteModal?.showModal();
                        "
                    >
                        <Trash2 class="size-4" />
                        <span class="text-red-500">Delete</span>
                    </a>
                </li>
            </template>
        </ul>
    </div>
    <dialog ref="deleteModal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                Are you sure you want to delete your post?
            </h3>

            <div class="modal-action">
                <form method="dialog">
                    <button class="btn" @click.prevent="deleteModal?.close()">
                        Cancel
                    </button>
                </form>

                <button
                    class="btn btn-error"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click.prevent="deletePost()"
                >
                    Delete Post
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</template>
