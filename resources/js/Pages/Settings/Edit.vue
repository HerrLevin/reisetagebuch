<script setup lang="ts">
import { api } from '@/api';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateAccountSettingsForm from '@/Pages/Settings/Partials/UpdateAccountSettingsForm.vue';
import UpdateDeviceSettingsForm from '@/Pages/Settings/Partials/UpdateDeviceSettingsForm.vue';
import { useUserStore } from '@/stores/user';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdateAccountInformationForm from './Partials/UpdateAccountInformationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';

const { t } = useI18n();
useTitle('Settings');

const route = useRoute();
const router = useRouter();
const user = useUserStore();
user.fetchUser(true);

const processingTraewelling = ref(false);

function connectTraewelling() {
    api.socialite.connectTraewelling().then((response) => {
        const url = response.data.url;
        console.log('Redirecting to Traewelling for authentication:', url);
        window.location.href = url;
    });
}

function disconnectTraewelling() {
    processingTraewelling.value = true;
    api.account
        .disconnectTraewelling()
        .then(() => {
            window.location.reload();
        })
        .catch((error) => {
            alert(
                error.response.data.message ||
                    t('settings.traewelling_disconnect.error'),
            );
        })
        .finally(() => {
            processingTraewelling.value = false;
        });
}

function traewellingCallback() {
    const code = route.query.code as string;
    if (!code) {
        alert(t('settings.traewelling_callback.missing_code'));
        return;
    }

    processingTraewelling.value = true;

    api.socialite
        .handleTraewellingCallback({ code })
        .then(() => {
            user.fetchUser(true);
            router.push({
                name: 'account.edit',
                query: {
                    traewelling_connected: 'true',
                },
            });
            alert(t('settings.traewelling_callback.success'));
        })
        .catch((error) => {
            alert(
                error.response.data.message ||
                    t('settings.traewelling_callback.error'),
            );
        })
        .finally(() => {
            processingTraewelling.value = false;
        });
}

if (route.name === 'socialite.traewelling.callback') {
    traewellingCallback();
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('settings.title') }}
            </h2>
        </template>

        <div v-if="user.user" class="min-w-full space-y-6">
            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <UpdateDeviceSettingsForm :user="user.user" class="max-w-xl" />
            </div>
            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <UpdateAccountSettingsForm :user="user.user" class="max-w-xl" />
            </div>
            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <UpdateAccountInformationForm
                    :user="user.user"
                    class="max-w-xl"
                />
            </div>

            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <div
                    v-if="user.user.traewellingConnected"
                    class="flex items-center gap-2"
                >
                    <span class="text-success">
                        {{ t('settings.connected_to_traewelling') }}
                    </span>
                    <a
                        class="btn btn-error btn-sm ml-4"
                        type="button"
                        @click="disconnectTraewelling()"
                    >
                        {{ t('settings.disconnect_traewelling') }}
                    </a>
                </div>
                <button
                    v-else
                    class="btn btn-primary"
                    type="button"
                    @click="connectTraewelling()"
                >
                    {{ t('settings.connect_traewelling') }}
                </button>
            </div>
            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
