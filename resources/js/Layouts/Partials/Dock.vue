<script setup lang="ts">
import NotificationBell from '@/Components/Notifications/NotificationBell.vue';
import { LocationService } from '@/Services/LocationService';
import { useUserStore } from '@/stores/user';
import { Link } from '@inertiajs/vue3';
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

const { t } = useI18n();

const user = useUserStore();

const latitude = ref(0);
const longitude = ref(0);
const intervalId = ref<number | null>(null);

onMounted(() => {
    updateLocation();
    // Update location every 5 minutes
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
    return route().current('notifications');
};
const isTripsCreateRoute = () => {
    return route().current()?.startsWith('trips.create');
};
const isDashboardRoute = () => {
    return route().current('dashboard');
};
const isVenueRoute = () => {
    return route().current('posts.create.start');
};
const isDeparturesRoute = () => {
    return route().current('posts.create.departures');
};
const isFilterRoute = () => {
    return route().current('posts.filter');
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
                <Link
                    :href="
                        route('posts.create.departures', {
                            latitude: latitude,
                            longitude: longitude,
                        })
                    "
                    class="btn btn-lg btn-circle"
                    as="button"
                >
                    <List />
                </Link>
            </div>
            <div>
                {{ t('posts.locations') }}
                <Link
                    :href="route('posts.create.start')"
                    as="button"
                    class="btn btn-lg btn-circle"
                >
                    <MapPin />
                </Link>
            </div>
            <div>
                {{ t('posts.text') }}
                <Link
                    :href="route('posts.create.text')"
                    class="btn btn-lg btn-circle"
                >
                    <SquarePen />
                </Link>
            </div>
        </div>
        <div class="dock">
            <Link
                :href="route('dashboard')"
                :class="{ 'dock-active': isDashboardRoute() }"
                as="button"
            >
                <House class="size-[1.2em]" />
                <span class="dock-label">{{ t('app.home') }}</span>
            </Link>
            <template v-if="user.user">
                <Link
                    :href="route('trips.create')"
                    as="button"
                    :class="{ 'dock-active': isTripsCreateRoute() }"
                >
                    <Route class="size-[1.2em]" />
                    <span class="dock-label">{{ t('new_route.title') }}</span>
                </Link>
                <Link
                    :href="route('notifications')"
                    as="button"
                    :class="{ 'dock-active': isNotificationsRoute() }"
                >
                    <NotificationBell />
                    <span class="dock-label">
                        {{ t('notifications.title') }}
                    </span>
                </Link>
                <Link
                    :href="route('posts.filter')"
                    as="button"
                    :class="{ 'dock-active': isFilterRoute() }"
                >
                    <Filter class="size-[1.2em]" />
                    <span class="dock-label">
                        {{ t('posts.filter.title') }}
                    </span>
                </Link>
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
        rgb(0, 0, 0),
        transparent 70%
    );
    pointer-events: none;
}

.fab-close {
    bottom: calc(4rem + env(safe-area-inset-bottom));
}
</style>
