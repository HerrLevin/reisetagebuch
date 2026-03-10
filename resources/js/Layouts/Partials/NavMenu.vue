<script setup lang="ts">
import NotificationBell from '@/Components/Notifications/NotificationBell.vue';
import { LocationService } from '@/Services/LocationService';
import { useUserStore } from '@/stores/user';
import {
    CirclePlus,
    House,
    List,
    MapPin,
    SquarePen,
    User,
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
const isPostsCreateRoute = () => {
    return (currentRoute.name as string)?.startsWith('posts.create');
};
const isHomeRoute = () => {
    return (currentRoute.name as string)?.startsWith('home');
};
</script>

<template>
    <ul class="menu menu-horizontal px-1">
        <li>
            <RouterLink
                to="/home"
                :class="{ 'btn-active': isHomeRoute() }"
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
                    to="/notifications"
                    class="btn btn-ghost"
                    active-class="btn-active"
                >
                    <NotificationBell class="size-5" />
                    {{ t('notifications.title') }}
                </RouterLink>
            </li>
            <li>
                <RouterLink
                    :to="{
                        name: 'profile.show',
                        params: { username: user.user.username },
                    }"
                    class="btn btn-ghost"
                    active-class="btn-active"
                >
                    <User class="size-5" />
                    {{ t('profile.title') }}
                </RouterLink>
            </li>
        </template>
    </ul>
</template>
