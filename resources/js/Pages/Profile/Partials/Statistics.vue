<script setup lang="ts">
import DurationStats from '@/Pages/Profile/Partials/DurationStats.vue';
import FollowListModal from '@/Pages/Profile/Partials/FollowListModal.vue';
import {
    formatFullNumber,
    formatShortenedNumber,
} from '@/Services/NumberFormattingService';
import { useUserStore } from '@/stores/user';
import { computed, PropType, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { UserDto } from '../../../../types/Api.gen';

const { t, locale } = useI18n();
const authUser = useUserStore();

const props = defineProps({
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});

const canViewFollowList = computed(
    () =>
        props.user.id === authUser.user?.id ||
        !props.user.requiresFollowRequest ||
        props.user.isFollowed,
);

const followListModal = useTemplateRef('followListModal');

function openFollowList(mode: 'followers' | 'following') {
    if (!canViewFollowList.value) {
        return;
    }
    followListModal.value?.open(mode);
}
</script>

<template>
    <div class="flex flex-wrap gap-x-3">
        <button
            type="button"
            class="tooltip flex"
            :class="
                canViewFollowList
                    ? 'link link-hover cursor-pointer'
                    : 'cursor-default'
            "
            :data-tip="formatFullNumber(user.statistics.followersCount, locale)"
            @click="openFollowList('followers')"
        >
            <b>{{
                formatShortenedNumber(user.statistics.followersCount, locale)
            }}</b>
            &nbsp;
            <span class="opacity-65">
                {{
                    t('profile.stats.followers', user.statistics.followersCount)
                }}
            </span>
        </button>
        <button
            type="button"
            class="tooltip flex"
            :class="
                canViewFollowList
                    ? 'link link-hover cursor-pointer'
                    : 'cursor-default'
            "
            :data-tip="formatFullNumber(user.statistics.followingCount, locale)"
            @click="openFollowList('following')"
        >
            <b>{{
                formatShortenedNumber(user.statistics.followingCount, locale)
            }}</b>
            &nbsp;
            <span class="opacity-65">
                {{
                    t(
                        'profile.stats.followings',
                        user.statistics.followingCount,
                    )
                }}
            </span>
        </button>
        <a
            class="link link-hover tooltip flex cursor-pointer"
            :data-tip="
                formatFullNumber(user.statistics.visitedLocationsCount, locale)
            "
        >
            <b>{{
                formatShortenedNumber(
                    user.statistics.visitedLocationsCount,
                    locale,
                )
            }}</b>
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
        <a
            class="link link-hover tooltip flex cursor-pointer"
            :data-tip="
                formatFullNumber(user.statistics.transportPostsCount, locale)
            "
        >
            <b>{{
                formatShortenedNumber(
                    user.statistics.transportPostsCount,
                    locale,
                )
            }}</b>
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
        <span
            class="tooltip flex"
            :data-tip="formatFullNumber(user.statistics.postsCount, locale)"
        >
            <b>{{
                formatShortenedNumber(user.statistics.postsCount, locale)
            }}</b>
            &nbsp;<span class="opacity-65">
                {{ t('profile.stats.posts', user.statistics.postsCount) }}
            </span>
        </span>
        <span
            class="tooltip flex"
            :data-tip="
                formatFullNumber(
                    user.statistics.travelledDistance / 1000,
                    locale,
                ) + ' km'
            "
        >
            <b>
                {{
                    formatShortenedNumber(
                        user.statistics.travelledDistance / 1000,
                        locale,
                    )
                }}
            </b>
            &nbsp;<span class="opacity-65">km</span>
        </span>
        <DurationStats :duration="user.statistics.travelledDuration" />
    </div>
    <FollowListModal ref="followListModal" :user-id="user.id" />
</template>
