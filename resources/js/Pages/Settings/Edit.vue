<script setup lang="ts">
import { api } from '@/api';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CreatePrivacyPolicyForm from '@/Pages/Settings/Partials/CreatePrivacyPolicyForm.vue';
import UpdateAccountSettingsForm from '@/Pages/Settings/Partials/UpdateAccountSettingsForm.vue';
import UpdateDeviceSettingsForm from '@/Pages/Settings/Partials/UpdateDeviceSettingsForm.vue';
import UpdateImprintForm from '@/Pages/Settings/Partials/UpdateImprintForm.vue';
import { getServerUrl, isNative } from '@/Services/ServerConfig';
import { useAuthStore } from '@/stores/auth';
import { useUserStore } from '@/stores/user';
import { onMounted, ref } from 'vue';
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
const authStore = useAuthStore();
user.fetchUser(true);

const processingTraewelling = ref(false);
const serverUrl = ref('');

onMounted(async () => {
    if (isNative()) {
        serverUrl.value = await getServerUrl();
    }
});

function changeServer() {
    authStore.logout().finally(() => {
        router.push({ name: 'connect-server' });
    });
}

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
            user.fetchUser(true);
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
            <div
                v-if="isNative()"
                class="card bg-base-100 min-w-full p-8 shadow-md"
            >
                <div class="max-w-xl space-y-2">
                    <h3 class="text-lg font-medium">
                        {{ t('connect_server.change_title') }}
                    </h3>
                    <p class="text-base-content/70 text-sm">
                        {{
                            t('connect_server.change_current', {
                                url: serverUrl,
                            })
                        }}
                    </p>
                    <button
                        class="btn btn-outline btn-sm"
                        type="button"
                        @click="changeServer()"
                    >
                        {{ t('connect_server.change_button') }}
                    </button>
                </div>
            </div>
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

            <div
                v-if="user.user.isAdmin"
                class="card bg-base-100 min-w-full p-8 shadow-md"
            >
                <UpdateImprintForm class="max-w-xl" />
            </div>

            <div
                v-if="user.user.isAdmin"
                class="card bg-base-100 min-w-full p-8 shadow-md"
            >
                <CreatePrivacyPolicyForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
