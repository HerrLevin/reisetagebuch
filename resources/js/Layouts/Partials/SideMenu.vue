<script setup lang="ts">
import { LocationService } from '@/Services/LocationService';
import { Link, usePage } from '@inertiajs/vue3';
import {
    CirclePlus,
    Cog,
    House,
    List,
    MapPin,
    Route,
    SquarePen,
    User,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const latitude = ref(0);
const longitude = ref(0);

const user = usePage().props.auth.user ?? null;

onMounted(() => {
    LocationService.getPosition(!!user)
        .then((position) => {
            latitude.value = position.coords.latitude;
            longitude.value = position.coords.longitude;
        })
        .catch(() => {});
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
    return route().current('profile.show', user?.username ?? '');
};
const isTripRoute = () => {
    return route().current('trips.create');
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
    <ul class="card menu bg-base-100 rounded-box w-full">
        <li>
            <Link
                :href="route('dashboard')"
                :class="{ 'menu-active': isDashboardRoute() }"
            >
                <House class="size-5" />
                {{ t('pages.timeline.title') }}
            </Link>
        </li>
        <template v-if="user">
            <li>
                <Link
                    :href="
                        route(defaultNewPostRoute(), {
                            latitude: latitude,
                            longitude: longitude,
                        })
                    "
                >
                    <CirclePlus class="size-5" />
                    <span class="dock-label">{{ t('new_post.title') }}</span>
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
                            <MapPin class="size-5" />
                            {{ t('posts.locations') }}
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
                            <List class="size-5" />
                            {{ t('posts.departures') }}
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
                            <SquarePen class="size-5" />
                            {{ t('posts.text') }}
                        </Link>
                    </li>
                </ul>
            </li>
            <li>
                <Link
                    :href="route('trips.create')"
                    :class="{ 'menu-active': isTripRoute() }"
                >
                    <Route class="size-5" />
                    {{ t('new_route.title') }}
                </Link>
            </li>
            <li>
                <Link
                    :href="route('profile.show', user?.username)"
                    :class="{ 'menu-active': isProfileRoute() }"
                >
                    <User class="size-5" />
                    {{ t('profile.title') }}
                </Link>
            </li>
            <li>
                <Link
                    :href="route('account.edit')"
                    :class="{ 'menu-active': isSettingsRoute() }"
                >
                    <Cog class="size-5" />
                    {{ t('settings.title') }}
                </Link>
            </li>
            <li class="border-base-300 mt-3 border-t-1">
                <Link :href="route('logout')" method="post">
                    {{ t('app.logout') }}
                </Link>
            </li>
        </template>
    </ul>
</template>
