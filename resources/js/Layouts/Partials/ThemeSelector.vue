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
    <label class="swap swap-rotate">
        <!-- this hidden checkbox controls the state -->
        <input
            v-model="checkBox"
            type="checkbox"
            class="theme-controller"
            value="light"
        />
        <Sun class="swap-off size-5" />

        <!-- moon icon -->
        <Moon class="swap-on size-5" />
    </label>
</template>
