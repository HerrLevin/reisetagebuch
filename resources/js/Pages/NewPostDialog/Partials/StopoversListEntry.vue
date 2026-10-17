<script setup lang="ts">
import TimeDisplay from '@/Pages/NewPostDialog/Partials/TimeDisplay.vue';
import MotisTimeService from '@/Services/MotisTimeService';
import { defineProps, onMounted, PropType, ref } from 'vue';
import { StopPlaceDto } from '../../../../types/Api.gen';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    stop: {
        type: Object as PropType<StopPlaceDto>,
        default: () => ({}),
    },
    mode: {
        type: String,
        default: '',
    },
    shortName: {
        type: String,
        default: '',
    },
    realTime: {
        type: Boolean,
        default: false,
    },
    selected: {
        type: Boolean,
        default: false,
    },
});

const { t } = useI18n();

const timeService = new MotisTimeService(props.stop);
const plannedTime = ref(timeService.plannedTimeString);
const time = ref(timeService.timeString);
const delay = ref(timeService.delay);

const row = ref<HTMLElement | null>(null);

onMounted(() => {
    if (props.selected) {
        row.value?.scrollIntoView({ behavior: 'smooth' });
    }
});
</script>

<template>
    <li
        ref="row"
        class="list-row hover:bg-base-200 cursor-pointer grid-cols-13 gap-0 py-2 md:px-8"
        :class="{
            'bg-base-300': selected,
            'text-error line-through': stop.cancelled,
        }"
    >
        <div class="col col-span-2 md:col-span-1">
            <TimeDisplay
                :planned-time="plannedTime"
                :time="time"
                :delay="delay"
                :real-time="realTime"
            />
        </div>
        <div class="col col-span-9 md:col-span-10">
            {{ stop.name }}
        </div>
        <div class="col col-span-2 text-right">
            <div
                v-if="stop.track !== stop.scheduledTrack"
                class="text-error line-through"
            >
                {{
                    t('common.platform_short', {
                        platform: stop.scheduledTrack,
                    })
                }}
            </div>
            <div v-if="stop.track">
                {{ t('common.platform_short', { platform: stop.track }) }}
            </div>
        </div>
    </li>
</template>

<style scoped>
.badge {
    height: auto;
}
</style>
