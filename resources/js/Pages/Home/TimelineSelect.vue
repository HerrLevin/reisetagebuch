<script setup lang="ts">
import { ChevronsUpDown } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { RouterLink } from 'vue-router';
const { t } = useI18n();
</script>

<template>
    <div class="dropdown">
        <div tabindex="0" role="button" class="btn btn-sm">
            <slot></slot>
            <ChevronsUpDown class="ml-1 h-4 w-4" />
        </div>
        <ul
            tabindex="-1"
            class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
        >
            <li>
                <RouterLink
                    :to="{ name: 'home.global' }"
                    active-class="menu-active"
                >
                    {{ t('pages.timeline.global_timeline') }}
                </RouterLink>
            </li>
            <li>
                <RouterLink :to="{ name: 'home' }" active-class="menu-active">
                    {{ t('pages.timeline.your_timeline') }}
                </RouterLink>
            </li>
        </ul>
    </div>
</template>

<style scoped></style>
