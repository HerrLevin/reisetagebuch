<script setup lang="ts">
import { Clock } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { PropType } from 'vue';

const selectedTime = defineModel<DateTime | null>();
defineEmits(['update:model-value']);

defineProps({
    modelValue: {
        type: Object as PropType<DateTime | null>,
        default: null,
    },
    buttonClass: {
        type: String,
        default: 'btn btn-neutral',
    },
    className: {
        type: String,
        default: 'dropdown-end',
    },
    displayTime: {
        type: Boolean,
        default: false,
    },
});

function selectDate(date: EventTarget | null) {
    if (date && date instanceof HTMLInputElement) {
        const dateObject = DateTime.fromISO(date.value);
        selectedTime.value = selectedTime.value
            ? selectedTime.value.set({
                  year: dateObject.year,
                  month: dateObject.month,
                  day: dateObject.day,
              })
            : dateObject;
    }
}

function selectTime(time: EventTarget | null) {
    if (time && time instanceof HTMLInputElement) {
        const timeObject = DateTime.fromISO(time.value);
        selectedTime.value = selectedTime.value
            ? selectedTime.value.set({
                  hour: timeObject.hour,
                  minute: timeObject.minute,
              })
            : timeObject;
    }
}
</script>

<template>
    <details class="dropdown" :class="className">
        <summary :class="buttonClass">
            <Clock class="h-4 w-4" />
            <span v-if="displayTime && selectedTime">
                {{ selectedTime.toLocaleString(DateTime.DATETIME_MED) }}
            </span>
        </summary>
        <div
            class="dropdown-content bg-base-100 rounded-box w-52 p-3 shadow-lg"
        >
            <input
                type="date"
                class="input input-bordered w-full"
                :value="selectedTime?.toFormat('yyyy-MM-dd')"
                @change="selectDate($event.target)"
            />
            <input
                type="time"
                class="input input-bordered mt-2 w-full"
                :value="selectedTime?.toFormat('HH:mm')"
                @change="selectTime($event.target)"
            />
            <div class="mt-2 grid grid-cols-2 gap-2">
                <button
                    type="button"
                    class="btn btn-secondary w-full"
                    @click="selectedTime = DateTime.now()"
                >
                    {{ $t('new_post.departures_filter.now') }}
                </button>
                <button
                    type="button"
                    class="btn btn-secondary w-full"
                    @click="selectedTime = null"
                >
                    {{ $t('new_post.departures_filter.clear') }}
                </button>
            </div>
        </div>
    </details>
</template>
