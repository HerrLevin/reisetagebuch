<script setup lang="ts">
</script>

<template>
    <div>
        <div class="grid grid-cols-3 gap-0 pb-0">
            <div class="text-left">
                <div class="mb-2 leading-none font-semibold">
                    Karlsruhe Hbf
                </div>
                <div class="opacity-50 text-xs">Karlsruhe</div>
                <p class="text-muted-foreground text-sm font-medium">10:05</p>
            </div>
            <div class="self-end text-center">
                <p class="text-muted-foreground text-sm font-medium">ICE 123</p>
            </div>
            <div class="text-right">
                <div class="mb-2 leading-none font-semibold">
                    Heidelberg Hbf
                </div>
                <div class="opacity-50 text-xs">Heidelberg</div>
                <p class="text-muted-foreground text-sm font-medium">10:35</p>
            </div>
        </div>
        <div class="flex w-full flex-col">
            <div class="divider divider-dashed mt-0">🚂</div>
        </div>
    </div>
</template>

<style scoped>
.divider-dashed {
    &::before, &::after {
        background:
            repeating-linear-gradient(
                90deg,
                transparent,
                transparent 5px,
                color-mix(in oklab, var(--color-base-content) 10%, transparent) 5px,
                color-mix(in oklab, var(--color-base-content) 10%, transparent) 10px
            );
    }
}
</style>
