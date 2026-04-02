<script setup lang="ts">
import type { ActivityPubRemoteInteractions } from '@/types/activitypub';
import { Heart, MessageCircle, Repeat2 } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { api } from '@/api';

const { t } = useI18n();

const props = defineProps({
    postId: {
        type: String,
        required: true,
    },
});

const interactions = ref<ActivityPubRemoteInteractions | null>(null);
const loading = ref(false);

async function fetchInteractions() {
    loading.value = true;
    try {
        const response = await api.instance.get(
            `/api/posts/${props.postId}/remote-interactions`,
        );
        interactions.value = response.data;
    } catch {
        interactions.value = null;
    } finally {
        loading.value = false;
    }
}

onMounted(fetchInteractions);
</script>

<template>
    <div
        v-if="interactions && (interactions.likes > 0 || interactions.boosts > 0 || interactions.replies.length > 0)"
        class="card bg-base-100 mt-4 shadow-md"
    >
        <div class="card-body">
            <h3 class="card-title text-base">
                {{ t('activitypub.remote_interactions') }}
            </h3>

            <div class="flex gap-4 text-sm">
                <div v-if="interactions.likes > 0" class="flex items-center gap-1">
                    <Heart class="h-4 w-4 fill-red-500 text-red-500" />
                    <span>{{ interactions.likes }} {{ t('activitypub.likes', interactions.likes) }}</span>
                </div>
                <div v-if="interactions.boosts > 0" class="flex items-center gap-1">
                    <Repeat2 class="h-4 w-4 text-green-500" />
                    <span>{{ interactions.boosts }} {{ t('activitypub.boosts', interactions.boosts) }}</span>
                </div>
            </div>

            <div v-if="interactions.replies.length > 0" class="mt-2">
                <h4 class="mb-2 text-sm font-medium">
                    <MessageCircle class="mr-1 inline h-4 w-4" />
                    {{ t('activitypub.replies', interactions.replies.length) }}
                </h4>
                <ul class="space-y-3">
                    <li
                        v-for="reply in interactions.replies"
                        :key="reply.id"
                        class="rounded-lg bg-base-200 p-3"
                    >
                        <div class="flex items-start gap-2">
                            <div class="avatar">
                                <div class="w-8 rounded">
                                    <img
                                        v-if="reply.actorAvatar"
                                        :src="reply.actorAvatar"
                                        :alt="reply.actorUsername"
                                    />
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-medium">
                                    {{ reply.actorDisplayName || reply.actorUsername }}
                                    <span class="font-normal opacity-60">
                                        @{{ reply.actorUsername }}@{{ reply.actorInstance }}
                                    </span>
                                </div>
                                <div
                                    v-if="reply.content"
                                    class="mt-1 text-sm"
                                    v-html="reply.content"
                                ></div>
                                <div class="mt-1 flex items-center gap-2 text-xs opacity-40">
                                    <span>{{ new Date(reply.createdAt).toLocaleString() }}</span>
                                    <a
                                        v-if="reply.remoteUrl"
                                        :href="reply.remoteUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="link"
                                    >
                                        &#x2197;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
