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
        <p
            v-if="user.bio"
            class="line-clamp-15 overflow-ellipsis whitespace-pre-wrap"
        >
            {{ user.bio }}
        </p>
    </div>
</template>
