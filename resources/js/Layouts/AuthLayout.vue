<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div class="bg-base-200 flex min-h-screen items-center">
        <div class="card mx-auto w-full max-w-5xl shadow-xl">
            <div
                class="bg-base-100 grid grid-cols-1 md:grid-cols-2 md:rounded-xl"
            >
                <div class="">
                    <div
                        class="hero bg-base-200 min-h-full md:rounded-l-xl"
                        style="
                            background-image: url('/assets/login/pexels-nurseryart-346885.jpg');
                            background-size: cover;
                            background-position: center;
                        "
                    >
                        <div class="hero-content py-12">
                            <div class="max-w-md">
                                <h1
                                    class="text-center text-3xl font-bold text-white mix-blend-difference"
                                >
                                    <ApplicationLogo
                                        class="inline-block h-12 w-12 fill-current"
                                    />
                                    &nbsp; Reisetagebuch
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-10 py-24">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
