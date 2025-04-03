<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div class="min-w-full space-y-6">
            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
