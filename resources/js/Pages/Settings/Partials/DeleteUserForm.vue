<script setup lang="ts">
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import axios from 'axios';
import { reactive, ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const passwordInput = ref<HTMLInputElement | null>(null);

const processing = ref(false);
const form = reactive({
    password: '',
});

const deleteModal = useTemplateRef('deleteModal');

const deleteUser = () => {
    processing.value = true;
    axios
        .delete('/api/account', { data: { password: form.password } })
        .then(() => {
            deleteModal.value?.close();
            window.location.href = '/';
        })
        .catch((error) => {
            passwordInput.value?.focus();
            // http response contains "message" field with error description
            alert(
                error.response.data.message ||
                    t('settings.delete_account.error'),
            );
        })
        .finally(() => {
            processing.value = false;
        });
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium">
                {{ t('settings.delete_account.title') }}
            </h2>

            <p class="mt-1 text-sm opacity-65">
                {{ t('settings.delete_account.warning') }}
            </p>
        </header>

        <button class="btn btn-error" @click="deleteModal?.showModal()">
            {{ t('settings.delete_account.title') }}
        </button>
        <dialog ref="deleteModal" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold">
                    {{ t('settings.delete_account.confirmation') }}
                </h3>
                <p class="py-4 opacity-65">
                    {{ t('settings.delete_account.enter_password') }}
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="Password"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        :placeholder="t('settings.delete_account.password')"
                        @keyup.enter="deleteUser"
                    />
                </div>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn">
                            {{ t('verbs.cancel') }}
                        </button>
                    </form>

                    <button
                        class="btn btn-error"
                        :class="{ 'opacity-25': processing }"
                        :disabled="processing"
                        @click="deleteUser"
                    >
                        {{ t('settings.delete_account.confirm') }}
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>{{ t('settings.delete_account.confirm') }}</button>
            </form>
        </dialog>
    </section>
</template>
