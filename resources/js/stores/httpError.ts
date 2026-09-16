import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useHttpErrorStore = defineStore('httpError', () => {
    const status = ref<403 | 404 | null>(null);

    function setError(newStatus: 403 | 404) {
        status.value = newStatus;
    }

    function clearError() {
        status.value = null;
    }

    return {
        status,
        setError,
        clearError,
    };
});
