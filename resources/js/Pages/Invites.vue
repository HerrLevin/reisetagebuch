<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { Invite } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
    invites: {
        type: Array as PropType<Array<Invite>>,
        default: () => [],
    },
});

const createForm = useForm({
    expiresAt: null,
});

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
            // show success message
            alert(t('invites.copied_to_clipboard'));
        })
        .catch(() => {
            // show error message
            alert(t('invites.copy_failed'));
        });
}
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
                <form @submit.prevent="createForm.post('/invites')">
                    <button type="submit" class="btn btn-primary">
                        {{ t('invites.create_invite') }}
                    </button>
                </form>
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
                                <Link
                                    :href="route('invites.destroy', invite.id)"
                                    method="delete"
                                    as="button"
                                    class="btn btn-error"
                                >
                                    {{ t('verbs.delete') }}
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
