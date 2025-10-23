<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <AuthLayout>
        <Head :title="t('auth.verify_email.title')" />

        <div class="mb-4 text-sm">
            {{ t('auth.verify_email.message') }}
        </div>

        <div
            v-if="verificationLinkSent"
            class="text-success mb-4 text-sm font-medium"
        >
            {{ t('auth.verify_email.link_sent') }}
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <button
                    class="btn btn-primary"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ t('auth.verify_email.resend') }}
                </button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="btn btn-link"
                >
                    {{ t('auth.verify_email.logout') }}
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
