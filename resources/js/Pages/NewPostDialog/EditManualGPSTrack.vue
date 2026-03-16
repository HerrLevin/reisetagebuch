<script setup lang="ts">
import { api } from '@/api';
import Loading from '@/Components/Loading.vue';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { isTransportPost } from '@/types/PostTypes';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { TransportPost } from '../../../types/Api.gen';

const { t } = useI18n();
const vueRouter = useRouter();

const props = defineProps({
    postId: {
        type: String,
        required: true,
    },
});

const post = ref<TransportPost | null>(null);

const subtitle = t('edit_manual_tracking.description');
const title = t('edit_manual_tracking.title');
const gpsInput = ref<File | null>(null);
const loading = ref(false);
const uploading = ref(false);

useTitle(title);

function fetchPost() {
    loading.value = true;
    api.posts
        .showPost(props.postId)
        .then((response) => {
            if (!isTransportPost(response.data)) {
                throw new Error('Post is not a transport post');
            }
            post.value = response.data;
            loading.value = false;
        })
        .catch((error) => {
            console.error('Error fetching post:', error);
            loading.value = false;
        });
}

const gpsUpload = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        gpsInput.value = input.files[0];
    }
};

function submit() {
    if (!post.value || !gpsInput.value) {
        return;
    }

    uploading.value = true;
    api.posts
        .uploadTransportTrack(props.postId, { track: gpsInput.value })
        .then(() => {
            uploading.value = false;
            vueRouter.push({
                name: 'posts.show',
                params: { postId: props.postId },
            });
        })
        .catch((error) => {
            alert(error.error.message);
            uploading.value = false;
        });
}

function deleteGeometry() {
    if (!post.value) {
        return;
    }

    // Confirm deletion with the user
    if (!confirm(t('edit_manual_tracking.confirm_delete'))) {
        return;
    }

    api.posts
        .deleteTransportTrack(props.postId)
        .then(() => {
            vueRouter.push({
                name: 'posts.show',
                params: { postId: props.postId },
            });
        })
        .catch((error) => {
            console.error('Error deleting geometry:', error);
        });
}

watch(() => props.postId, fetchPost, { immediate: true });
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">{{ title }}</h2>
        </template>
        <div class="card bg-base-100 min-w-full shadow-md">
            <div class="card-body">
                <div class="flex w-full items-center gap-4 pb-0">
                    <div class="text-3xl">🧭</div>
                    <div class="text-xl">{{ title }}</div>
                </div>
                <div class="pb-8 text-sm opacity-70">
                    {{ subtitle }}
                </div>
                <form>
                    <!-- file upload and map preview would go here -->
                    <div class="col col-span-2 md:col-span-1">
                        <label for="gps_file" class="font-bold">
                            {{ t('edit_manual_tracking.file') }}
                        </label>
                        <input
                            id="gps_file"
                            type="file"
                            class="file-input input-bordered w-full"
                            accept=".gpx, .geojson, .json"
                            @change="gpsUpload"
                        />
                    </div>
                    <div class="flex w-full justify-between pt-8">
                        <div>
                            <button
                                v-show="post?.userGeometry"
                                type="button"
                                class="btn btn-outline btn-error"
                                @click.prevent="deleteGeometry()"
                            >
                                {{ t('edit_manual_tracking.delete_track') }}
                            </button>
                            <Loading v-if="loading" />
                        </div>
                        <div class="flex gap-4">
                            <button
                                class="btn btn-secondary"
                                type="button"
                                @click.prevent="vueRouter.back()"
                            >
                                {{ t('verbs.cancel') }}
                            </button>
                            <button
                                class="btn btn-primary"
                                type="submit"
                                :disabled="uploading || !gpsInput"
                                @click.prevent="submit()"
                            >
                                {{ t('edit_manual_tracking.save_track') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
