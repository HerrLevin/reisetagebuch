import { api } from '@/api';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { AppConfigurationDto, Feature } from '../../types/Api.gen';

export const useAppConfigurationStore = defineStore('appConfiguration', () => {
    const configuration = ref<AppConfigurationDto | null>(null);

    const fetchConfig = async () => {
        api.app
            .getAppConfiguration()
            .then((response) => {
                configuration.value = response.data;
            })
            .catch((error) => {
                console.error('Error fetching notifications:', error);
            });
    };

    const checkFeature = (feature: Feature) => {
        return (
            configuration.value?.featureFlags.find(
                (setting) => setting.name === feature,
            )?.enabled || false
        );
    };

    const canRegister = () => {
        return checkFeature(Feature.Registration);
    };

    const canInvite = () => {
        return checkFeature(Feature.Invite);
    };

    return {
        configuration,
        fetchConfig,
        canRegister,
        canInvite,
    };
});
