<script setup lang="ts"></script>

<template>
    <div class="flex items-start justify-between">
        <div class="avatar relative -mt-10 ml-0 sm:-mt-16 sm:ml-8">
            <div
                class="border-base-200 skeleton w-20 rounded-xl border-5 sm:w-32"
            ></div>
        </div>
        <div class="flex items-center gap-2">
            <div class="skeleton h-10 w-25 rounded-4xl"></div>
            <div class="skeleton h-10 w-10 rounded-4xl"></div>
        </div>
    </div>
    <div>
        <div class="flex items-center">
            <div class="skeleton h-2 w-30"></div>
        </div>
        <div class="flex items-center space-x-3">
            <div class="skeleton h-1 w-30"></div>
        </div>
    </div>
    <div class="flex flex-wrap gap-x-3 gap-y-2">
        <div class="skeleton h-2 w-30"></div>
        <div class="skeleton h-2 w-30"></div>
        <div class="skeleton h-2 w-30"></div>
        <div class="skeleton h-2 w-30"></div>
        <div class="skeleton h-2 w-30"></div>
    </div>
</template>
