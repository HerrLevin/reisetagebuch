<script setup lang="ts">
import RemoteActorListCard from '@/Pages/Search/Partials/RemoteActorListCard.vue';
import UserListCard from '@/Pages/Posts/Partials/UserListCard.vue';
import { api } from '@/api';
import { ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    RemoteFollowDto,
    RemoteFollowerDto,
    UserDto,
} from '../../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps<{
    userId: string;
}>();

const modal = useTemplateRef('followListModal');
const loading = ref(false);
const mode = ref<'followers' | 'following'>('followers');
const localUsers = ref<UserDto[]>([]);
const remoteActors = ref<(RemoteFollowDto | RemoteFollowerDto)[]>([]);

async function load() {
    loading.value = true;
    localUsers.value = [];
    remoteActors.value = [];

    try {
        const [localResponse, remoteResponse] = await Promise.all([
            mode.value === 'followers'
                ? api.users.getFollowers(props.userId)
                : api.users.getFollowings(props.userId),
            mode.value === 'followers'
                ? api.users.getUserActivityPubFollowers(props.userId)
                : api.users.getUserActivityPubFollowing(props.userId),
        ]);
        localUsers.value = localResponse.data;
        remoteActors.value = remoteResponse.data;
    } finally {
        loading.value = false;
    }
}

function open(requestedMode: 'followers' | 'following') {
    mode.value = requestedMode;
    modal.value?.show();
    load();
}

defineExpose({ open });
</script>

<template>
    <dialog ref="followListModal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                {{
                    t(
                        mode === 'followers'
                            ? 'profile.stats.followers_modal_title'
                            : 'profile.stats.following_modal_title',
                    )
                }}
            </h3>

            <div v-if="loading" class="flex justify-center py-6">
                <span class="loading loading-spinner" />
            </div>

            <ul v-else class="list">
                <li v-for="localUser in localUsers" :key="localUser.id">
                    <div class="list-row hover-list-entry">
                        <UserListCard :user="localUser" />
                    </div>
                </li>
                <li v-for="actor in remoteActors" :key="actor.actorId">
                    <div class="list-row hover-list-entry">
                        <RemoteActorListCard :actor="actor" />
                    </div>
                </li>
            </ul>

            <p
                v-if="!loading && !localUsers.length && !remoteActors.length"
                class="text-base-content/50 py-6 text-center text-sm"
            >
                {{ t('profile.stats.no_results') }}
            </p>

            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">{{ t('verbs.close') }}</button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>{{ t('verbs.close') }}</button>
        </form>
    </dialog>
</template>
