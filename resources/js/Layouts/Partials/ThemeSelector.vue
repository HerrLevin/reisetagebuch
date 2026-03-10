<script setup lang="ts">
import { Moon, Sun } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const checkBox = ref(false);
// get theme from local storage
const storedTheme = localStorage.getItem('theme');

if (storedTheme) {
    checkBox.value = storedTheme === 'light';
} else {
    // get theme from system preference
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)')
        .matches
        ? 'dark'
        : 'light';
    checkBox.value = systemTheme === 'light';
}

// watch checkbox state
watch(checkBox, (newValue) => {
    if (!newValue) {
        localStorage.setItem('theme', 'dark');
    } else {
        localStorage.setItem('theme', 'light');
    }
});
</script>
<template>
    <label class="flex w-full cursor-pointer items-center gap-2">
        <span class="label-text">
            <Moon class="size-5" />
        </span>
        <input
            v-model="checkBox"
            type="checkbox"
            value="light"
            class="toggle theme-controller"
        />
        <span class="label-text">
            <Sun class="size-5" />
        </span>
    </label>
</template>
