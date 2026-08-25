<script setup lang="ts">
import { api } from '@/api';
import StopOverList from '@/Pages/Posts/Partials/StopOverList.vue';
import { getTransportProgress } from '@/Services/TimeFormattingService';
import { useUserStore } from '@/stores/user';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    TransportPost,
    TransportPostStopoverDto,
} from '../../../types/Api.gen';

const { t } = useI18n();
const user = useUserStore();

const props = defineProps<{
    post: TransportPost;
}>();

const stopovers = ref<TransportPostStopoverDto[]>([]);
const loading = ref(false);

const isOwner = computed(() => user.user?.id === props.post.user.id);

const isCompleted = computed(() => getTransportProgress(props.post) >= 100);

const nextStopover = computed(() => {
    if (isCompleted.value || stopovers.value.length === 0) {
        return null;
    }

    return (
        stopovers.value.find(
            (stop) => !stop.manualArrivalTime && !stop.manualDepartureTime,
        ) ?? null
    );
});

function fetchStopovers() {
    loading.value = true;
    api.posts
        .getStopoversForTransportPost(props.post.id)
        .then((response) => {
            stopovers.value = response.data;
        })
        .catch(() => {
            stopovers.value = [];
        })
        .finally(() => {
            loading.value = false;
        });
}

watch(() => props.post.id, fetchStopovers, { immediate: true });
</script>

<template>
    <div class="card bg-base-100 min-w-full shadow-md">
        <div class="card-body">
            <div class="collapse-arrow collapse">
                <input type="checkbox" />
                <div
                    class="collapse-title flex flex-wrap items-center justify-between gap-2 font-semibold"
                >
                    <span>{{ t('posts.stopovers.title') }}</span>
                    <span
                        v-if="nextStopover"
                        class="text-sm font-normal opacity-70"
                    >
                        {{
                            t('posts.stopovers.next_stop', {
                                name: nextStopover.location.name,
                            })
                        }}
                    </span>
                </div>
                <div class="collapse-content">
                    <StopOverList
                        v-if="stopovers.length > 0"
                        :active-transport-post="post"
                        :stopovers="stopovers"
                        :editable="isOwner"
                        @update:stopovers="stopovers = $event"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
