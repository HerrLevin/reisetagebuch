<script setup lang="ts">
import StopOverList from '@/Pages/Posts/Partials/StopOverList.vue';
import { getTransportProgress } from '@/Services/TimeFormattingService';
import { useUserStore } from '@/stores/user';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    TransportPost,
    TransportPostStopoverDto,
} from '../../../types/Api.gen';

const { t } = useI18n();
const user = useUserStore();

const props = defineProps<{
    post: TransportPost;
    stopovers: TransportPostStopoverDto[];
}>();

const isOwner = computed(() => user.user?.id === props.post.user.id);

const isCompleted = computed(() => getTransportProgress(props.post) >= 100);

const nextStopover = computed(() => {
    if (isCompleted.value || props.stopovers.length === 0) {
        return null;
    }

    return (
        props.stopovers.find(
            (stop) => !stop.manualArrivalTime && !stop.manualDepartureTime,
        ) ?? null
    );
});

const emits = defineEmits<{
    (e: 'update:stopovers', stopovers: TransportPostStopoverDto[]): void;
}>();
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
                        @update:stopovers="emits('update:stopovers', $event)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
