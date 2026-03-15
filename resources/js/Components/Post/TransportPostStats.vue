<script setup lang="ts">
import DurationStats from '@/Pages/Profile/Partials/DurationStats.vue';
import { CircleGauge, Clock, Route } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { TransportPost } from '../../../types/Api.gen';

const { t, locale } = useI18n();

const props = defineProps<{
    post: TransportPost;
}>();

const distance = computed(() => {
    const distance = props.post.distance / 1000;
    return distance.toLocaleString(locale.value, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
});

const speed = computed(() => {
    const duration = props.post.duration / 60 / 60; // convert duration from seconds to hours
    const distance = props.post.distance / 1000; // convert distance from meters to kilometers
    const speed = distance / duration; // speed in km/h
    return (
        speed.toLocaleString(locale.value, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }) +
        ' ' +
        t('posts.speed_unit')
    );
});
</script>

<template>
    <div class="card bg-base-100 min-w-full shadow-md">
        <div class="card-body px-auto py-0">
            <div class="stats">
                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <Route class="inline-block h-8 w-8 stroke-current" />
                    </div>
                    <div class="stat-title">{{ t('posts.distance') }}</div>
                    <div class="stat-value">
                        {{ distance }}
                        <span class="text-sm opacity-50"> km </span>
                    </div>
                </div>

                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <Clock class="inline-block h-8 w-8 stroke-current" />
                    </div>
                    <div class="stat-title">{{ t('posts.duration') }}</div>
                    <div class="stat-value">
                        <DurationStats :duration="post.duration" />
                    </div>
                </div>

                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <CircleGauge
                            class="inline-block h-8 w-8 stroke-current"
                        />
                    </div>
                    <div class="stat-title">{{ t('posts.speed') }}</div>
                    <div class="stat-value">
                        {{ speed }}
                        <span class="text-sm opacity-50">
                            {{ t('posts.speed_unit') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
