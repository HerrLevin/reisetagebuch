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

const user = usePage().props.auth.user;

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
    return route().current('profile.show', user.username);
};
</script>

<template>
    <ul class="card menu bg-base-100 rounded-box w-full">
        <li>
            <Link
                :href="route('dashboard')"
                :class="{ 'menu-active': isDashboardRoute() }"
            >
                <House class="h-5 w-5" />

                Dashboard
            </Link>
        </li>
        <li>
            <Link
                :href="
                    route('posts.create.start', {
                        latitude: latitude,
                        longitude: longitude,
                    })
                "
            >
                <PlusCircle class="h-5 w-5" />
                <span class="dock-label">New Post</span>
            </Link>
            <ul v-if="isPostsCreateRoute()">
                <li>
                    <Link
                        :href="
                            route('posts.create.start', {
                                latitude: latitude,
                                longitude: longitude,
                            })
                        "
                        :class="{ 'menu-active': isVenueRoute() }"
                    >
                        <Pin class="h-5 w-5" />
                        Locations
                    </Link>
                </li>

                <li>
                    <Link
                        :href="
                            route('posts.create.departures', {
                                latitude: latitude,
                                longitude: longitude,
                            })
                        "
                        :class="{ 'menu-active': isDeparturesRoute() }"
                    >
                        <List class="h-5 w-5" />
                        Departures
                    </Link>
                </li>
                <li>
                    <Link
                        :href="
                            route('posts.create.text', {
                                latitude: latitude,
                                longitude: longitude,
                            })
                        "
                        :class="{ 'menu-active': isTextRoute() }"
                    >
                        <PencilSquare class="h-5 w-5" />
                        Text
                    </Link>
                </li>
            </ul>
        </li>
        <li>
            <Link
                :href="route('profile.show', user.username)"
                :class="{ 'menu-active': isProfileRoute() }"
            >
                <UserIcon class="h-5 w-5" />
                Profile
            </Link>
        </li>
        <li>
            <Link
                :href="route('account.edit')"
                :class="{ 'menu-active': isSettingsRoute() }"
            >
                <Cog class="h-5 w-5" />
                Settings
            </Link>
        </li>
        <li class="border-base-300 mt-3 border-t-1">
            <Link :href="route('logout')" method="post"> Log Out</Link>
        </li>
    </ul>
</template>

<style scoped></style>
