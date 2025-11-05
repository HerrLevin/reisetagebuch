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
const isTripRoute = () => {
    return route().current('trips.create');
};
</script>

<template>
    <ul class="menu menu-horizontal px-1">
        <li>
            <Link
                :href="route('dashboard')"
                :class="{ 'btn-active': isDashboardRoute() }"
                class="btn btn-ghost"
            >
                <House class="size-5" />
                {{ t('pages.timeline.title') }}
            </Link>
        </li>
        <template v-if="user">
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
                        <Link
                            :href="
                                route('posts.create.start', {
                                    latitude: latitude,
                                    longitude: longitude,
                                })
                            "
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
                    :class="{ 'btn-active': isTripRoute() }"
                    class="btn btn-ghost"
                >
                    <Route class="size-5" />
                    {{ t('new_route.title') }}
                </Link>
            </li>
        </template>
    </ul>
</template>
