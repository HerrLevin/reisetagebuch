<script setup lang="ts">
import Calendar from '@/Icons/Calendar.vue';
import EllipsisVertical from '@/Icons/EllipsisVertical.vue';
import Link from '@/Icons/Link.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const textTest = `Hi!

Wo?

Lorem ipsum dolor sit amet, consectetur adipiscing elit.
`;
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">Profile</h2>
        </template>

        <div class="-mt-4 mb-4 sm:mt-0">
            <div
                class="h-52 sm:h-64 sm:rounded-xl"
                style="
                    background-color: var(--color-secondary);
                    background-image: url('/assets/pexels-nubikini-386025.jpg');
                    background-position: center center;
                    background-repeat: no-repeat;
                    background-size: cover;
                "
            ></div>
        </div>

        <div class="mb-4 space-y-1 px-5 md:px-0">
            <div class="flex items-start justify-between">
                <div class="avatar relative -mt-10 ml-0 sm:-mt-16 sm:ml-8">
                    <div
                        class="border-base-200 w-20 rounded-xl border-5 sm:w-32"
                    >
                        <img src="/assets/pexels-brenoanp-442535-1136575.jpg" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn rounded-full">
                        <div class="flex items-center">Follow</div>
                    </button>
                    <button class="btn btn-circle" type="button">
                        <EllipsisVertical />
                    </button>
                </div>
            </div>
            <div class="-mt-3">
                <div class="flex items-center">
                    <h3 class="truncate text-2xl font-bold">Max Mustermann</h3>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm">@mmustermann</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-x-3">
                <a class="link link-hover flex cursor-pointer">
                    <b>51</b>&nbsp;<span class="opacity-65">friends</span>
                </a>
                <a class="link link-hover flex cursor-pointer">
                    <b>3.207</b>&nbsp;<span class="opacity-65">locations</span>
                </a>
                <a class="link link-hover flex cursor-pointer">
                    <b>12</b>&nbsp;<span class="opacity-65">trips</span>
                </a>
            </div>
            <div class="flex flex-wrap gap-x-3">
                <span class="flex">
                    <b>1.234</b>&nbsp;<span class="opacity-65">posts</span>
                </span>
                <span class="flex">
                    <b>129.182</b>&nbsp;<span class="opacity-65">km</span>
                </span>
                <span class="flex">
                    <b>64</b>&nbsp;<span class="opacity-65">d</span>&nbsp;
                    <b>21</b>&nbsp;<span class="opacity-65">h</span>&nbsp;
                    <b>28</b>&nbsp;<span class="opacity-65">m</span>
                </span>
            </div>
            <div>
                <p class="line-clamp-15 overflow-ellipsis whitespace-pre-wrap">
                    {{ textTest }}
                </p>
            </div>
            <div class="flex gap-3">
                <div class="flex flex-wrap gap-x-5 gap-y-2 opacity-65">
                    <div class="flex items-center gap-1">
                        <Calendar class="h-4 w-4" />
                        <span class="truncate text-sm">Joined Mar 2027</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-x-5 gap-y-2 opacity-65">
                    <div class="flex items-center gap-1">
                        <Link class="h-4 w-4" />
                        <a class="link link-hover link-primary truncate text-sm"
                            >traewelling.de/@HerrLevin_</a
                        >
                    </div>
                </div>
            </div>
            <ul class="menu menu-horizontal bg-base-200 rounded-box">
                <li><a>posts</a></li>
                <li><a>trips</a></li>
                <li><a>images</a></li>
            </ul>
        </div>

        <div class="card bg-base-100 min-w-full shadow-md">
            Hier kommen dann die posts hin!
        </div>
    </AuthenticatedLayout>
</template>
