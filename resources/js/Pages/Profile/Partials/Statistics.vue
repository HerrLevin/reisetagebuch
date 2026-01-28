<script setup lang="ts">
import DurationStats from '@/Pages/Profile/Partials/DurationStats.vue';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { UserDto } from '../../../../types/Api.gen';

const { t } = useI18n();

defineProps({
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});
</script>

<template>
    <!-- todo: add statistics -->
    <div class="flex flex-wrap gap-x-3">
        <a class="link link-hover flex cursor-pointer">
            <b>0</b>&nbsp;<span class="opacity-65">
                {{ t('profile.stats.friends', 0) }}
            </span>
        </a>
        <a class="link link-hover flex cursor-pointer">
            <b>{{ user.statistics.visitedLocationsCount }}</b>
            &nbsp;
            <span class="opacity-65">
                {{
                    t(
                        'profile.stats.locations',
                        user.statistics.visitedLocationsCount,
                    )
                }}
            </span>
        </a>
        <a class="link link-hover flex cursor-pointer">
            <b>{{ user.statistics.transportPostsCount }}</b>
            &nbsp;
            <span class="opacity-65">
                {{
                    t(
                        'profile.stats.trips',
                        user.statistics.transportPostsCount,
                    )
                }}
            </span>
        </a>
    </div>
    <div class="flex flex-wrap gap-x-3">
        <span class="flex">
            <b>{{ user.statistics.postsCount }}</b>
            &nbsp;<span class="opacity-65">
                {{ t('profile.stats.posts', user.statistics.postsCount) }}
            </span>
        </span>
        <span class="flex">
            <b>{{ (user.statistics.travelledDistance / 1000).toFixed(2) }}</b>
            &nbsp;<span class="opacity-65">km</span>
        </span>
        <DurationStats :duration="user.statistics.travelledDuration" />
    </div>
</template>
