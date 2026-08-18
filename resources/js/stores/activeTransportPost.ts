import { api } from '@/api';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { TransportPost, TransportPostStopoverDto } from '../../types/Api.gen';

export const useActiveTransportPostStore = defineStore(
    'activeTransportPost',
    () => {
        const activeTransportPost = ref<TransportPost | null>(null);
        const loadingPost = ref<boolean>(false);
        const stopovers = ref<TransportPostStopoverDto[] | []>([]);
        const refreshedAt = ref<Date | null>(null);

        const fetchPost = async (force = false) => {
            // only refresh every 5 minutes
            if (
                !force &&
                activeTransportPost.value !== null &&
                refreshedAt.value
            ) {
                const now = new Date();
                const diff =
                    (now.getTime() - refreshedAt.value.getTime()) / 1000;
                if (diff < 300 && activeTransportPost.value) {
                    return;
                }
            }
            loadingPost.value = true;
            api.posts
                .getActiveTransportPost()
                .then((response) => {
                    activeTransportPost.value = response.data;
                    refreshedAt.value = new Date();
                    fetchStopovers();
                })
                .catch((error) => {
                    console.error('Error fetching user:', error);
                })
                .finally(() => {
                    loadingPost.value = false;
                });
        };

        const fetchStopovers = async () => {
            if (activeTransportPost.value == null) {
                return;
            }

            api.posts
                .getStopoversForTransportPost(activeTransportPost.value!.id)
                .then((response) => {
                    stopovers.value = response?.data ?? [];
                });
        };

        return {
            activeTransportPost,
            stopovers,
            loadingPost,
            fetchPost,
            fetchStopovers,
        };
    },
    {
        persist: true,
    },
);
