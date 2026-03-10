<script setup lang="ts">
import { ArrowLeft } from 'lucide-vue-next';
import { useRouter } from 'vue-router';
const vueRouter = useRouter();

function handleBack() {
    vueRouter.back();
}
</script>

<template>
    <div class="border-base-300 flex items-center gap-2 border-b-1 p-4">
        <button
            class="btn btn-ghost btn-sm btn-circle text-base-content normal-case"
            type="button"
            @click="handleBack"
        >
            <ArrowLeft class="size-6" />
        </button>
    </div>
</template>
