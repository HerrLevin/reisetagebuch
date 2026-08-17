<script setup lang="ts">
import { api } from '@/api';
import Delay from '@/Components/Post/Partials/Delay.vue';
import EditTimesDialog from '@/Components/Post/Partials/EditTimesDialog.vue';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getBaseText, prettyDates } from '@/Services/PostTextService';
import { CircleX, Pencil, PlaneLanding, PlaneTakeoff } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { computed, onMounted, ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { TransportPostStopoverDto } from '../../../types/Api.gen';
import { useActiveTransportPostStore } from '@/stores/activeTransportPost';

const { t } = useI18n();

const title = t('active_transport_post.title');
const activePost = useActiveTransportPostStore();

const subtitle = computed(() =>
    activePost.activeTransportPost
        ? `${getBaseText(activePost.activeTransportPost)} (${prettyDates(activePost.activeTransportPost)})`
        : '',
);

function fetchActivePost() {
    activePost.fetchPost().then(() => {
        useTitle(
            activePost.activeTransportPost
                ? `${title} · ${subtitle.value}`
                : title,
        );
    });
}

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
    return activePost.stopovers
        .slice(index + 1)
        .some((stop) => stop.manualArrivalTime || stop.manualDepartureTime);
}

function logArrival(stop: TransportPostStopoverDto, index: number) {
    const timestamp = new Date().toISOString();
    if (!activePost.activeTransportPost) {
        return;
    }
    if (
        (laterStopAlreadyLogged(index) ||
            activePost.stopovers[index].manualDepartureTime) &&
        !confirm(t('active_transport_post.overwrite_confirm'))
    ) {
        return;
    }

    api.posts
        .logStopoverArrival(activePost.activeTransportPost.id, stop.id, {
            timestamp,
        })
        .then((response) => {
            activePost.stopovers = response.data;
        });
}

function logDeparture(stop: TransportPostStopoverDto, index: number) {
    const timestamp = new Date().toISOString();
    if (!activePost.activeTransportPost) {
        return;
    }
    if (
        (laterStopAlreadyLogged(index) ||
            activePost.stopovers[index].manualDepartureTime) &&
        !confirm(t('active_transport_post.overwrite_confirm'))
    ) {
        return;
    }

    api.posts
        .logStopoverDeparture(activePost.activeTransportPost.id, stop.id, {
            timestamp,
        })
        .then((response) => {
            activePost.stopovers = response.data;
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
        editingIndex.value < activePost.stopovers.length - 1,
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
    if (!activePost.activeTransportPost || !editingStop.value) {
        return;
    }
    const stopId = editingStop.value.id;
    const requests: Promise<{ data: TransportPostStopoverDto[] }>[] = [];

    if (editShowArrival.value && editArrival.value) {
        requests.push(
            api.posts.logStopoverArrival(
                activePost.activeTransportPost.id,
                stopId,
                {
                    timestamp: editArrival.value.toISO() as string,
                },
            ),
        );
    }
    if (editShowDeparture.value && editDeparture.value) {
        requests.push(
            api.posts.logStopoverDeparture(
                activePost.activeTransportPost.id,
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
            activePost.stopovers = last.data;
        }
    });
}

function applyClearResponse(stopoverList: TransportPostStopoverDto[]) {
    activePost.stopovers = stopoverList;
    const updated = stopoverList.find((s) => s.id === editingStop.value?.id);
    if (!updated) {
        return;
    }
    editingStop.value = updated;
    editArrival.value = parseTimeOrNull(updated.manualArrivalTime);
    editDeparture.value = parseTimeOrNull(updated.manualDepartureTime);
}

function clearArrival() {
    if (!activePost.activeTransportPost || !editingStop.value) {
        return;
    }
    api.posts
        .clearStopoverArrival(
            activePost.activeTransportPost.id,
            editingStop.value.id,
        )
        .then((response) => applyClearResponse(response.data));
}

function clearDeparture() {
    if (!activePost.activeTransportPost || !editingStop.value) {
        return;
    }
    api.posts
        .clearStopoverDeparture(
            activePost.activeTransportPost.id,
            editingStop.value.id,
        )
        .then((response) => applyClearResponse(response.data));
}

onMounted(fetchActivePost);
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">{{ title }}</h2>
        </template>

        <div v-if="activePost.loadingPost" class="skeleton h-40 w-full" />

        <div
            v-else-if="!activePost.activeTransportPost"
            class="card bg-base-100 min-w-full shadow-md"
        >
            <div class="card-body items-center text-center">
                <p>{{ t('active_transport_post.no_active_post') }}</p>
                <p class="text-sm opacity-70">
                    {{ t('active_transport_post.no_active_post_hint') }}
                </p>
            </div>
        </div>

        <div v-else class="card bg-base-100 min-w-full shadow-md">
            <div class="card-body">
                <div class="pb-4 text-sm opacity-70">{{ subtitle }}</div>

                <ul class="list w-full">
                    <li
                        v-for="(stop, index) in activePost.stopovers"
                        :key="stop.id"
                        class="list-row items-center"
                    >
                        <div class="list-col-grow">
                            <div class="font-medium">
                                {{ stop.location.name }}
                            </div>
                            <div
                                class="flex flex-wrap gap-x-4 text-xs opacity-70"
                            >
                                <span v-if="index > 0">
                                    {{ t('edit_transport_times.arrival') }}:
                                    {{
                                        formatScheduledTime(
                                            stop.scheduledArrivalTime,
                                            stop.arrivalDelay,
                                        ) ?? '–'
                                    }}
                                    <Delay
                                        :delay="
                                            delayInMinutes(stop.arrivalDelay)
                                        "
                                    />
                                </span>
                                <span
                                    v-if="
                                        index < activePost.stopovers.length - 1
                                    "
                                >
                                    {{ t('edit_transport_times.departure') }}:
                                    {{
                                        formatScheduledTime(
                                            stop.scheduledDepartureTime,
                                            stop.departureDelay,
                                        ) ?? '–'
                                    }}
                                    <Delay
                                        :delay="
                                            delayInMinutes(stop.departureDelay)
                                        "
                                    />
                                </span>
                            </div>
                            <div
                                v-if="
                                    stop.manualArrivalTime ||
                                    stop.manualDepartureTime
                                "
                                class="text-success flex flex-wrap gap-x-4 text-xs font-medium"
                            >
                                <span v-if="stop.manualArrivalTime">
                                    {{
                                        t(
                                            'active_transport_post.logged_arrival',
                                        )
                                    }}:
                                    {{
                                        formatActualTime(stop.manualArrivalTime)
                                    }}
                                </span>
                                <span v-if="stop.manualDepartureTime">
                                    {{
                                        t(
                                            'active_transport_post.logged_departure',
                                        )
                                    }}:
                                    {{
                                        formatActualTime(
                                            stop.manualDepartureTime,
                                        )
                                    }}
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
                                v-if="index < activePost.stopovers.length - 1"
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
            </div>
        </div>

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
                <button
                    type="button"
                    class="btn btn-outline"
                    @click="clearArrival"
                >
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
    </AuthenticatedLayout>
</template>
