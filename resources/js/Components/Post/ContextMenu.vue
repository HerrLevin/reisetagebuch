<script setup lang="ts">
import EllipsisHorizontal from '@/Icons/EllipsisHorizontal.vue';
import Trash from '@/Icons/Trash.vue';
import { BasePost } from '@/types/PostTypes';
import { useForm } from '@inertiajs/vue3';
import { PropType, ref, useTemplateRef } from 'vue';

const props = defineProps({
    post: {
        type: Object as PropType<BasePost>,
        required: true,
    },
});

const emit = defineEmits(['delete:post']);

const dropdownOpen = ref(false);
const deleteModal = useTemplateRef('deleteModal');
const form = useForm({});

const deleteUser = () => {
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
};
</script>

<template>
    <details class="dropdown dropdown-bottom dropdown-end" :open="dropdownOpen">
        <summary
            class="btn btn-ghost btn-sm btn-circle"
            @click.prevent="dropdownOpen = !dropdownOpen"
        >
            <EllipsisHorizontal />
        </summary>
        <ul
            class="menu dropdown-content bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
        >
            <li><a>Edit</a></li>
            <li class="mx-0 border-b-1"></li>
            <li>
                <a @click.prevent="deleteModal?.showModal()">
                    <Trash class="size-4" />
                    <span class="text-red-500">Delete</span>
                </a>
            </li>
        </ul>
    </details>
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
                    @click.prevent="deleteUser"
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

<style scoped></style>
