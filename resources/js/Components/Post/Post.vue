<script setup lang="ts">
import HashTags from '@/Components/Post/HashTags.vue';
import Interactions from '@/Components/Post/Interactions.vue';
import LocationDisplay from '@/Components/Post/LocationDisplay.vue';
import RouteDisplay from '@/Components/Post/RouteDisplay.vue';
import {
    getTravelReasonIcon,
    getTravelReasonLabel,
} from '@/Services/TravelReasonMapping';
import { getVisibilityIcon } from '@/Services/VisibilityMapping';
import { isLocationPost, isTransportPost } from '@/types/PostTypes';
import { DateTime } from 'luxon';
import { computed, PropType, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink } from 'vue-router';
import { BasePost, LocationPost, TransportPost } from '../../../types/Api.gen';

const { t } = useI18n();
const emit = defineEmits(['delete:post', 'update:post']);

const props = defineProps({
    post: {
        type: Object as PropType<BasePost | TransportPost | LocationPost>,
        required: true,
    },
});

// Local reactive post state
const localPost = ref(props.post);

// Watch for prop changes (in case parent updates post prop)
watch(
    () => props.post,
    (newPost) => {
        localPost.value = newPost;
    },
);

// Listen for update:post event and update localPost
function onUpdatePost(newPost: BasePost | TransportPost | LocationPost) {
    localPost.value = newPost;
    emit('update:post', newPost);
}

const relativeCreatedAt = computed(() => {
    if (!localPost.value?.createdAt) {
        return '';
    }
    const date = DateTime.fromISO(localPost.value!.createdAt);

    if (date.diffNow('days').days < -1) {
        return date.toLocaleString();
    } else {
        return date.toRelative() || '';
    }
});
</script>

<template v-show="!deleted" v-if="localPost">
    <div class="avatar">
        <div class="bg-primary size-10 rounded-xl">
            <img
                v-if="localPost.user.avatar"
                :src="localPost.user.avatar"
                :alt="
                    t('posts.profile_picture_alt', {
                        name: localPost.user.name,
                    })
                "
            />
        </div>
    </div>
    <div>
        <div class="mb-1 text-xs">
            <RouterLink
                :to="`/profile/${localPost.user.username}`"
                class="opacity-60"
            >
                {{ localPost.user.name }}
            </RouterLink>
            <div
                v-if="
                    (isLocationPost(localPost) || isTransportPost(localPost)) &&
                    localPost.travelReason
                "
                class="inline text-xs"
            >
                ·
                <div class="dropdown dropdown-hover">
                    <component
                        :is="getTravelReasonIcon(localPost.travelReason)"
                        class="iconSize inline opacity-60"
                        role="button"
                        tabindex="0"
                    />
                    <div
                        tabindex="-1"
                        class="card card-sm dropdown-content bg-base-100 rounded-box z-1 w-64 shadow-sm"
                    >
                        <div tabindex="-1" class="card-body">
                            <h3 class="card-title">
                                {{
                                    getTravelReasonLabel(localPost.travelReason)
                                }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            ·
            <span class="text-xs opacity-60">
                <component
                    :is="getVisibilityIcon(localPost.visibility)"
                    class="iconSize inline"
                />
                {{ relativeCreatedAt }}
            </span>
            <span v-if="isTransportPost(localPost)" class="text-xs opacity-60">
                · {{ DateTime.fromISO(localPost.publishedAt).toLocaleString() }}
            </span>
            <span
                v-if="isLocationPost(localPost) && localPost.visitedAt"
                class="text-xs opacity-60"
            >
                · @
                {{
                    DateTime.fromISO(localPost.visitedAt).toLocaleString(
                        DateTime.DATETIME_MED,
                    )
                }}
            </span>
        </div>
        <p
            v-if="localPost.body"
            class="list-col-wrap my-2 ps-3 text-xs whitespace-pre-wrap"
        >
            {{ localPost.body }}
        </p>
        <LocationDisplay
            v-if="isLocationPost(localPost)"
            :post="localPost as LocationPost"
        />
        <RouteDisplay
            v-else-if="isTransportPost(localPost)"
            :post="localPost as TransportPost"
        />
        <HashTags :hash-tags="localPost.hashTags" />
        <Interactions
            :post="localPost"
            @delete:post="emit('delete:post')"
            @update:post="onUpdatePost"
        />
    </div>
</template>

<style scoped>
.iconSize {
    width: 1em;
    height: 1em;
}
</style>
