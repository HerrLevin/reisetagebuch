<script setup lang="ts">
defineProps({
    src: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="-mt-4 mb-4 sm:mt-0">
        <div
            class="h-52 sm:h-64 sm:rounded-xl"
            :style="`
                background-color: var(--color-secondary);
                background-image: url('${src}');
                background-position: center center;
                background-repeat: no-repeat;
                background-size: cover;
            `"
        ></div>
    </div>
</template>
