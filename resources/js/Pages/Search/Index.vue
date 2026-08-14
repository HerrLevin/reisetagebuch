<script setup lang="ts">
import { api } from '@/api';
import RemoteFollowButton from '@/Pages/Profile/Partials/RemoteFollowButton.vue';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserListCard from '@/Pages/Posts/Partials/UserListCard.vue';
import { Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { debounce } from 'vue-debounce';
import { useI18n } from 'vue-i18n';
import { RemoteActorProfileDto, UserDto } from '../../../types/Api.gen';

const { t } = useI18n();
useTitle(t('search.title'));

const query = ref('');
const localResults = ref<UserDto[]>([]);
const searching = ref(false);
const searched = ref(false);

const looksLikeHandle = computed(() => {
    const parts = query.value.trim().replace(/^@/, '').split('@');

    return parts.length === 2 && parts[0].length > 0 && parts[1].length > 0;
});

function searchLocalUsers() {
    const q = query.value.trim();
    if (q.length === 0) {
        localResults.value = [];
        searched.value = false;
        return;
    }

    searching.value = true;
    api.users
        .searchUsers({ q })
        .then((response) => {
            localResults.value = response.data;
        })
        .catch(() => {
            localResults.value = [];
        })
        .finally(() => {
            searching.value = false;
            searched.value = true;
        });
}
const debouncedSearch = debounce(() => searchLocalUsers(), 300);

// Exact-handle Fediverse lookup (secondary, automatic once the query looks like a handle)
const resolvedActor = ref<RemoteActorProfileDto | null>(null);
const resolveError = ref<string | null>(null);
const resolving = ref(false);
let lastResolvedQuery: string | null = null;

function instanceOf(actorId: string): string {
    try {
        return new URL(actorId).hostname;
    } catch {
        return actorId;
    }
}

async function resolveActor() {
    if (!looksLikeHandle.value) return;
    const handle = query.value.trim();
    lastResolvedQuery = handle;
    resolving.value = true;
    resolveError.value = null;
    resolvedActor.value = null;

    try {
        const response = await api.instance.get('/activitypub/resolve', {
            params: { handle },
            validateStatus: (status) => status === 200 || status === 404,
        });

        if (response.status === 404) {
            resolveError.value = t('fediverse.not_found');
        } else {
            resolvedActor.value = response.data;
        }
    } catch {
        resolveError.value = t('fediverse.resolve_error');
    } finally {
        resolving.value = false;
    }
}
const debouncedResolveActor = debounce(() => resolveActor(), 300);

function onInput() {
    debouncedSearch();

    if (looksLikeHandle.value) {
        if (query.value.trim() !== lastResolvedQuery) {
            debouncedResolveActor();
        }
    } else {
        lastResolvedQuery = null;
        resolvedActor.value = null;
        resolveError.value = null;
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="container mx-auto max-w-2xl space-y-8 px-4 py-6">
            <h1 class="text-2xl font-bold">{{ t('search.title') }}</h1>

            <div class="join w-full">
                <input
                    v-model="query"
                    type="text"
                    class="input input-bordered join-item flex-1"
                    :placeholder="t('search.placeholder')"
                    @input="onInput"
                    @keydown.enter="resolveActor"
                />
                <span class="btn btn-square join-item pointer-events-none">
                    <Search class="size-4" />
                </span>
            </div>

            <div v-if="searching" class="flex justify-center py-6">
                <span class="loading loading-spinner" />
            </div>

            <ul v-else-if="localResults.length" class="list">
                <li v-for="localUser in localResults" :key="localUser.id">
                    <div class="list-row hover-list-entry">
                        <UserListCard :user="localUser" />
                    </div>
                </li>
            </ul>

            <p v-else-if="searched" class="text-base-content/50 text-sm">
                {{ t('search.no_results') }}
            </p>

            <!-- Fediverse handle lookup: fires automatically once the query looks like a handle -->
            <div v-if="looksLikeHandle" class="card bg-base-200">
                <div class="card-body gap-3">
                    <h2 class="card-title text-lg">
                        {{ t('search.fediverse_section_title') }}
                    </h2>

                    <div
                        v-if="resolving"
                        class="text-base-content/60 flex items-center gap-2 text-sm"
                    >
                        <span class="loading loading-spinner loading-sm" />
                        {{ t('verbs.loading') }}
                    </div>

                    <div
                        v-else-if="resolveError"
                        class="flex items-center gap-3"
                    >
                        <p class="text-error text-sm">{{ resolveError }}</p>
                        <button class="btn btn-sm" @click="resolveActor">
                            {{ t('fediverse.find') }}
                        </button>
                    </div>

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

                        <RemoteFollowButton
                            v-model:follow-state="resolvedActor.followState"
                            :actor-id="resolvedActor.actorId"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
