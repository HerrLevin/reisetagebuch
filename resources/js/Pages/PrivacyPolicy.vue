<script setup lang="ts">
import { api } from '@/api';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useAuthStore } from '@/stores/auth';
import { useUserStore } from '@/stores/user';
import markdownit from 'markdown-it';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { PrivacyPolicyDto } from '../../types/Api.gen';

const { t } = useI18n();
useTitle(t('pages.privacyPolicy.title'));

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const userStore = useUserStore();

const md = markdownit();

const currentPolicy = ref<PrivacyPolicyDto | null>(null);
const upcomingPolicy = ref<PrivacyPolicyDto | null>(null);
const loaded = ref(false);
const acceptingCurrent = ref(false);
const acceptingUpcoming = ref(false);

const currentContent = computed(() =>
    currentPolicy.value ? md.render(currentPolicy.value.content) : null,
);
const upcomingContent = computed(() =>
    upcomingPolicy.value ? md.render(upcomingPolicy.value.content) : null,
);

function formatDate(value: string): string {
    return new Date(value).toLocaleString();
}

function loadCurrent() {
    return api.app
        .getPrivacyPolicy()
        .then((response) => {
            currentPolicy.value = response.data;
        })
        .catch(() => {
            currentPolicy.value = null;
        });
}

function loadUpcoming() {
    return api.app
        .getUpcomingPrivacyPolicy()
        .then((response) => {
            upcomingPolicy.value = response.data;
        })
        .catch(() => {
            upcomingPolicy.value = null;
        });
}

onMounted(() => {
    Promise.all([loadCurrent(), loadUpcoming()]).finally(() => {
        loaded.value = true;
    });
});

function accept(policy: PrivacyPolicyDto, target: 'current' | 'upcoming') {
    const processing =
        target === 'current' ? acceptingCurrent : acceptingUpcoming;
    processing.value = true;

    api.app
        .acceptPrivacyPolicy(policy.id)
        .then((response) => {
            if (target === 'current') {
                currentPolicy.value = response.data;
            } else {
                upcomingPolicy.value = response.data;
            }
            return userStore.fetchUser(true);
        })
        .then(() => {
            const redirect = route.query.redirect as string | undefined;
            if (target === 'current' && redirect) {
                router.push(redirect);
            }
        })
        .finally(() => {
            processing.value = false;
        });
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('pages.privacyPolicy.title') }}
            </h2>
        </template>

        <div class="min-w-full space-y-6">
            <div
                v-if="
                    loaded &&
                    authStore.isAuthenticated() &&
                    currentPolicy &&
                    !currentPolicy.acceptedAt
                "
                class="alert alert-warning shadow-lg"
            >
                {{ t('pages.privacyPolicy.acceptRequiredNotice') }}
            </div>

            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <h1 class="mb-4 text-2xl font-semibold">
                    {{ t('pages.privacyPolicy.title') }}
                </h1>

                <!-- eslint-disable vue/no-v-html -->
                <pre
                    v-if="loaded && currentContent"
                    class="prose"
                    v-html="currentContent"
                ></pre>
                <!-- eslint-enable vue/no-v-html -->

                <p v-else-if="loaded" class="opacity-65">
                    {{ t('pages.privacyPolicy.empty') }}
                </p>

                <div
                    v-if="authStore.isAuthenticated() && currentPolicy"
                    class="mt-6"
                >
                    <button
                        v-if="!currentPolicy.acceptedAt"
                        class="btn btn-primary"
                        :disabled="acceptingCurrent"
                        @click="accept(currentPolicy, 'current')"
                    >
                        {{ t('pages.privacyPolicy.accept') }}
                    </button>
                    <span v-else class="text-success">
                        {{ t('pages.privacyPolicy.accepted') }}
                    </span>
                </div>
            </div>

            <div
                v-if="loaded && upcomingPolicy"
                class="card bg-base-100 min-w-full p-8 shadow-md"
            >
                <h2 class="mb-4 text-xl font-semibold">
                    {{ t('pages.privacyPolicy.upcomingTitle') }}
                </h2>
                <p class="mb-4 opacity-65">
                    {{
                        t('pages.privacyPolicy.upcomingDescription', {
                            date: formatDate(upcomingPolicy.validFrom),
                        })
                    }}
                </p>

                <!-- eslint-disable-next-line vue/no-v-html -->
                <pre class="prose" v-html="upcomingContent"></pre>

                <div v-if="authStore.isAuthenticated()" class="mt-6">
                    <button
                        v-if="!upcomingPolicy.acceptedAt"
                        class="btn btn-primary"
                        :disabled="acceptingUpcoming"
                        @click="accept(upcomingPolicy, 'upcoming')"
                    >
                        {{ t('pages.privacyPolicy.accept') }}
                    </button>
                    <span v-else class="text-success">
                        {{ t('pages.privacyPolicy.accepted') }}
                    </span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
