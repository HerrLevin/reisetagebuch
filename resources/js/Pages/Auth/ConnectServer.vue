<script setup lang="ts">
import { initApi } from '@/api';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useTitle } from '@/composables/useTitle';
import { setServerUrl } from '@/Services/ServerConfig';
import axios from 'axios';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';

const { t } = useI18n();
useTitle(t('connect_server.title'));

const router = useRouter();
const url = ref('');
const error = ref('');
const processing = ref(false);

const submit = async () => {
    if (!url.value.trim()) {
        return;
    }

    processing.value = true;
    error.value = '';

    try {
        const normalized = await setServerUrl(url.value);
        // Generous timeout: on-device HTTP (CapacitorHttp) round trips for a
        // cold TLS/DNS handshake on a mobile network can run well past what
        // a browser fetch would take.
        await axios.get(`${normalized}/nodeinfo/2.0`, { timeout: 30000 });
        await initApi();
        router.push({ name: 'login' });
    } catch (err) {
        console.error(err);
        error.value = t('connect_server.not_found');
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <div class="bg-base-200 flex min-h-screen items-center justify-center px-4">
        <div class="card bg-base-100 w-full max-w-md p-8 shadow-xl">
            <div class="mb-6 text-center">
                <ApplicationLogo class="mx-auto mb-2 h-16 w-16 fill-current" />
                <h1 class="text-2xl font-semibold">
                    {{ t('connect_server.title') }}
                </h1>
                <p class="text-base-content/70 mt-2 text-sm">
                    {{ t('connect_server.message') }}
                </p>
            </div>

            <form @submit.prevent="submit">
                <div class="form-control w-full">
                    <InputLabel
                        for="server-url"
                        :value="t('connect_server.url_label')"
                    />
                    <TextInput
                        id="server-url"
                        v-model="url"
                        type="text"
                        class="mt-1 block w-full"
                        :placeholder="t('connect_server.url_placeholder')"
                        :error="error"
                        required
                        autofocus
                        autocapitalize="none"
                        autocorrect="off"
                    />
                    <InputError class="mt-2" :message="error" />
                </div>

                <button
                    type="submit"
                    class="btn btn-primary mt-6 w-full"
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                >
                    {{ t('connect_server.submit') }}
                </button>
            </form>
        </div>
    </div>
</template>
