<script setup lang="ts">
import { DateTime } from 'luxon';
import { useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

withDefaults(
    defineProps<{
        title: string;
        subtitle?: string;
        departureLabel: string;
        arrivalLabel: string;
        showDeparture?: boolean;
        showArrival?: boolean;
        arrivalFirst?: boolean;
    }>(),
    {
        subtitle: '',
        showDeparture: true,
        showArrival: true,
        arrivalFirst: false,
    },
);

const departure = defineModel<DateTime | null>('departure', { default: null });
const arrival = defineModel<DateTime | null>('arrival', { default: null });

const emit = defineEmits<{
    save: [];
    cancel: [];
}>();

const dialog = useTemplateRef('dialog');
let saved = false;

function show() {
    saved = false;
    dialog.value?.showModal();
}

function close() {
    dialog.value?.close();
}

function onClose() {
    if (!saved) {
        emit('cancel');
    }
    saved = false;
}

function submit() {
    if (departure.value && arrival.value && arrival.value < departure.value) {
        alert(t('edit_transport_times.arrival_before_departure_error'));
        return;
    }
    saved = true;
    emit('save');
    close();
}

function selectDepartureDate(event: Event) {
    const target = event.target as HTMLInputElement;
    const date = DateTime.fromISO(target.value);
    departure.value = departure.value
        ? departure.value.set({
              year: date.year,
              month: date.month,
              day: date.day,
          })
        : date;
}

function selectDepartureTime(event: Event) {
    const target = event.target as HTMLInputElement;
    if (departure.value) {
        const [hours, minutes] = target.value.split(':').map(Number);
        departure.value = departure.value.set({
            hour: hours,
            minute: minutes,
            second: 0,
        });
    } else {
        departure.value = DateTime.fromISO(target.value);
    }
}

function selectDepartureSeconds(event: Event) {
    const target = event.target as HTMLInputElement;
    const seconds = Number(target.value);
    departure.value = departure.value
        ? departure.value.set({ second: seconds })
        : DateTime.now().set({ second: seconds });
}

function selectArrivalDate(event: Event) {
    const target = event.target as HTMLInputElement;
    const date = DateTime.fromISO(target.value);
    arrival.value = arrival.value
        ? arrival.value.set({
              year: date.year,
              month: date.month,
              day: date.day,
          })
        : date;
}

function selectArrivalTime(event: Event) {
    const target = event.target as HTMLInputElement;
    if (arrival.value) {
        const [hours, minutes] = target.value.split(':').map(Number);
        arrival.value = arrival.value.set({
            hour: hours,
            minute: minutes,
            second: 0,
        });
    } else {
        arrival.value = DateTime.fromISO(target.value);
    }
}

function selectArrivalSeconds(event: Event) {
    const target = event.target as HTMLInputElement;
    const seconds = Number(target.value);
    arrival.value = arrival.value
        ? arrival.value.set({ second: seconds })
        : DateTime.now().set({ second: seconds });
}

defineExpose({ show, close });
</script>

<template>
    <dialog ref="dialog" class="modal" @close="onClose">
        <div class="modal-box max-w-xl">
            <h3 class="text-lg font-bold">{{ title }}</h3>
            <p v-if="subtitle" class="pt-1 text-sm opacity-70">
                {{ subtitle }}
            </p>
            <form class="mt-4" @submit.prevent="submit">
                <div class="flex flex-col gap-6">
                    <div
                        v-if="showDeparture"
                        class="flex flex-wrap items-end gap-4"
                        :class="arrivalFirst ? 'order-2' : 'order-1'"
                    >
                        <div class="min-w-36 flex-1">
                            <label
                                for="editTimesDepartureDate"
                                class="font-bold"
                            >
                                {{ departureLabel }}
                            </label>
                            <input
                                id="editTimesDepartureDate"
                                type="date"
                                class="input input-bordered w-full"
                                :value="departure?.toFormat('yyyy-MM-dd')"
                                @change="selectDepartureDate"
                            />
                        </div>
                        <div class="min-w-40 flex-1">
                            <div class="flex justify-between opacity-60">
                                <label
                                    for="editTimesDepartureTime"
                                    class="font-bold"
                                >
                                    {{ t('edit_transport_times.time') }}
                                </label>
                                <span class="text-sm">
                                    {{ departure?.zoneName }}
                                </span>
                            </div>
                            <div class="join w-full">
                                <input
                                    id="editTimesDepartureTime"
                                    type="time"
                                    class="input input-bordered join-item w-full"
                                    :value="departure?.toFormat('HH:mm')"
                                    @change="selectDepartureTime"
                                />
                                <input
                                    type="number"
                                    min="0"
                                    max="59"
                                    step="5"
                                    class="input input-bordered join-item w-full"
                                    :value="departure?.toFormat('ss') || ''"
                                    @change="selectDepartureSeconds"
                                />
                            </div>
                        </div>
                        <slot name="departure-extra" />
                    </div>

                    <div
                        v-if="showArrival"
                        class="flex flex-wrap items-end gap-4"
                        :class="arrivalFirst ? 'order-1' : 'order-2'"
                    >
                        <div class="min-w-36 flex-1">
                            <label for="editTimesArrivalDate" class="font-bold">
                                {{ arrivalLabel }}
                            </label>
                            <input
                                id="editTimesArrivalDate"
                                type="date"
                                class="input input-bordered w-full"
                                :value="arrival?.toFormat('yyyy-MM-dd')"
                                @change="selectArrivalDate"
                            />
                        </div>
                        <div class="min-w-40 flex-1">
                            <div class="flex justify-between opacity-60">
                                <label
                                    for="editTimesArrivalTime"
                                    class="font-bold"
                                >
                                    {{ t('edit_transport_times.time') }}
                                </label>
                                <span class="text-sm">
                                    {{ arrival?.zoneName }}
                                </span>
                            </div>
                            <div class="join w-full">
                                <input
                                    id="editTimesArrivalTime"
                                    type="time"
                                    class="input input-bordered join-item w-full"
                                    :value="arrival?.toFormat('HH:mm')"
                                    @change="selectArrivalTime"
                                />
                                <input
                                    type="number"
                                    min="0"
                                    max="59"
                                    step="5"
                                    class="input input-bordered join-item w-full"
                                    :value="arrival?.toFormat('ss') || ''"
                                    @change="selectArrivalSeconds"
                                />
                            </div>
                        </div>
                        <slot name="arrival-extra" />
                    </div>
                </div>

                <div class="modal-action">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        @click="close"
                    >
                        {{ t('verbs.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ t('edit_transport_times.save_times') }}
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>{{ t('verbs.cancel') }}</button>
        </form>
    </dialog>
</template>
