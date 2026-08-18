<script setup lang="ts">
import EditTimesDialog from '@/Components/Post/Partials/EditTimesDialog.vue';
import { DateTime } from 'luxon';
import { CircleX, Pencil, PlaneLanding, PlaneTakeoff } from 'lucide-vue-next';
import {
    TransportPost,
    TransportPostStopoverDto,
} from '../../../../types/Api.gen';
import { api } from '@/api';
import { computed, PropType, ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import Delay from '@/Components/Post/Partials/Delay.vue';

const { t } = useI18n();
const props = defineProps({
    activeTransportPost: {
        type: Object as PropType<TransportPost | null>,
        required: true,
    },
    stopovers: {
        type: Array as PropType<TransportPostStopoverDto[]>,
        required: true,
    },
});

const emit = defineEmits<{
    (e: 'update:stopovers', post: TransportPostStopoverDto[]): void;
}>();

function delayInMinutes(delaySeconds: number | null): number | null {
    return delaySeconds !== null ? Math.floor(delaySeconds / 60) : null;
}

function formatScheduledTime(
    time: string | null,
    delaySeconds: number | null,
): string | null {
    if (!time) {
        return null;
    }
    const scheduled = DateTime.fromISO(time);
    const adjusted = delaySeconds
        ? scheduled.plus({ seconds: delaySeconds })
        : scheduled;

    return adjusted.toLocaleString(DateTime.TIME_SIMPLE);
}

function formatActualTime(time: string | null): string | null {
    return time
        ? DateTime.fromISO(time).toLocaleString(DateTime.TIME_WITH_SECONDS)
        : null;
}

function laterStopAlreadyLogged(index: number): boolean {
    return props.stopovers
        .slice(index + 1)
        .some((stop) => stop.manualArrivalTime || stop.manualDepartureTime);
}

function logArrival(stop: TransportPostStopoverDto, index: number) {
    const timestamp = new Date().toISOString();
    if (!props.activeTransportPost) {
        return;
    }
    if (
        (laterStopAlreadyLogged(index) ||
            props.stopovers[index].manualDepartureTime) &&
        !confirm(t('active_transport_post.overwrite_confirm'))
    ) {
        return;
    }

    api.posts
        .logStopoverArrival(props.activeTransportPost.id, stop.id, {
            timestamp,
        })
        .then((response) => {
            emit('update:stopovers', response.data);
        });
}

function logDeparture(stop: TransportPostStopoverDto, index: number) {
    const timestamp = new Date().toISOString();
    if (!props.activeTransportPost) {
        return;
    }
    if (
        (laterStopAlreadyLogged(index) ||
            props.stopovers[index].manualDepartureTime) &&
        !confirm(t('active_transport_post.overwrite_confirm'))
    ) {
        return;
    }

    api.posts
        .logStopoverDeparture(props.activeTransportPost.id, stop.id, {
            timestamp,
        })
        .then((response) => {
            emit('update:stopovers', response.data);
        });
}

const editDialog = useTemplateRef('editDialog');
const editingStop = ref<TransportPostStopoverDto | null>(null);
const editingIndex = ref<number | null>(null);
const editDeparture = ref<DateTime | null>(null);
const editArrival = ref<DateTime | null>(null);

const editShowDeparture = computed(
    () =>
        editingIndex.value !== null &&
        editingIndex.value < props.stopovers.length - 1,
);
const editShowArrival = computed(
    () => editingIndex.value !== null && editingIndex.value > 0,
);
const editDialogTitle = computed(() =>
    t('active_transport_post.edit_stop_times_title', {
        name: editingStop.value?.location.name ?? '',
    }),
);

function parseTimeOrNull(iso: string | null): DateTime | null {
    if (!iso) {
        return null;
    }
    const parsed = DateTime.fromISO(iso);

    return parsed.isValid ? parsed : null;
}

function openEditDialog(stop: TransportPostStopoverDto, index: number) {
    editingStop.value = stop;
    editingIndex.value = index;
    editArrival.value = parseTimeOrNull(stop.manualArrivalTime);
    editDeparture.value = parseTimeOrNull(stop.manualDepartureTime);
    editDialog.value?.show();
}

function saveStopoverTimes() {
    if (!props.activeTransportPost || !editingStop.value) {
        return;
    }
    const stopId = editingStop.value.id;
    const requests: Promise<{ data: TransportPostStopoverDto[] }>[] = [];

    if (editShowArrival.value && editArrival.value) {
        requests.push(
            api.posts.logStopoverArrival(props.activeTransportPost.id, stopId, {
                timestamp: editArrival.value.toISO() as string,
            }),
        );
    }
    if (editShowDeparture.value && editDeparture.value) {
        requests.push(
            api.posts.logStopoverDeparture(
                props.activeTransportPost.id,
                stopId,
                {
                    timestamp: editDeparture.value.toISO() as string,
                },
            ),
        );
    }

    Promise.all(requests).then((responses) => {
        const last = responses[responses.length - 1];
        if (last) {
            emit('update:stopovers', last.data);
        }
    });
}

function applyClearResponse(stopoverList: TransportPostStopoverDto[]) {
    emit('update:stopovers', stopoverList);
    const updated = stopoverList.find((s) => s.id === editingStop.value?.id);
    if (!updated) {
        return;
    }
    editingStop.value = updated;
    editArrival.value = parseTimeOrNull(updated.manualArrivalTime);
    editDeparture.value = parseTimeOrNull(updated.manualDepartureTime);
}

function clearArrival() {
    if (!props.activeTransportPost || !editingStop.value) {
        return;
    }
    api.posts
        .clearStopoverArrival(
            props.activeTransportPost.id,
            editingStop.value.id,
        )
        .then((response) => applyClearResponse(response.data));
}

function clearDeparture() {
    if (!props.activeTransportPost || !editingStop.value) {
        return;
    }
    api.posts
        .clearStopoverDeparture(
            props.activeTransportPost.id,
            editingStop.value.id,
        )
        .then((response) => applyClearResponse(response.data));
}
</script>

<template>
    <ul class="list w-full">
        <li
            v-for="(stop, index) in props.stopovers"
            :key="stop.id"
            class="list-row items-center px-0"
        >
            <div class="list-col-grow">
                <div class="font-medium">
                    {{ stop.location.name }}
                </div>
                <div class="flex flex-wrap gap-x-4 text-xs opacity-70">
                    <span v-if="index > 0">
                        {{ t('edit_transport_times.arrival') }}:
                        {{
                            formatScheduledTime(
                                stop.scheduledArrivalTime,
                                stop.arrivalDelay,
                            ) ?? '–'
                        }}
                        <Delay :delay="delayInMinutes(stop.arrivalDelay)" />
                    </span>
                    <span v-if="index < props.stopovers.length - 1">
                        {{ t('edit_transport_times.departure') }}:
                        {{
                            formatScheduledTime(
                                stop.scheduledDepartureTime,
                                stop.departureDelay,
                            ) ?? '–'
                        }}
                        <Delay :delay="delayInMinutes(stop.departureDelay)" />
                    </span>
                </div>
                <div
                    v-if="stop.manualArrivalTime || stop.manualDepartureTime"
                    class="text-success flex flex-wrap gap-x-4 text-xs font-medium"
                >
                    <span v-if="stop.manualArrivalTime">
                        {{ t('active_transport_post.logged_arrival') }}:
                        {{ formatActualTime(stop.manualArrivalTime) }}
                    </span>
                    <span v-if="stop.manualDepartureTime">
                        {{ t('active_transport_post.logged_departure') }}:
                        {{ formatActualTime(stop.manualDepartureTime) }}
                    </span>
                </div>
            </div>
            <div class="flex gap-2">
                <button
                    v-if="index > 0"
                    class="btn btn-sm btn-outline"
                    @click="logArrival(stop, index)"
                >
                    <PlaneLanding class="size-4" />
                    <span class="hidden md:inline">
                        {{ t('edit_transport_times.arrive_now') }}
                    </span>
                </button>
                <button
                    v-if="index < props.stopovers.length - 1"
                    class="btn btn-sm btn-primary"
                    @click="logDeparture(stop, index)"
                >
                    <PlaneTakeoff class="size-4" />
                    <span class="hidden md:inline">
                        {{ t('edit_transport_times.depart_now') }}
                    </span>
                </button>
                <button
                    class="btn btn-sm btn-ghost"
                    @click="openEditDialog(stop, index)"
                >
                    <Pencil class="size-4" />
                    <span class="hidden md:inline">
                        {{ t('verbs.edit') }}
                    </span>
                </button>
            </div>
        </li>
    </ul>

    <EditTimesDialog
        ref="editDialog"
        v-model:departure="editDeparture"
        v-model:arrival="editArrival"
        :title="editDialogTitle"
        :departure-label="t('edit_transport_times.departure')"
        :arrival-label="t('edit_transport_times.arrival')"
        :show-departure="editShowDeparture"
        :show-arrival="editShowArrival"
        arrival-first
        @save="saveStopoverTimes"
    >
        <template v-if="editingStop?.manualArrivalTime" #arrival-extra>
            <button type="button" class="btn btn-outline" @click="clearArrival">
                <CircleX class="size-5" />
                {{ t('edit_transport_times.clear_arrival') }}
            </button>
        </template>
        <template v-if="editingStop?.manualDepartureTime" #departure-extra>
            <button
                type="button"
                class="btn btn-outline"
                @click="clearDeparture"
            >
                <CircleX class="size-5" />
                {{ t('edit_transport_times.clear_departure') }}
            </button>
        </template>
    </EditTimesDialog>
</template>
