<script setup lang="ts">
import TransitousSearch from '@/Pages/NewPostDialog/Partials/TransitousSearch.vue';
import { TripLocation } from '@/types/TripCreation';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { GeocodeResponseEntry } from '../../../../types/Api.gen';
const { t } = useI18n();

defineProps({
    stops: {
        type: Array as PropType<TripLocation[]>,
        required: true,
    },
});

type StopUpdateEvent = {
    stop: TripLocation;
    value: GeocodeResponseEntry;
};

const emit = defineEmits<{
    (e: 'update:start', value: GeocodeResponseEntry): void;
    (e: 'update:end', value: GeocodeResponseEntry): void;
    (e: 'update:stop', value: StopUpdateEvent): void;
    (e: 'add-stop'): void;
}>();
</script>

<template>
    <div class="card bg-base-100 min-w-full p-0 shadow-md">
        <div class="card-body">
            <div class="grid grid-cols-1 gap-8">
                <TransitousSearch @select="emit('update:start', $event)" />
            </div>
            <div
                v-for="stop in stops"
                :key="stop.order"
                class="grid grid-cols-1 gap-8"
            >
                <TransitousSearch
                    @select="
                        emit('update:stop', {
                            stop,
                            value: $event,
                        } as StopUpdateEvent)
                    "
                />
            </div>
            <a class="link" @click.prevent="emit('add-stop')">
                {{ t('new_route.add_stop') }}
            </a>
            <div class="grid grid-cols-1 gap-8">
                <TransitousSearch @select="emit('update:end', $event)" />
            </div>
        </div>
    </div>
</template>
