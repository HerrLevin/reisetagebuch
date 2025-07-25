<script setup lang="ts">
import LocationListEntryInfo from '@/Pages/NewPostDialog/Partials/LocationListEntryInfo.vue';
import { getEmojiFromTags } from '@/Services/LocationTypeService';
import { LocationPost } from '@/types/PostTypes';
import type { PropType } from 'vue';

defineProps({
    post: {
        type: Object as PropType<LocationPost>,
        required: true,
    },
});
</script>

<template>
    <div>
        <div>
            <div class="font-semibold">
                <LocationListEntryInfo
                    v-if="post.location"
                    :location="post.location"
                >
                    <template #activator="{ onClick }">
                        <a href="#" @click.prevent="onClick">
                            {{ getEmojiFromTags(post.location.tags) }}
                            {{ post.location.name }}
                        </a>
                    </template>
                </LocationListEntryInfo>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
