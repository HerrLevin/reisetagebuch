import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLocationHistoryStore = defineStore(
    'locationHistory',
    () => {
        const allowHistory = ref<boolean | null>(null);

        function setAllowHistory(value: boolean) {
            allowHistory.value = value;
        }

        return {
            allowHistory,
            setAllowHistory,
        };
    },
    {
        persist: true,
    },
);
