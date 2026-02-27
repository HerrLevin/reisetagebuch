<script setup lang="ts">
import NotificationBell from '@/Components/Notifications/NotificationBell.vue';
import { LocationService } from '@/Services/LocationService';
import { useUserStore } from '@/stores/user';
import {
    Filter,
    House,
    List,
    MapPin,
    Plus,
    Route,
    SquarePen,
} from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink, useRoute } from 'vue-router';

const { t } = useI18n();

const user = useUserStore();
const currentRoute = useRoute();

const latitude = ref(0);
const longitude = ref(0);
const intervalId = ref<number | null>(null);

onMounted(() => {
    updateLocation();
    // Update location every minute
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

            if (!isVenueRoute() && !isDeparturesRoute()) {
                LocationService.prefetchLocationData(position);
            }
        })
        .catch(() => {});
}

const isNotificationsRoute = () => {
    return currentRoute.name === 'notifications';
};
const isTripsCreateRoute = () => {
    return (currentRoute.name as string)?.startsWith('trips.create');
};
const isDashboardRoute = () => {
    return currentRoute.name === 'dashboard';
};
const isVenueRoute = () => {
    return currentRoute.name === 'posts.create.start';
};
const isDeparturesRoute = () => {
    return currentRoute.name === 'posts.create.departures';
};
const isFilterRoute = () => {
    return currentRoute.name === 'posts.filter';
};
</script>

<template>
    <div class="md:invisible">
        <div class="fab">
            <div
                tabindex="0"
                role="button"
                class="btn btn-lg btn-circle btn-info"
            >
                <span class="sr-only">
                    {{ t('new_post.title') }}
                </span>
                <Plus class="size-[1.2em]" />
            </div>

            <div class="fab-close">
                {{ t('verbs.close') }}
                <span class="btn btn-circle btn-lg btn-error">✕</span>
            </div>
            <div>
                {{ t('posts.departures') }}
                <RouterLink
                    :to="`/posts/transport/departures?latitude=${latitude}&longitude=${longitude}`"
                    class="btn btn-lg btn-circle"
                >
                    <List />
                </RouterLink>
            </div>
            <div>
                {{ t('posts.locations') }}
                <RouterLink to="/posts/location" class="btn btn-lg btn-circle">
                    <MapPin />
                </RouterLink>
            </div>
            <div>
                {{ t('posts.text') }}
                <RouterLink to="/posts/new" class="btn btn-lg btn-circle">
                    <SquarePen />
                </RouterLink>
            </div>
        </div>
        <div class="dock">
            <RouterLink
                to="/home"
                :class="{ 'dock-active': isDashboardRoute() }"
            >
                <House class="size-[1.2em]" />
                <span class="dock-label">{{ t('app.home') }}</span>
            </RouterLink>
            <template v-if="user.user">
                <RouterLink
                    to="/trips/create"
                    :class="{ 'dock-active': isTripsCreateRoute() }"
                >
                    <Route class="size-[1.2em]" />
                    <span class="dock-label">{{ t('new_route.title') }}</span>
                </RouterLink>
                <RouterLink
                    to="/notifications"
                    :class="{ 'dock-active': isNotificationsRoute() }"
                >
                    <NotificationBell />
                    <span class="dock-label">
                        {{ t('notifications.title') }}
                    </span>
                </RouterLink>
                <RouterLink
                    to="/posts/filter"
                    :class="{ 'dock-active': isFilterRoute() }"
                >
                    <Filter class="size-[1.2em]" />
                    <span class="dock-label">
                        {{ t('posts.filter.title') }}
                    </span>
                </RouterLink>
            </template>
        </div>
    </div>
</template>
<style scoped>
.fab {
    padding-bottom: calc(4rem + env(safe-area-inset-bottom));
}

.fab:focus-within::before {
    content: '';
    position: fixed;
    inset: 0;
    background: radial-gradient(
        circle at bottom right,
        var(--color-base-100),
        transparent 70%
    );
    pointer-events: none;
}

.fab-close {
    bottom: calc(4rem + env(safe-area-inset-bottom));
}
</style>
