<script setup lang="ts">
import { api } from '@admin/api';
import PageHeader from '@admin/Components/PageHeader.vue';
import InputLabel from '@shared/Components/InputLabel.vue';
import { onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const processing = ref(false);
const recentlySuccessful = ref(false);

const form = reactive<{ content: string | null }>({
    content: '',
});

onMounted(() => {
    api.app.getImprint().then((response) => {
        form.content = response.data.content ?? '';
    });
});

function formSubmit() {
    processing.value = true;
    recentlySuccessful.value = false;
    api.app
        .updateImprint({ content: form.content })
        .then((response) => {
            form.content = response.data.content ?? '';
            recentlySuccessful.value = true;
            setTimeout(() => {
                recentlySuccessful.value = false;
            }, 2000);
        })
        .catch((error) => {
            console.error('Error updating imprint:', error);
        })
        .finally(() => {
            processing.value = false;
        });
}
</script>

<template>
    <PageHeader
        :title="t('settings.imprint.title')"
        :description="t('settings.imprint.description')"
    />

    <section class="card bg-base-100 max-w-3xl p-6 shadow">
        <form class="space-y-6" @submit.prevent="formSubmit()">
            <div>
                <InputLabel
                    for="imprintContent"
                    :value="t('settings.imprint.content')"
                />

                <textarea
                    id="imprintContent"
                    v-model="form.content"
                    class="textarea mt-1 block min-h-48 w-full"
                ></textarea>
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
