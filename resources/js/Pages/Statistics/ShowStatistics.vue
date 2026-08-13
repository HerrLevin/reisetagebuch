<script setup lang="ts">
import { api } from '@/api';
import CountriesMap from '@/Components/Maps/CountriesMap.vue';
import { useTitle } from '@/composables/useTitle';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { UserDto } from '../../../types/Api.gen';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useUserStore } from '@/stores/user';

const { t } = useI18n();

const authenticatedUser = useUserStore();
const user = ref<UserDto | null>(null);
const loading = ref(true);

useTitle(t('statistics.title'));

const loadProfileData = async () => {
    loading.value = true;
    await authenticatedUser.fetchUser();
    try {
        api.profile
            .getProfile(authenticatedUser.user!.username)
            .then((response) => {
                user.value = response.data;
                loading.value = false;
            });
    } catch (error) {
        console.error('Error loading profile data:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadProfileData();
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('statistics.title') }}
            </h2>
        </template>
        <div class="card bg-base-100 min-w-full shadow-md">
            <CountriesMap />
        </div>
    </AuthenticatedLayout>
</template>
