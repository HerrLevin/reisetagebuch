<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { BasePost, LocationPost, TransportPost } from '@/types/PostTypes';
import { Head } from '@inertiajs/vue3';
import { PropType } from 'vue';
import Post from '@/Components/Post/Post.vue';
import ArrowLeft from '@/Icons/ArrowLeft.vue';

defineProps({
    post: {
        type: Object as PropType<BasePost | TransportPost | LocationPost>,
        required: true,
    },
});

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head title="" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">Dashboard</h2>
        </template>

        <div class="card bg-base-100 min-w-full shadow-md">
            <div
                class="border-base-300 flex items-center justify-between border-b-1 p-4"
            >
                <button
                    class="btn btn-ghost btn-sm text-base-content normal-case"
                    type="button"
                    @click="goBack"
                >
                    <ArrowLeft class="size-6" />
                </button>
            </div>
            <div class="p-4">
                <ul class="list">
                    <li class="list-row">
                        <Post :post />
                    </li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
