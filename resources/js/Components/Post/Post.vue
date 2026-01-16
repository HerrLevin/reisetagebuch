<script setup lang="ts">
import HashTags from '@/Components/Post/HashTags.vue';
import Interactions from '@/Components/Post/Interactions.vue';
import LocationDisplay from '@/Components/Post/LocationDisplay.vue';
import RouteDisplay from '@/Components/Post/RouteDisplay.vue';
import {
    getTravelReasonIcon,
    getTravelReasonLabel,
} from '@/Services/ApiTravelReasonMapping';
import { getVisibilityIcon } from '@/Services/VisibilityMapping';
import { isApiLocationPost, isApiTransportPost } from '@/types/PostTypes';
import { Link } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { BasePost, LocationPost, TransportPost } from '../../../types/Api.gen';

const { t } = useI18n();
const emit = defineEmits(['delete:post']);

const props = defineProps({
    post: {
        type: Object as PropType<BasePost | TransportPost | LocationPost>,
        required: true,
    },
});

let relativeCreatedAt;
const date = DateTime.fromISO(props.post?.createdAt);

if (date.diffNow('days').days < -1) {
    relativeCreatedAt = date.toLocaleString();
} else {
    relativeCreatedAt = date.toRelative() || '';
}
</script>

<template v-show="!deleted">
    <div class="avatar">
        <div class="bg-primary size-10 rounded-xl">
            <img
                v-if="post.user.avatar"
                :src="post.user.avatar"
                :alt="t('posts.profile_picture_alt', { name: post.user.name })"
            />
        </div>
    </div>
    <div class="list-col-grow">
        <div class="mb-1 text-xs">
            <Link
                :href="route('profile.show', post.user.username)"
                class="opacity-60"
            >
                {{ post.user.name }}
            </Link>
            <div
                v-if="
                    (isApiLocationPost(post) || isApiTransportPost(post)) &&
                    post.travelReason
                "
                class="inline text-xs"
            >
                ·
                <div class="dropdown dropdown-hover">
                    <component
                        :is="getTravelReasonIcon(post.travelReason)"
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
                                {{ getTravelReasonLabel(post.travelReason) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            ·
            <span class="text-xs opacity-60">
                <component
                    :is="getVisibilityIcon(post.visibility)"
                    class="iconSize inline"
                />
                {{ relativeCreatedAt }}
            </span>
            <span v-if="isApiTransportPost(post)" class="text-xs opacity-60">
                · {{ DateTime.fromISO(post.publishedAt).toLocaleString() }}
            </span>
        </div>
        <p
            v-if="post.body"
            class="list-col-wrap my-2 ps-3 text-xs whitespace-pre-wrap"
        >
            {{ post.body }}
        </p>
        <LocationDisplay
            v-if="isApiLocationPost(post)"
            :post="post as LocationPost"
        />
        <RouteDisplay
            v-else-if="isApiTransportPost(post)"
            :post="post as TransportPost"
        />
        <HashTags :hash-tags="post.hashTags" />
        <Interactions :post @delete:post="emit('delete:post')" />
    </div>
</template>

<style scoped>
.iconSize {
    width: 1em;
    height: 1em;
}
</style>
