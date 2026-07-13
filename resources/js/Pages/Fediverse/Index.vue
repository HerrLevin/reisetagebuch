<script setup lang="ts">
import { api } from '@/api';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Search, Users } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
useTitle(t('fediverse.title'));

interface RemoteActor {
    actor_id: string;
    display_name: string | null;
    preferred_username: string | null;
    summary: string | null;
    icon_url: string | null;
    profile_url: string | null;
    follow_state: 'pending' | 'accepted' | 'rejected' | null;
}

interface RemoteFollow {
    actor_id: string;
    state: string;
    created_at: string;
    display_name: string | null;
    preferred_username: string | null;
    icon_url: string | null;
    profile_url: string | null;
}

const handle = ref('');
const resolvedActor = ref<RemoteActor | null>(null);
const resolveError = ref<string | null>(null);
const resolving = ref(false);
const following = ref(false);
const remoteFollows = ref<RemoteFollow[]>([]);
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
    } catch (e: any) {
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
            actor_id: resolvedActor.value.actor_id,
        });
        resolvedActor.value.follow_state = 'pending';
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
        (f) => f.actor_id !== actorId,
    );
    if (resolvedActor.value?.actor_id === actorId) {
        resolvedActor.value.follow_state = null;
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
                            v-if="resolvedActor.icon_url"
                            :src="resolvedActor.icon_url"
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
                                    resolvedActor.display_name ||
                                    resolvedActor.preferred_username
                                }}
                            </div>
                            <div class="text-base-content/60 truncate text-sm">
                                @{{ resolvedActor.preferred_username }}@{{
                                    instanceOf(resolvedActor.actor_id)
                                }}
                            </div>
                            <div
                                v-if="resolvedActor.summary"
                                class="mt-1 line-clamp-3 text-sm [&_a]:underline"
                                v-html="resolvedActor.summary"
                            />
                        </div>

                        <button
                            v-if="!resolvedActor.follow_state"
                            class="btn btn-primary btn-sm shrink-0"
                            :disabled="following"
                            @click="follow"
                        >
                            {{ t('fediverse.follow') }}
                        </button>
                        <button
                            v-else-if="resolvedActor.follow_state === 'pending'"
                            class="btn btn-sm shrink-0"
                            disabled
                        >
                            {{ t('fediverse.pending') }}
                        </button>
                        <button
                            v-else-if="
                                resolvedActor.follow_state === 'accepted'
                            "
                            class="btn btn-sm shrink-0"
                            @click="unfollow(resolvedActor.actor_id)"
                        >
                            {{ t('fediverse.unfollow') }}
                        </button>
                        <span
                            v-else-if="
                                resolvedActor.follow_state === 'rejected'
                            "
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
                        v-for="follow in remoteFollows"
                        :key="follow.actor_id"
                        class="bg-base-200 rounded-box flex items-center gap-3 p-3"
                    >
                        <img
                            v-if="follow.icon_url"
                            :src="follow.icon_url"
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
                                    follow.display_name ||
                                    follow.preferred_username
                                }}
                            </div>
                            <div class="text-base-content/60 truncate text-xs">
                                @{{ follow.preferred_username }}@{{
                                    instanceOf(follow.actor_id)
                                }}
                            </div>
                        </div>

                        <span
                            v-if="follow.state === 'pending'"
                            class="badge badge-warning badge-sm shrink-0"
                        >
                            {{ t('fediverse.pending') }}
                        </span>
                        <span
                            v-else-if="follow.state === 'rejected'"
                            class="badge badge-error badge-sm shrink-0"
                        >
                            {{ t('fediverse.rejected') }}
                        </span>

                        <button
                            class="btn btn-ghost btn-sm shrink-0"
                            @click="unfollow(follow.actor_id)"
                        >
                            {{ t('fediverse.unfollow') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
