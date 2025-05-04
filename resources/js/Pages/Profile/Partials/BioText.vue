<script setup lang="ts">
import type { UserDto } from '@/types';
import { PropType } from 'vue';

defineProps({
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});
</script>

<template>
    <div>
        <p class="line-clamp-15 overflow-ellipsis whitespace-pre-wrap">
            {{ user.name }}
        </p>
    </div>
</template>
