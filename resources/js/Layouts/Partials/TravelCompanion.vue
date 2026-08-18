<script setup lang="ts">
import { CircleChevronRight } from 'lucide-vue-next';
import {
    formatArrivalTime,
    getTransportProgress,
} from '@/Services/TimeFormattingService';
import { getArrivalDelay } from '@/Services/TripTimeService';
import { getRouteBadgeStyle } from '@/Services/DepartureTypeService';
import { getPostLineName } from '@/Services/LineNameFormattingService';
import StopOverList from '@/Pages/Posts/Partials/StopOverList.vue';
import { useActiveTransportPostStore } from '@/stores/activeTransportPost';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { getCurrentOrNextStopover } from '@/Services/TripStopoverService';
import { TransportPostStopoverDto } from '../../../types/Api.gen';

const activePost = useActiveTransportPostStore();
const nextStopover = ref<TransportPostStopoverDto | null>(null);
const progress = ref(0);
const refreshInterval = setInterval(() => {
    getNextStopover();
    calculateProgress();
    activePost.fetchPost();
}, 1000);

function calculateProgress(): void {
    progress.value = getTransportProgress(activePost.activeTransportPost);
}

function getNextStopover(): void {
    if (activePost.stopovers) {
        nextStopover.value = getCurrentOrNextStopover(activePost.stopovers);
    }
}

function getFormattedArrivalTime(): string | null {
    if (!activePost.activeTransportPost) {
        return null;
    }
    return formatArrivalTime(
        activePost.activeTransportPost.destinationStop,
        activePost.activeTransportPost.manualArrivalTime,
        getArrivalDelay(activePost.activeTransportPost) || 0,
    );
}
onBeforeUnmount(() => {
    clearInterval(refreshInterval);
});

onMounted(() => {
    activePost.fetchPost();
});
</script>

<template>
    <div
        v-if="activePost.activeTransportPost"
        onclick="my_modal_5.showModal()"
        class="btn btn-lg bg-base-300 text-base-content/65 w-full flex-col gap-0 px-2 pt-0 pb-2 text-start text-sm shadow-xl"
    >
        <div class="flex w-full justify-between text-xs font-normal">
            <div class="line-clamp-1 overflow-ellipsis">
                <div
                    v-show="activePost.activeTransportPost.trip.lineName"
                    class="badge badge-xs"
                    :style="
                        getRouteBadgeStyle(activePost.activeTransportPost.trip)
                    "
                >
                    {{ getPostLineName(activePost.activeTransportPost) }}
                </div>
                {{ activePost.activeTransportPost.destinationStop.name }}
            </div>
            <div class="ms-1">
                {{ getFormattedArrivalTime() }}
            </div>
        </div>
        <div v-if="nextStopover" class="flex w-full justify-between">
            <p>
                <CircleChevronRight class="inline-block size-3" />
                {{ nextStopover.location.name }}
            </p>
        </div>
        <progress
            class="progress progress-secondary -mx-4 mt-1 -mb-3 w-full"
            style="height: 2px"
            :value="progress"
            max="100"
        ></progress>
    </div>
    <dialog
        v-if="activePost.activeTransportPost"
        id="my_modal_5"
        class="modal modal-bottom"
    >
        <div class="modal-box">
            <div class="flex w-full justify-between">
                <div class="line-clamp-1 font-bold overflow-ellipsis">
                    <div
                        v-show="activePost.activeTransportPost.trip.lineName"
                        class="badge badge-sm"
                        :style="
                            getRouteBadgeStyle(
                                activePost.activeTransportPost.trip,
                            )
                        "
                    >
                        {{ getPostLineName(activePost.activeTransportPost) }}
                    </div>
                    {{ activePost.activeTransportPost.destinationStop.name }}
                </div>
                <form method="dialog">
                    <button
                        class="btn btn-sm btn-circle btn-ghost absolute top-4 right-2"
                    >
                        ✕
                    </button>
                </form>
            </div>
            <StopOverList
                :stopovers="activePost.stopovers ?? []"
                :active-transport-post="activePost.activeTransportPost"
                @update:stopovers="activePost.stopovers = $event"
            />
        </div>
    </dialog>
</template>
