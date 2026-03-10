<script setup lang="ts">
import { useNotificationStore } from '@/stores/notifications';
import { useUserStore } from '@/stores/user';
import { Bell } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { onMounted, onUnmounted, ref } from 'vue';

const store = useNotificationStore();
const user = useUserStore();
const { unreadCount } = storeToRefs(store);

const interval = ref<number | null>(null);

onMounted(() => {
    if (user.user) {
        store.fetchUnreadCount();

        interval.value = setInterval(() => {
            store.fetchUnreadCount();
        }, 30000);
    }
});

onUnmounted(() => {
    if (interval.value !== null) {
        clearInterval(interval.value);
    }
});
</script>

<template>
    <div v-if="user.user" class="indicator" @click="store.fetchNotifications()">
        <Bell class="size-5" />
        <span
            v-show="unreadCount > 0"
            class="badge indicator-item badge-primary badge-xs"
        >
            {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
    </div>
</template>
