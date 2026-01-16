<script setup lang="ts">
import LocationListEntryInfo from '@/Pages/NewPostDialog/Partials/LocationListEntryInfo.vue';
import { getEmojiFromTags } from '@/Services/LocationTypeService';
import type { PropType } from 'vue';
import { LocationPost } from '../../../types/Api.gen';

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
