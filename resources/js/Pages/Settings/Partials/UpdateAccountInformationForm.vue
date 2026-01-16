<script setup lang="ts">
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const processing = ref(false);
const recentlySuccessful = ref(false);
const user = usePage().props.auth.user;

const form = reactive({
    name: user.name,
    username: user.username,
    email: user.email,
});

function submitForm() {
    processing.value = true;
    axios
        .patch('/api/account', form)
        .then(() => {
            recentlySuccessful.value = true;
            setTimeout(() => {
                recentlySuccessful.value = false;
            }, 2000);
        })
        .catch((error) => {
            alert(error);
        })
        .finally(() => {
            processing.value = false;
        });
}
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

        <form class="mt-6 space-y-6" @submit.prevent="submitForm()">
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
                    required
                    autocomplete="name"
                />
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
                    required
                    autocomplete="username"
                />
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
                    required
                    autocomplete="email"
                />
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
                <button class="btn btn-primary" :disabled="processing">
                    {{ t('verbs.save') }}
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="recentlySuccessful" class="text-sm opacity-65">
                        {{ t('verbs.saved') }}
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
