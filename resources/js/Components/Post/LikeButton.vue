<script setup lang="ts">
import { Heart } from 'lucide-vue-next';
import { ref } from 'vue';

const liked = ref(false);
</script>

<template>
    <button class="btn btn-square btn-ghost" @click="liked = !liked">
        <Heart v-if="liked" class="size-6" />
        <Heart v-else class="size-6" fill="currentColor" />
    </button>
</template>
