<script setup lang="ts">
import Cog from '@/Icons/Cog.vue';
import House from '@/Icons/House.vue';
import List from '@/Icons/List.vue';
import PencilSquare from '@/Icons/PencilSquare.vue';
import Pin from '@/Icons/Pin.vue';
import PlusCircle from '@/Icons/PlusCircle.vue';
import { LocationService } from '@/Services/LocationService';
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const latitude = ref(0);
const longitude = ref(0);

onMounted(() => {
    LocationService.getPosition().then((position) => {
        latitude.value = position.coords.latitude;
        longitude.value = position.coords.longitude;
    });
});

const isPostsCreateRoute = () => {
    return route().current()?.startsWith('posts.create');
};
const isDashboardRoute = () => {
    return route().current('dashboard');
};
const isSettingsRoute = () => {
    return route().current('profile.edit');
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
</script>

<template>
    <div class="md:hidden">
        <div class="over-dock" :class="{ hidden: !isPostsCreateRoute() }">
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

            <Link
                :href="
                    route('posts.create.start', {
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
                :href="route('profile.edit')"
                as="button"
                :class="{ 'dock-active': isSettingsRoute() }"
            >
                <Cog class="size-[1.2em]" />
                <span class="dock-label">Settings</span>
            </Link>
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
