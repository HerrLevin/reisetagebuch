<script setup lang="ts">
import { api } from '@/api';
import LocationsMap from '@/Components/Maps/LocationsMap.vue';
import { useTitle } from '@/composables/useTitle';
import ProfileWrapper from '@/Pages/Profile/ProfileWrapper.vue';
import { PropType, ref, watch, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { UserDto } from '../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps({
    username: {
        type: String as PropType<string>,
        required: true,
    },
});
const user = ref<UserDto | null>(null);
const loading = ref(true);

watchEffect(() => {
    if (user.value) {
        useTitle(t('profile.profile_of', { name: user.value.name }));
    }
});

const loadProfileData = async () => {
    loading.value = true;
    user.value = null;
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

watch(() => props.username, loadProfileData, { immediate: true });
</script>

<template>
    <ProfileWrapper :user="user">
        <div class="card bg-base-100 min-w-full shadow-md">
            <LocationsMap v-if="user" :user />
        </div>
    </ProfileWrapper>
</template>
