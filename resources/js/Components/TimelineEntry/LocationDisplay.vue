<script setup lang="ts">
import LocationListEntryInfo from '@/Pages/NewPostDialog/Partials/LocationListEntryInfo.vue';
import { DateTime } from 'luxon';
import type { PropType } from 'vue';
import { getEmojiFromTags } from '../../Services/LocationTypeService';
import { LocationPost } from '@/types/PostTypes';

const props = defineProps({
    post: {
        type: Object as PropType<LocationPost>,
        required: true,
    },
});

function relativeCreatedAt(): string {
    const date = DateTime.fromISO(props.post?.created_at);

    if (date.diffNow('days').days < -1) {
        return date.toLocaleString();
    }
    return date.toRelative() || '';
}
</script>

<template>
    <div>
        <div>
            <div class="text-xs opacity-60">
                {{ post.user.name }}
                ·
                {{ relativeCreatedAt() }}
            </div>
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
