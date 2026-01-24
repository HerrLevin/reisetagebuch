import { api } from '@/api';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { AuthenticatedUserDto } from '../../types/Api.gen';

export const useUserStore = defineStore(
    'user',
    () => {
        const user = ref<AuthenticatedUserDto | null>(null);
        const refreshedAt = ref<Date | null>(null);

        const fetchUser = async (force = false) => {
            // only refresh every 5 minutes
            if (!force && user.value && refreshedAt.value) {
                const now = new Date();
                const diff =
                    (now.getTime() - refreshedAt.value.getTime()) / 1000;
                if (diff < 300 && user.value) {
                    return user.value;
                }
            }
            try {
                const response = await api.auth.getAuthenticatedUser();
                user.value = response.data;
                return user.value;
            } catch (error) {
                console.error('Error fetching user:', error);
            }
        };

        const invalidateUser = () => {
            refreshedAt.value = null;
            user.value = null;
        };

        return {
            user,
            fetchUser,
            invalidateUser,
        };
    },
    {
        persist: true,
    },
);
