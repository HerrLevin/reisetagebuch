<script setup lang="ts">
import NotificationBell from '@/Components/Notifications/NotificationBell.vue';
import { LocationService } from '@/Services/LocationService';
import { useUserStore } from '@/stores/user';
import {
    CirclePlus,
    Filter,
    House,
    List,
    MapPin,
    Route,
    SquarePen,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink, useRoute } from 'vue-router';

const { t } = useI18n();

const latitude = ref(0);
const longitude = ref(0);

const user = useUserStore();
const currentRoute = useRoute();
const intervalId = ref<number | null>(null);

onMounted(() => {
    updateLocation();
    intervalId.value = setInterval(updateLocation, 60 * 1000);
});

onUnmounted(() => {
    if (intervalId.value) {
        clearInterval(intervalId.value);
    }
});

function updateLocation() {
    LocationService.getPosition(!!user.user)
        .then((position) => {
            latitude.value = position.coords.latitude;
            longitude.value = position.coords.longitude;
        })
        .catch(() => {});
}
const isNotificationRoute = () => {
    return currentRoute.name === 'notifications';
};
const isPostsCreateRoute = () => {
    return (currentRoute.name as string)?.startsWith('posts.create');
};
const isDashboardRoute = () => {
    return currentRoute.name === 'timeline';
};
const isTripRoute = () => {
    return currentRoute.name === 'trips.create';
};
const isFilterRoute = () => {
    return currentRoute.name === 'posts.filter';
};
</script>

<template>
    <ul class="menu menu-horizontal px-1">
        <li>
            <RouterLink
                to="/home"
                :class="{ 'btn-active': isDashboardRoute() }"
                class="btn btn-ghost"
            >
                <House class="size-5" />
                {{ t('pages.timeline.title') }}
            </RouterLink>
        </li>
        <template v-if="user.user">
            <li class="dropdown">
                <div
                    tabindex="0"
                    role="button"
                    class="btn btn-ghost"
                    :class="{ 'btn-active': isPostsCreateRoute() }"
                >
                    <CirclePlus class="me-1 size-5" />
                    {{ t('new_post.title') }}
                </div>
                <ul
                    tabindex="-1"
                    class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow"
                >
                    <li>
                        <RouterLink to="/posts/location">
                            <MapPin class="size-5" />
                            {{ t('posts.locations') }}
                        </RouterLink>
                    </li>
                    <li>
                        <RouterLink
                            :to="`/posts/transport/departures?latitude=${latitude}&longitude=${longitude}`"
                        >
                            <List class="size-5" />
                            {{ t('posts.departures') }}
                        </RouterLink>
                    </li>
                    <li>
                        <RouterLink
                            :to="`/posts/new?latitude=${latitude}&longitude=${longitude}`"
                        >
                            <SquarePen class="size-5" />
                            {{ t('posts.text') }}
                        </RouterLink>
                    </li>
                </ul>
            </li>
            <li>
                <RouterLink
                    to="/trips/create"
                    :class="{ 'btn-active': isTripRoute() }"
                    class="btn btn-ghost"
                >
                    <Route class="size-5" />
                    {{ t('new_route.title') }}
                </RouterLink>
            </li>
            <li>
                <RouterLink
                    to="/notifications"
                    :class="{ 'btn-active': isNotificationRoute() }"
                    class="btn btn-ghost"
                >
                    <NotificationBell class="size-5" />
                    {{ t('notifications.title') }}
                </RouterLink>
            </li>
            <li>
                <RouterLink
                    to="/posts/filter"
                    :class="{ 'btn-active': isFilterRoute() }"
                    class="btn btn-ghost"
                >
                    <Filter class="size-5" />
                    {{ t('posts.filter.title') }}
                </RouterLink>
            </li>
        </template>
    </ul>
</template>
