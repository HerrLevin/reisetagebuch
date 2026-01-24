<script setup lang="ts">
import { useNotificationStore } from '@/stores/notifications';
import { useUserStore } from '@/stores/user';
import { Bell } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';

const store = useNotificationStore();
const user = useUserStore();
const { unreadCount } = storeToRefs(store);

onMounted(() => {
    if (user.user) {
        store.fetchUnreadCount();

        setInterval(() => {
            store.fetchUnreadCount();
        }, 30000);
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
