<script setup lang="ts">
import Cog from '@/Icons/Cog.vue';
import House from '@/Icons/House.vue';
import List from '@/Icons/List.vue';
import PencilSquare from '@/Icons/PencilSquare.vue';
import Pin from '@/Icons/Pin.vue';
import PlusCircle from '@/Icons/PlusCircle.vue';
import UserIcon from '@/Icons/UserIcon.vue';
import { LocationService } from '@/Services/LocationService';
import { Link, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const latitude = ref(0);
const longitude = ref(0);

onMounted(() => {
    LocationService.getPosition().then((position) => {
        latitude.value = position.coords.latitude;
        longitude.value = position.coords.longitude;

        if (!isVenueRoute() && !isDeparturesRoute()) {
            LocationService.prefetchLocationData(position);
        }
    });
});

const user = usePage().props.auth.user ?? null;

const isPostsCreateRoute = () => {
    return route().current()?.startsWith('posts.create');
};
const isDashboardRoute = () => {
    return route().current('dashboard');
};
const isSettingsRoute = () => {
    return route().current('account.edit');
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
                    <Pin />
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
                    <PencilSquare />
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
                    <PlusCircle class="size-[1.2em]" />
                    <span class="dock-label">New Post</span>
                </Link>
                <Link
                    :href="route('profile.show', user.username)"
                    as="button"
                    :class="{ 'dock-active': isProfileRoute() }"
                >
                    <UserIcon class="size-[1.2em]" />
                    <span class="dock-label">Profile</span>
                </Link>
                <Link
                    :href="route('account.edit')"
                    as="button"
                    :class="{ 'dock-active': isSettingsRoute() }"
                >
                    <Cog class="size-[1.2em]" />
                    <span class="dock-label">Settings</span>
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
