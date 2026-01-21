<script setup lang="ts">
import { api } from '@/api';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getBaseText, prettyDates } from '@/Services/ApiPostTextService';
import { getDepartureDelay } from '@/Services/TripTimeService';
import { isApiTransportPost } from '@/types/PostTypes';
import { Head, router } from '@inertiajs/vue3';
import { CircleX, PlaneLanding, PlaneTakeoff } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    TransportPost,
    TransportTimesUpdateRequest,
} from '../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps({
    postId: {
        type: String,
        required: true,
    },
});

const post = ref<TransportPost | null>(null);

const manualDeparture = ref<DateTime | null>(null);
const manualArrival = ref<DateTime | null>(null);
const subtitle = ref('');
const title = t('edit_transport_times.title');
const fullTitle = ref('');

function fetchPost() {
    api.posts
        .showPost(props.postId)
        .then((response) => {
            if (!isApiTransportPost(response.data)) {
                throw new Error('Post is not a transport post');
            }
            post.value = response.data;
            manualDeparture.value = post.value?.manualDepartureTime
                ? DateTime.fromISO(post.value.manualDepartureTime)
                : null;
            manualArrival.value = post.value?.manualArrivalTime
                ? DateTime.fromISO(post.value.manualArrivalTime)
                : null;

            getTitles();
        })
        .catch((error) => {
            console.error('Error fetching post:', error);
        });
}

function submit() {
    if (!post.value) {
        return;
    }
    if (
        manualDeparture.value &&
        manualArrival.value &&
        manualArrival.value < manualDeparture.value
    ) {
        alert(t('edit_transport_times.arrival_before_departure_error'));
        return;
    }
    api.posts
        .updateTransportTimes(post.value!.id, {
            manualDepartureTime: manualDeparture.value
                ? manualDeparture.value.toISO()
                : null,
            manualArrivalTime: manualArrival.value
                ? manualArrival.value.toISO()
                : null,
        } as TransportTimesUpdateRequest)
        .then(() => {
            router.visit(`/posts/${post.value?.id}`);
        });
}

function getTitles() {
    if (!post.value) {
        return;
    }
    subtitle.value = `${getBaseText(post.value)} (${prettyDates(post.value)})`;
    fullTitle.value = `${title} · ${subtitle.value}`;
}

function goBack() {
    window.history.back();
}

function departNow() {
    if (!post.value) {
        return;
    }
    manualDeparture.value = DateTime.now().set({ second: 0, millisecond: 0 });
    post.value.manualDepartureTime = manualDeparture.value.toISO();
    const delay = getDepartureDelay(post.value);
    if (delay && delay > 1 && post.value?.destinationStop?.arrivalTime) {
        manualArrival.value = DateTime.fromISO(
            post.value.destinationStop.arrivalTime,
        ).plus({ minutes: delay });
    }
}

function selectDepartureDate(event: Event) {
    const target = event.target as HTMLInputElement;

    const date = DateTime.fromISO(target.value);
    if (manualDeparture.value) {
        manualDeparture.value = manualDeparture.value.set({
            year: date.year,
            month: date.month,
            day: date.day,
        });
    } else {
        manualDeparture.value = date;
    }
}

function selectDepartureTime(event: Event) {
    const target = event.target as HTMLInputElement;

    if (manualDeparture.value) {
        const [hours, minutes] = target.value.split(':').map(Number);
        manualDeparture.value = manualDeparture.value.set({
            hour: hours,
            minute: minutes,
            second: 0,
        });
    } else {
        manualDeparture.value = DateTime.fromISO(target.value);
    }
}

function selectDepartureSeconds(event: Event) {
    const target = event.target as HTMLInputElement;
    const seconds = Number(target.value);

    if (manualDeparture.value) {
        manualDeparture.value = manualDeparture.value.set({
            second: seconds,
        });
    } else {
        manualDeparture.value = DateTime.now().set({ second: seconds });
    }
}

function selectArrivalSeconds(event: Event) {
    const target = event.target as HTMLInputElement;
    const seconds = Number(target.value);

    if (manualArrival.value) {
        manualArrival.value = manualArrival.value.set({
            second: seconds,
        });
    } else {
        manualArrival.value = DateTime.now().set({ second: seconds });
    }
}

function selectArrivalDate(event: Event) {
    const target = event.target as HTMLInputElement;

    const date = DateTime.fromISO(target.value);
    if (manualArrival.value) {
        manualArrival.value = manualArrival.value.set({
            year: date.year,
            month: date.month,
            day: date.day,
        });
    } else {
        manualArrival.value = date;
    }
}

function selectArrivalTime(event: Event) {
    const target = event.target as HTMLInputElement;

    if (manualArrival.value) {
        const [hours, minutes] = target.value.split(':').map(Number);
        manualArrival.value = manualArrival.value.set({
            hour: hours,
            minute: minutes,
            second: 1,
        });
    } else {
        manualArrival.value = DateTime.fromISO(target.value);
    }
}

watch(() => props.postId, fetchPost, { immediate: true });
</script>

<template>
    <Head :title="fullTitle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">{{ title }}</h2>
        </template>
        <div class="card bg-base-100 min-w-full shadow-md">
            <div class="card-body">
                <div class="flex w-full items-center gap-4 pb-0">
                    <div class="text-3xl">⏱️</div>
                    <div class="text-xl">{{ title }}</div>
                </div>
                <div class="pb-8 text-sm opacity-70">
                    {{ subtitle }}
                </div>
                <form>
                    <div class="grid grid-cols-4 gap-4">
                        <div class="col col-span-2 md:col-span-1">
                            <label for="departureDate" class="font-bold">
                                {{ t('edit_transport_times.departure') }}
                            </label>
                            <input
                                id="departureDate"
                                type="date"
                                class="input input-bordered w-full"
                                :value="manualDeparture?.toFormat('yyyy-MM-dd')"
                                @change="selectDepartureDate"
                            />
                        </div>
                        <div class="col col-span-2 md:col-span-1">
                            <div class="flex justify-between opacity-60">
                                <label for="departureTime" class="font-bold">
                                    {{ t('edit_transport_times.time') }}
                                </label>
                                <span class="text-sm">
                                    {{ manualDeparture?.zoneName }}
                                </span>
                            </div>
                            <div class="join w-full">
                                <input
                                    id="departureTime"
                                    type="time"
                                    class="input input-bordered join-item w-full"
                                    :value="manualDeparture?.toFormat('HH:mm')"
                                    @change="selectDepartureTime"
                                />
                                <input
                                    type="number"
                                    min="0"
                                    max="59"
                                    class="input input-bordered join-item w-full"
                                    step="5"
                                    :value="
                                        manualDeparture?.toFormat('ss') || ''
                                    "
                                    @change="selectDepartureSeconds"
                                />
                            </div>
                        </div>
                        <div class="col col-span-2 content-end md:col-span-1">
                            <button
                                class="btn btn-outline w-full"
                                @click.prevent="manualDeparture = null"
                            >
                                <CircleX class="size-5" />
                                {{ t('edit_transport_times.clear_departure') }}
                            </button>
                        </div>
                        <div class="col col-span-2 content-end md:col-span-1">
                            <button
                                class="btn btn-primary w-full"
                                @click.prevent="departNow()"
                            >
                                <PlaneTakeoff class="size-5" />
                                {{ t('edit_transport_times.depart_now') }}
                            </button>
                        </div>
                        <div class="col col-span-2 md:col-span-1">
                            <label for="arrivalDate" class="font-bold">
                                {{ t('edit_transport_times.arrival') }}
                            </label>
                            <input
                                id="arrivalDate"
                                type="date"
                                class="input input-bordered w-full"
                                :value="manualArrival?.toFormat('yyyy-MM-dd')"
                                @change="selectArrivalDate"
                            />
                        </div>
                        <div class="col col-span-2 md:col-span-1">
                            <div class="flex justify-between opacity-60">
                                <label for="departureTime" class="font-bold">
                                    {{ t('edit_transport_times.time') }}
                                </label>
                                <span class="text-sm">
                                    {{ manualArrival?.zoneName }}
                                </span>
                            </div>
                            <div class="join w-full">
                                <input
                                    id="arrivalTime"
                                    type="time"
                                    class="input input-bordered w-full"
                                    :value="manualArrival?.toFormat('HH:mm')"
                                    @change="selectArrivalTime"
                                />
                                <input
                                    type="number"
                                    min="0"
                                    max="59"
                                    class="input input-bordered join-item w-full"
                                    step="5"
                                    :value="manualArrival?.toFormat('ss') || ''"
                                    @change="selectArrivalSeconds"
                                />
                            </div>
                        </div>

                        <div class="col col-span-2 content-end md:col-span-1">
                            <button
                                class="btn btn-outline w-full"
                                @click.prevent="manualArrival = null"
                            >
                                <CircleX class="size-5" />
                                {{ t('edit_transport_times.clear_arrival') }}
                            </button>
                        </div>
                        <div class="col col-span-2 content-end md:col-span-1">
                            <button
                                class="btn btn-primary w-full"
                                @click.prevent="
                                    manualArrival = DateTime.now().set({
                                        second: 30,
                                        millisecond: 0,
                                    })
                                "
                            >
                                <PlaneLanding class="size-5" />
                                {{ t('edit_transport_times.arrive_now') }}
                            </button>
                        </div>
                    </div>
                    <div class="flex w-full justify-end gap-4 pt-8">
                        <button
                            class="btn btn-secondary"
                            @click.prevent="goBack()"
                        >
                            {{ t('verbs.cancel') }}
                        </button>
                        <button
                            class="btn btn-primary"
                            type="submit"
                            @click.prevent="submit()"
                        >
                            {{ t('edit_transport_times.save_times') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
