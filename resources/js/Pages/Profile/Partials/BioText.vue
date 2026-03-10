<script setup lang="ts">
import { PropType } from 'vue';
import { UserDto } from '../../../../types/Api.gen';

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
