<script setup lang="ts">
import { api } from '@/api';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Search, Users } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { RemoteActorProfileDto, RemoteFollowDto } from '../../../types/Api.gen';

const { t } = useI18n();
useTitle(t('fediverse.title'));

const handle = ref('');
const resolvedActor = ref<RemoteActorProfileDto | null>(null);
const resolveError = ref<string | null>(null);
const resolving = ref(false);
const following = ref(false);
const remoteFollows = ref<RemoteFollowDto[]>([]);
const loadingFollows = ref(true);

function instanceOf(actorId: string): string {
    try {
        return new URL(actorId).hostname;
    } catch {
        return actorId;
    }
}

async function resolveActor() {
    if (!handle.value.trim()) return;
    resolving.value = true;
    resolveError.value = null;
    resolvedActor.value = null;

    try {
        const response = await api.instance.get('/activitypub/resolve', {
            params: { handle: handle.value.trim() },
        });
        resolvedActor.value = response.data;
    } catch (e) {
        resolveError.value =
            e.response?.status === 404
                ? t('fediverse.not_found')
                : t('fediverse.resolve_error');
    } finally {
        resolving.value = false;
    }
}

async function follow() {
    if (!resolvedActor.value) return;
    following.value = true;

    try {
        await api.instance.post('/activitypub/follow', {
            actor_id: resolvedActor.value.actorId,
        });
        resolvedActor.value.followState = 'pending';
        await loadFollowing();
    } finally {
        following.value = false;
    }
}

async function unfollow(actorId: string) {
    await api.instance.delete('/activitypub/follow', {
        data: { actor_id: actorId },
    });
    remoteFollows.value = remoteFollows.value.filter(
        (f) => f.actorId !== actorId,
    );
    if (resolvedActor.value?.actorId === actorId) {
        resolvedActor.value.followState = null;
    }
}

async function loadFollowing() {
    loadingFollows.value = true;
    try {
        const response = await api.instance.get('/activitypub/following');
        remoteFollows.value = response.data;
    } finally {
        loadingFollows.value = false;
    }
}

loadFollowing();
</script>

<template>
    <AuthenticatedLayout>
        <div class="container mx-auto max-w-2xl space-y-8 px-4 py-6">
            <h1 class="text-2xl font-bold">{{ t('fediverse.title') }}</h1>

            <!-- Search -->
            <div class="card bg-base-200">
                <div class="card-body gap-3">
                    <h2 class="card-title text-lg">
                        {{ t('fediverse.find_title') }}
                    </h2>
                    <div class="join w-full">
                        <input
                            v-model="handle"
                            type="text"
                            class="input input-bordered join-item flex-1"
                            :placeholder="t('fediverse.handle_placeholder')"
                            @keydown.enter="resolveActor"
                        />
                        <button
                            class="btn btn-primary join-item"
                            :disabled="resolving || !handle.trim()"
                            @click="resolveActor"
                        >
                            <Search class="size-4" />
                            {{
                                resolving
                                    ? t('verbs.loading')
                                    : t('fediverse.find')
                            }}
                        </button>
                    </div>

                    <p v-if="resolveError" class="text-error text-sm">
                        {{ resolveError }}
                    </p>

                    <!-- Resolved actor card -->
                    <div
                        v-if="resolvedActor"
                        class="bg-base-100 rounded-box flex items-start gap-4 p-4"
                    >
                        <img
                            v-if="resolvedActor.iconUrl"
                            :src="resolvedActor.iconUrl"
                            class="size-14 shrink-0 rounded-full object-cover"
                            alt=""
                        />
                        <div
                            v-else
                            class="bg-base-300 size-14 shrink-0 rounded-full"
                        />

                        <div class="min-w-0 flex-1">
                            <div class="truncate font-semibold">
                                {{
                                    resolvedActor.displayName ||
                                    resolvedActor.preferredUsername
                                }}
                            </div>
                            <div class="text-base-content/60 truncate text-sm">
                                @{{ resolvedActor.preferredUsername }}@{{
                                    instanceOf(resolvedActor.actorId)
                                }}
                            </div>
                            <!-- eslint-disable vue/no-v-html -->
                            <div
                                v-if="resolvedActor.summary"
                                class="mt-1 line-clamp-3 text-sm [&_a]:underline"
                                v-html="resolvedActor.summary"
                            />
                            <!-- eslint-enable vue/no-v-html -->
                        </div>

                        <button
                            v-if="!resolvedActor.followState"
                            class="btn btn-primary btn-sm shrink-0"
                            :disabled="following"
                            @click="follow"
                        >
                            {{ t('fediverse.follow') }}
                        </button>
                        <button
                            v-else-if="resolvedActor.followState === 'pending'"
                            class="btn btn-sm shrink-0"
                            disabled
                        >
                            {{ t('fediverse.pending') }}
                        </button>
                        <button
                            v-else-if="resolvedActor.followState === 'accepted'"
                            class="btn btn-sm shrink-0"
                            @click="unfollow(resolvedActor.actorId)"
                        >
                            {{ t('fediverse.unfollow') }}
                        </button>
                        <span
                            v-else-if="resolvedActor.followState === 'rejected'"
                            class="badge badge-error badge-sm shrink-0"
                        >
                            {{ t('fediverse.rejected') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Following list -->
            <div>
                <h2 class="mb-3 flex items-center gap-2 text-lg font-semibold">
                    <Users class="size-5" />
                    {{ t('fediverse.following_title') }}
                </h2>

                <div v-if="loadingFollows" class="flex justify-center py-6">
                    <span class="loading loading-spinner" />
                </div>

                <p
                    v-else-if="remoteFollows.length === 0"
                    class="text-base-content/50 text-sm"
                >
                    {{ t('fediverse.no_following') }}
                </p>

                <div v-else class="space-y-2">
                    <div
                        v-for="remoteFollow in remoteFollows"
                        :key="remoteFollow.actorId"
                        class="bg-base-200 rounded-box flex items-center gap-3 p-3"
                    >
                        <img
                            v-if="remoteFollow.iconUrl"
                            :src="remoteFollow.iconUrl"
                            class="size-10 shrink-0 rounded-full object-cover"
                            alt=""
                        />
                        <div
                            v-else
                            class="bg-base-300 size-10 shrink-0 rounded-full"
                        />

                        <div class="min-w-0 flex-1">
                            <div class="truncate font-medium">
                                {{
                                    remoteFollow.displayName ||
                                    remoteFollow.preferredUsername
                                }}
                            </div>
                            <div class="text-base-content/60 truncate text-xs">
                                @{{ remoteFollow.preferredUsername }}@{{
                                    instanceOf(remoteFollow.actorId)
                                }}
                            </div>
                        </div>

                        <span
                            v-if="remoteFollow.state === 'pending'"
                            class="badge badge-warning badge-sm shrink-0"
                        >
                            {{ t('fediverse.pending') }}
                        </span>
                        <span
                            v-else-if="remoteFollow.state === 'rejected'"
                            class="badge badge-error badge-sm shrink-0"
                        >
                            {{ t('fediverse.rejected') }}
                        </span>

                        <button
                            class="btn btn-ghost btn-sm shrink-0"
                            @click="unfollow(remoteFollow.actorId)"
                        >
                            {{ t('fediverse.unfollow') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
