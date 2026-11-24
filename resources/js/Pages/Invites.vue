<script setup lang="ts">
import LoadingButton from '@shared/Components/LoadingButton.vue';
import { api } from '@/api';
import { useTitle } from '@shared/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getServerOrigin } from '@/Services/ServerConfig';
import { DateTime } from 'luxon';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { InviteDto } from '../../types/Api.gen';

const { t } = useI18n();

useTitle(t('invites.title'));

const invites = ref<InviteDto[]>([]);
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
    await api.invites.createInvite({});
    await fetchInvites();
}

async function deleteInvite(inviteCode: string): Promise<void> {
    await api.invites.deleteInvite(inviteCode);
    await fetchInvites();
}

function humanTimestamp(timestamp: string | null): string {
    if (!timestamp) {
        return '-';
    }
    return DateTime.fromISO(timestamp).toLocaleString(DateTime.DATETIME_MED);
}

async function copy(id: string): Promise<void> {
    const registerRoute = `${await getServerOrigin()}/register`;
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
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('invites.title') }}
            </h2>
        </template>

        <div class="card bg-base-100 flex min-w-full shadow-md">
            <div class="flex min-w-full justify-center py-5">
                <LoadingButton
                    class="btn btn-primary"
                    :disabled="loading"
                    :action="createInvite"
                >
                    {{ t('invites.create_invite') }}
                </LoadingButton>
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
                                <LoadingButton
                                    class="btn btn-error"
                                    :disabled="loading"
                                    :action="() => deleteInvite(invite.id)"
                                >
                                    {{ t('verbs.delete') }}
                                </LoadingButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
