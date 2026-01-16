<script setup lang="ts">
import { api } from '@/app';
import LocationsMap from '@/Components/Maps/LocationsMap.vue';
import ProfileWrapper from '@/Pages/Profile/ProfileWrapper.vue';
import type { UserDto } from '@/types';
import { Head } from '@inertiajs/vue3';
import { PropType, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    username: {
        type: String as PropType<string>,
        required: true,
    },
});
const user = ref<UserDto | null>(null);
const loading = ref(true);

const loadProfileData = async () => {
    loading.value = true;
    try {
        api.profile.getProfile(props.username).then((response) => {
            user.value = response.data;
            loading.value = false;
        });
    } catch (error) {
        console.error('Error loading profile data:', error);
    } finally {
        loading.value = false;
    }
};

loadProfileData();
</script>

<template>
    <Head v-if="user" :title="t('profile.profile_of', { name: user.name })" />

    <ProfileWrapper :user="user">
        <div class="card bg-base-100 min-w-full shadow-md">
            <LocationsMap v-if="user" :user />
        </div>
    </ProfileWrapper>
</template>
