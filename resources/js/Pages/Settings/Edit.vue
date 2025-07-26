<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateAccountSettingsForm from '@/Pages/Settings/Partials/UpdateAccountSettingsForm.vue';
import { Head, Link } from '@inertiajs/vue3';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdateAccountInformationForm from './Partials/UpdateAccountInformationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';

const props = defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
    traewellingConnected?: boolean;
}>();

function connectTraewelling() {
    window.location.href = route('traewelling.connect');
}
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <div class="min-w-full space-y-6">
            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <UpdateAccountSettingsForm :status="status" class="max-w-xl" />
            </div>
            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <UpdateAccountInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <div
                    v-if="props.traewellingConnected"
                    class="flex items-center gap-2"
                >
                    <span class="text-success"> Connected to Traewelling </span>
                    <Link
                        :href="route('traewelling.disconnect')"
                        class="btn btn-error btn-sm ml-4"
                        type="button"
                    >
                        Disconnect
                    </Link>
                </div>
                <button
                    v-else
                    class="btn btn-primary"
                    type="button"
                    @click="connectTraewelling()"
                >
                    Connect Traewelling Account
                </button>
            </div>
            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
