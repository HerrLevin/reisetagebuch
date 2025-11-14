<script setup lang="ts">
import { useNotificationStore } from '@/stores/notifications';
import { Bell } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';

const store = useNotificationStore();
const { unreadCount } = storeToRefs(store);

onMounted(() => {
    store.fetchUnreadCount();

    setInterval(() => {
        store.fetchUnreadCount();
    }, 30000);
});
</script>

<template>
    <div class="indicator" @click="store.fetchNotifications()">
        <Bell class="size-5" />
        <span
            v-show="unreadCount > 0"
            class="badge indicator-item badge-primary badge-xs"
        >
            {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
    </div>
</template>
