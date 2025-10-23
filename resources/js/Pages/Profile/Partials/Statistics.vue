<script setup lang="ts">
import type { UserDto } from '@/types';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';

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
            <b>3.207</b>&nbsp;<span class="opacity-65">
                {{ t('profile.stats.locations', 1234) }}
            </span>
        </a>
        <a class="link link-hover flex cursor-pointer">
            <b>12</b>&nbsp;<span class="opacity-65">
                {{ t('profile.stats.trips', 12) }}
            </span>
        </a>
    </div>
    <!-- todo: add statistics -->
    <div class="flex flex-wrap gap-x-3">
        <span class="flex">
            <b>1.234</b>&nbsp;<span class="opacity-65">
                {{ t('profile.stats.posts', 1234) }}
            </span>
        </span>
        <span class="flex">
            <b>129.182</b>&nbsp;<span class="opacity-65">km</span>
        </span>
        <span class="flex">
            <b>64</b>&nbsp;<span class="opacity-65">d</span>&nbsp;
            <b>21</b>&nbsp;<span class="opacity-65">h</span>&nbsp;
            <b>28</b>&nbsp;<span class="opacity-65">m</span>
        </span>
    </div>
</template>
