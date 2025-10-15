<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getBaseText, prettyDates } from '@/Services/PostTextService';
import { TransportPost } from '@/types/PostTypes';
import { Head, router } from '@inertiajs/vue3';
import { CircleX, PlaneLanding, PlaneTakeoff } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { PropType, ref } from 'vue';

const props = defineProps({
    post: {
        type: Object as PropType<TransportPost> | null,
        required: true,
    },
});

const manualDeparture = ref<DateTime | null>(
    props.post?.manualDepartureTime
        ? DateTime.fromISO(props.post.manualDepartureTime)
        : null,
);
const manualArrival = ref<DateTime | null>(
    props.post?.manualArrivalTime
        ? DateTime.fromISO(props.post.manualArrivalTime)
        : null,
);

function submit() {
    if (
        manualDeparture.value &&
        manualArrival.value &&
        manualArrival.value < manualDeparture.value
    ) {
        alert('Arrival time cannot be before departure time.');
        return;
    }
    router.put(route('posts.update.transport-times', props.post?.id), {
        manualDepartureTime: manualDeparture.value
            ? manualDeparture.value.toISO()
            : null,
        manualArrivalTime: manualArrival.value
            ? manualArrival.value.toISO()
            : null,
    });
}

const subtitle = `${getBaseText(props.post)} (${prettyDates(props.post)})`;
const title = `Edit Transport Times`;
const fullTitle = `${title} · ${subtitle}`;

function goBack() {
    window.history.back();
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
        });
    } else {
        manualDeparture.value = DateTime.fromISO(target.value);
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
        });
    } else {
        manualArrival.value = DateTime.fromISO(target.value);
    }
}
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
                            <label for="departureDate" class="font-bold"
                                >Departure</label
                            >
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
                                    Time
                                </label>
                                <span class="text-sm">{{
                                    manualDeparture?.zoneName
                                }}</span>
                            </div>
                            <input
                                id="departureTime"
                                type="time"
                                class="input input-bordered w-full"
                                :value="manualDeparture?.toFormat('HH:mm')"
                                @change="selectDepartureTime"
                            />
                        </div>
                        <div class="col col-span-2 content-end md:col-span-1">
                            <button
                                class="btn btn-outline w-full"
                                @click.prevent="manualDeparture = null"
                            >
                                <CircleX class="size-5" />
                                Clear Departure
                            </button>
                        </div>
                        <div class="col col-span-2 content-end md:col-span-1">
                            <button
                                class="btn btn-primary w-full"
                                @click.prevent="
                                    manualDeparture = DateTime.now()
                                "
                            >
                                <PlaneTakeoff class="size-5" />
                                Depart Now
                            </button>
                        </div>
                        <div class="col col-span-2 md:col-span-1">
                            <label for="arrivalDate" class="font-bold"
                                >Arrival</label
                            >
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
                                    Time
                                </label>
                                <span class="text-sm">
                                    {{ manualArrival?.zoneName }}
                                </span>
                            </div>
                            <input
                                id="departureTime"
                                type="time"
                                class="input input-bordered w-full"
                                :value="manualArrival?.toFormat('HH:mm')"
                                @change="selectArrivalTime"
                            />
                        </div>

                        <div class="col col-span-2 content-end md:col-span-1">
                            <button
                                class="btn btn-outline w-full"
                                @click.prevent="manualArrival = null"
                            >
                                <CircleX class="size-5" />
                                Clear Arrival
                            </button>
                        </div>
                        <div class="col col-span-2 content-end md:col-span-1">
                            <button
                                class="btn btn-primary w-full"
                                @click.prevent="manualArrival = DateTime.now()"
                            >
                                <PlaneLanding class="size-5" />
                                Arrive Now
                            </button>
                        </div>
                    </div>
                    <div class="flex w-full justify-end gap-4 pt-8">
                        <button
                            class="btn btn-secondary"
                            @click.prevent="goBack()"
                        >
                            Cancel
                        </button>
                        <button
                            class="btn btn-primary"
                            type="submit"
                            @click.prevent="submit()"
                        >
                            Save Times
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
