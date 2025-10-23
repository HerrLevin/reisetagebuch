<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    username: user.username,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">
                {{ t('settings.account_information.title') }}
            </h2>

            <p class="mt-1 text-sm opacity-65">
                {{ t('settings.account_information.description') }}
            </p>
        </header>

        <form
            class="mt-6 space-y-6"
            @submit.prevent="form.patch(route('account.update'))"
        >
            <div>
                <InputLabel
                    for="name"
                    :value="t('settings.account_information.name')"
                />

                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    :error="form.errors.name"
                    required
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel
                    for="username"
                    :value="t('settings.account_information.username')"
                />

                <TextInput
                    id="username"
                    v-model="form.username"
                    type="text"
                    class="mt-1 block w-full"
                    :error="form.errors.username"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.username" />
            </div>

            <div>
                <InputLabel
                    for="email"
                    :value="t('settings.account_information.email')"
                />

                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    :error="form.errors.email"
                    required
                    autocomplete="email"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm">
                    {{ t('settings.account_information.email_not_verified') }}
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="link"
                    >
                        {{
                            t(
                                'settings.account_information.resend_verification',
                            )
                        }}
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="text-success mt-2 text-sm font-medium"
                >
                    {{
                        t('settings.account_information.verification_link_sent')
                    }}
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button class="btn btn-primary" :disabled="form.processing">
                    {{ t('verbs.save') }}
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm opacity-65"
                    >
                        {{ t('verbs.saved') }}
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
