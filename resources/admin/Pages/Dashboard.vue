<script setup lang="ts">
import { api } from '@admin/api';
import PageHeader from '@admin/Components/PageHeader.vue';
import StatCard from '@admin/Components/StatCard.vue';
import Loading from '@shared/Components/Loading.vue';
import {
    Globe,
    Handshake,
    Inbox,
    MessageSquare,
    UserPlus,
    Users,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import type { AdminStatisticsDto } from '../../types/Api.gen';

const { t } = useI18n();
const stats = ref<AdminStatisticsDto | null>(null);
const failed = ref(false);

onMounted(() => {
    api.admin
        .getAdminStatistics()
        .then((response) => (stats.value = response.data))
        .catch(() => (failed.value = true));
});
</script>

<template>
    <PageHeader :title="t('admin.dashboard')" />

    <div v-if="failed" role="alert" class="alert alert-error">
        {{ t('admin.stats.error') }}
    </div>
    <Loading v-else-if="!stats" />
    <div v-else class="flex flex-col gap-4">
        <div
            class="stats stats-vertical md:stats-horizontal bg-base-100 w-full shadow"
        >
            <StatCard
                :title="t('admin.stats.users')"
                :value="stats.users"
                :description="`${stats.activeUsersMonth} ${t('admin.stats.active_users_month')}`"
                :icon="Users"
            />
            <StatCard
                :title="t('admin.stats.posts')"
                :value="stats.posts"
                :description="`${stats.postsMonth} ${t('admin.stats.posts_month')}`"
                :icon="MessageSquare"
            />
            <StatCard
                :title="t('admin.stats.invites')"
                :value="stats.invites"
                :icon="Handshake"
            />
        </div>
        <div
            class="stats stats-vertical md:stats-horizontal bg-base-100 w-full shadow"
        >
            <StatCard
                :title="t('admin.stats.federated_servers')"
                :value="stats.federatedServers"
                :description="`${stats.federatedServersMonth} ${t('admin.stats.federated_servers_month')}`"
                :icon="Globe"
            />
            <StatCard
                :title="t('admin.stats.remote_followers')"
                :value="stats.remoteFollowers"
                :icon="UserPlus"
            />
            <StatCard
                :title="t('admin.stats.remote_posts')"
                :value="stats.remotePosts"
                :description="`${stats.remotePostsMonth} ${t('admin.stats.remote_posts_month')}`"
                :icon="Inbox"
            />
        </div>
    </div>
</template>
