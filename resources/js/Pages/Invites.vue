<script setup lang="ts">
import { api } from '@/api';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { Invite } from '@/types';
import { Head } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const invites = ref<Invite[]>([]);
const loading = ref(false);

async function fetchInvites(): Promise<void> {
    loading.value = true;
    try {
        const response = await api.invites.listInvites();
        invites.value = response.data;
    } finally {
        loading.value = false;
    }
}

async function createInvite(): Promise<void> {
    loading.value = true;
    try {
        api.invites.createInvite({}).then(() => {
            fetchInvites();
        });
    } finally {
        loading.value = false;
    }
}

async function deleteInvite(inviteCode: string): Promise<void> {
    loading.value = true;
    try {
        api.invites.deleteInvite(inviteCode).then(() => {
            fetchInvites();
        });
    } finally {
        loading.value = false;
    }
}

function humanTimestamp(timestamp: string | null): string {
    if (!timestamp) {
        return '-';
    }
    return DateTime.fromISO(timestamp).toLocaleString(DateTime.DATETIME_MED);
}

function copy(id: string): void {
    const registerRoute = route('register');
    navigator.clipboard
        .writeText(registerRoute + '?invite=' + id)
        .then(() => {
            alert(t('invites.copied_to_clipboard'));
        })
        .catch(() => {
            alert(t('invites.copy_failed'));
        });
}

onMounted(() => {
    fetchInvites();
});
</script>
<template>
    <Head :title="t('invites.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('invites.title') }}
            </h2>
        </template>

        <div class="card bg-base-100 flex min-w-full shadow-md">
            <div class="flex min-w-full justify-center py-5">
                <button
                    type="button"
                    class="btn btn-primary"
                    :disabled="loading"
                    @click="createInvite"
                >
                    {{ t('invites.create_invite') }}
                </button>
            </div>
            <div class="divider"></div>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>{{ t('invites.code') }}</th>
                            <th>{{ t('invites.created_at') }}</th>
                            <th>{{ t('invites.used_at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="invite in invites" :key="invite.id">
                            <td>
                                <input
                                    type="text"
                                    class="input input-bordered w-full"
                                    :value="invite.id"
                                    readonly
                                />
                            </td>
                            <td>{{ humanTimestamp(invite.createdAt) }}</td>
                            <td>{{ humanTimestamp(invite.usedAt) }}</td>
                            <td>
                                <button class="btn" @click="copy(invite.id)">
                                    {{ t('invites.copy') }}
                                </button>
                            </td>
                            <td>
                                <button
                                    class="btn btn-error"
                                    :disabled="loading"
                                    @click="deleteInvite(invite.id)"
                                >
                                    {{ t('verbs.delete') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
