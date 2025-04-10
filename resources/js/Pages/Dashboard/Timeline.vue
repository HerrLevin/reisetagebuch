<script setup lang="ts">
import TimelineEntry from '@/Components/TimelineEntry/TimelineEntry.vue';

const demoData = [
    {
        id: 1,
        username: 'Julia',
        stars: 10,
        likes: 3,
        region: 'Heidelberg, Deutschland',
        location: "Yorma's",
        createdAt: '2025-04-08T19:00:00Z',
        body: 'Bester Kaffee!',
        picture: 'https://loremfaces.net/96/id/1.jpg',
    },
    {
        id: 2,
        username: 'Julia',
        stars: 5,
        likes: 1,
        region: 'Karlsruhe, Deutschland',
        location: '',
        createdAt: '2025-04-08T18:00:00Z',
        body: '',
        picture: 'https://loremfaces.net/96/id/1.jpg',
        showRoute: true,
    },
    {
        id: 3,
        username: 'Marc',
        stars: 5,
        likes: 1,
        region: 'Stockholm, Schweden',
        location: 'Bageri',
        createdAt: '2025-04-08T18:00:00Z',
        body: 'Das Brot ist sehr lecker!',
        picture: 'https://loremfaces.net/96/id/2.jpg',
    },
    {
        id: 4,
        username: 'Julia',
        stars: 3,
        likes: 0,
        region: 'Karlsruhe, Deutschland',
        location: 'Karlsruhe Hbf',
        createdAt: '2025-04-08T17:00:00Z',
        body: '',
        picture: 'https://loremfaces.net/96/id/1.jpg',
    },
    {
        id: 5,
        username: 'Felix',
        stars: 8,
        likes: 2,
        region: 'Berlin, Deutschland',
        location: 'Berghain',
        createdAt: '2025-04-08T16:00:00Z',
        picture: 'https://loremfaces.net/96/id/3.jpg',
    },
];
</script>

<template>
    <ul class="list">
        <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
            Your Dashboard
        </li>
        <TimelineEntry
            v-for="demo in demoData"
            :key="demo.id"
            :username="demo.username"
            :stars="demo.stars"
            :likes="demo.likes"
            :region="demo.region"
            :location="demo.location"
            :createdAt="demo.createdAt"
            :body="demo.body"
            :picture="demo.picture"
            :showRoute="demo.showRoute"
        />
    </ul>
</template>
