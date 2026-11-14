<script setup lang="ts">
import { ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';

type Theme = 'system' | 'light' | 'dark';

const { t } = useI18n();
const themes: Theme[] = ['system', 'light', 'dark'];

function stored(): Theme {
    try {
        const value = localStorage.getItem('admin-theme');
        return themes.includes(value as Theme) ? (value as Theme) : 'system';
    } catch {
        return 'system';
    }
}

const theme = ref<Theme>(stored());

// "system" drops data-theme so daisyUI's --prefersdark follows the OS.
watchEffect(() => {
    if (theme.value === 'system') {
        document.documentElement.removeAttribute('data-theme');
    } else {
        document.documentElement.setAttribute('data-theme', theme.value);
    }
    try {
        localStorage.setItem('admin-theme', theme.value);
    } catch {
        // storage unavailable (private mode); theme just isn't remembered
    }
});
</script>

<template>
    <label class="select select-sm w-full">
        <span class="label">{{ t('admin.theme.label') }}</span>
        <select v-model="theme">
            <option v-for="option in themes" :key="option" :value="option">
                {{ t(`admin.theme.${option}`) }}
            </option>
        </select>
    </label>
</template>
