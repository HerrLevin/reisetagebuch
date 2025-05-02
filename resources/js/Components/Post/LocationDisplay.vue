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
                    <template v-slot:activator="{ onClick }">
                        <a href="#" @click.prevent="onClick">
                            {{ getEmojiFromTags(post.location.tags) }}
                            {{ post.location.name }}
                        </a>
                    </template>
                </LocationListEntryInfo>
            </div>
        </div>

        <div class="mt-1">
            <div class="flex items-center gap-2 text-xs">
                <!--                <span class="opacity-50">{{ region }}</span>-->
                <!--                <StarsIndicator :stars />-->
                <!--                <LikesIndicator :likes />-->
            </div>
        </div>
    </div>
</template>

<style scoped></style>
