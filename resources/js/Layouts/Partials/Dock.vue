<script setup lang="ts">
import { LocationService } from '@/Services/LocationService';
import { Link, usePage } from '@inertiajs/vue3';
import {
    CirclePlus,
    House,
    List,
    MapPin,
    Route,
    SquarePen,
    User,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const latitude = ref(0);
const longitude = ref(0);

onMounted(() => {
    LocationService.getPosition(!!usePage().props.auth.user)
        .then((position) => {
            latitude.value = position.coords.latitude;
            longitude.value = position.coords.longitude;

            if (!isVenueRoute() && !isDeparturesRoute()) {
                LocationService.prefetchLocationData(position);
            }
        })
        .catch(() => {});
});

const user = usePage().props.auth.user ?? null;

const isTripsCreateRoute = () => {
    return route().current()?.startsWith('trips.create');
};
const isPostsCreateRoute = () => {
    return route().current()?.startsWith('posts.create');
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
const isTextRoute = () => {
    return route().current('posts.create.text');
};
const isProfileRoute = () => {
    return route().current('profile.show', user?.username ?? '');
};

const defaultNewPostRoute = () => {
    if (user.settings?.default_new_post_view === 'text') {
        return 'posts.create.text';
    } else if (user.settings?.default_new_post_view === 'departures') {
        return 'posts.create.departures';
    } else {
        return 'posts.create.start';
    }
};
</script>

<template>
    <div class="md:invisible">
        <div
            v-if="user"
            class="over-dock"
            :class="{ invisible: !isPostsCreateRoute() }"
        >
            <div class="join">
                <Link
                    :href="
                        route('posts.create.start', {
                            latitude: latitude,
                            longitude: longitude,
                        })
                    "
                    as="button"
                    class="btn btn-soft btn-info btn-s join-item"
                    :class="{ 'btn-active': isVenueRoute() }"
                >
                    <MapPin />
                </Link>
                <Link
                    :href="
                        route('posts.create.departures', {
                            latitude: latitude,
                            longitude: longitude,
                        })
                    "
                    class="btn btn-soft btn-info btn-s join-item"
                    :class="{ 'btn-active': isDeparturesRoute() }"
                    as="button"
                >
                    <List />
                </Link>
                <Link
                    :href="route('posts.create.text')"
                    class="btn btn-soft btn-info btn-s join-item"
                    :class="{ 'btn-active': isTextRoute() }"
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
                <span class="dock-label">Home</span>
            </Link>
            <template v-if="user">
                <Link
                    :href="
                        route(defaultNewPostRoute(), {
                            latitude: latitude,
                            longitude: longitude,
                        })
                    "
                    as="button"
                    :class="{ 'dock-active': isPostsCreateRoute() }"
                >
                    <CirclePlus class="size-[1.2em]" />
                    <span class="dock-label">New Post</span>
                </Link>
                <Link
                    :href="route('trips.create')"
                    as="button"
                    :class="{ 'dock-active': isTripsCreateRoute() }"
                >
                    <Route class="size-[1.2em]" />
                    <span class="dock-label">New Trip</span>
                </Link>
                <Link
                    :href="route('profile.show', user.username)"
                    as="button"
                    :class="{ 'dock-active': isProfileRoute() }"
                >
                    <User class="size-[1.2em]" />
                    <span class="dock-label">Profile</span>
                </Link>
            </template>
        </div>
    </div>
</template>
<style scoped>
.over-dock {
    position: fixed;
    right: calc(0.25rem * 0);
    bottom: calc(0.5rem * 0);
    left: calc(0.25rem * 0);
    z-index: 1;
    display: flex;
    width: 100%;
    flex-direction: row;
    align-items: top;
    justify-content: space-around;
    color: currentColor;
    height: calc(7rem + env(safe-area-inset-bottom));
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
