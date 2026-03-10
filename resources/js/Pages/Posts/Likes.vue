<script setup lang="ts">
import { api } from '@/api';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CardBack from '@/Pages/Posts/Partials/CardBack.vue';
import UserListCard from '@/Pages/Posts/Partials/UserListCard.vue';
import { ref, watch, watchEffect } from 'vue';
import { UserDto } from '../../../types/Api.gen';

const props = defineProps({
    postId: {
        type: String,
        required: true,
    },
});

const heading = ref('');
const pageTitle = ref('');
const loading = ref(false);
const likes = ref<UserDto[]>([]);

function fetchPost() {
    loading.value = true;
    api.posts
        .getPostLikes(props.postId)
        .then((response) => {
            if (!response.data) {
                likes.value = [];
                return;
            }
            likes.value = response.data;
        })
        .catch(() => {
            likes.value = [];
        })
        .finally(() => {
            loading.value = false;
        });
}

watch(() => props.postId, fetchPost, { immediate: true });

watchEffect(() => {
    if (pageTitle.value) {
        useTitle(pageTitle.value);
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ heading }}
            </h2>
        </template>

        <div class="card bg-base-100 min-w-full shadow-md">
            <CardBack />
            <ul class="list">
                <li v-for="like in likes" :key="like.id">
                    <div class="list-row hover-list-entry">
                        <UserListCard :user="like" />
                    </div>
                </li>
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
