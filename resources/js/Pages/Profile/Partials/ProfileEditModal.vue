<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextArea from '@/Components/TextArea.vue';
import TextInput from '@/Components/TextInput.vue';
import { UserDto } from '@/types';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { PropType, ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const authUser = usePage().props.auth.user;

const props = defineProps({
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});

const form = useForm({
    bio: '',
    website: '',
    avatar: null as File | null,
    header: null as File | null,
    name: '',
});

const editModal = useTemplateRef('editModal');
const avatarInput = ref('');
const headerInput = ref('');

const resetModal = () => {
    form.reset();
    avatarInput.value = '';
    headerInput.value = '';
    form.name = props.user.name;
    form.bio = props.user.bio || '';
    form.website = props.user.website || '';
};

const openModal = () => {
    resetModal();
    editModal.value?.show();
};

const submit = () => {
    form.post(route('profile.update', authUser.username), {
        preserveScroll: true,
        onSuccess: () => {
            resetModal();
            editModal.value?.close();
            router.reload();
        },
        onError: () => {
            if (form.errors.avatar) {
                avatarInput.value = '';
                form.avatar = null;
            }
            if (form.errors.header) {
                headerInput.value = '';
                form.header = null;
            }
        },
    });
};

const avatarUpload = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        form.avatar = input.files[0];
    }
};
const headerUpload = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        form.header = input.files[0];
    }
};
</script>

<template>
    <button
        v-if="user.id === authUser.id"
        class="btn rounded-full"
        type="button"
        @click="openModal()"
    >
        <span class="flex items-center">
            {{ t('profile.edit_profile') }}
        </span>
    </button>
    <dialog ref="editModal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                {{ t('profile.edit_my_profile') }}
            </h3>

            <div class="mt-6">
                <InputLabel
                    for="displayname"
                    :value="t('profile.display_name')"
                    class="sr-only"
                />

                <TextInput
                    id="displayname"
                    ref="displaynameInput"
                    v-model="form.name"
                    :error="form.errors.name"
                    class="mt-1 block w-3/4"
                    :placeholder="t('profile.display_name')"
                    @keydown.enter="submit"
                />

                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <div class="mt-6">
                <InputLabel
                    for="bio"
                    :value="t('profile.bio')"
                    class="sr-only"
                />

                <TextArea
                    id="bio"
                    ref="bioInput"
                    v-model="form.bio"
                    :error="form.errors.bio"
                    class="mt-1 block w-3/4"
                    :placeholder="t('profile.bio')"
                />

                <InputError :message="form.errors.bio" class="mt-2" />
            </div>

            <div class="mt-6">
                <InputLabel
                    for="website"
                    :value="t('profile.bio')"
                    class="sr-only"
                />

                <TextInput
                    id="website"
                    ref="websiteInput"
                    v-model="form.website"
                    :error="form.errors.website"
                    class="mt-1 block w-3/4"
                    :placeholder="t('profile.website')"
                    @keydown.enter="submit"
                />

                <InputError :message="form.errors.website" class="mt-2" />
            </div>
            <div class="mt-6">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">
                        {{ t('profile.profile_picture') }}
                    </legend>
                    <input
                        type="file"
                        class="file-input w-full"
                        accept=".png, .jpg, .jpeg, .webp"
                        :value="avatarInput"
                        @input="avatarUpload"
                    />
                    <label class="label">
                        {{ t('profile.max_size') }}
                    </label>
                    <InputError :message="form.errors.avatar" class="mt-2" />
                </fieldset>
            </div>
            <div class="">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">
                        {{ t('profile.header_image') }}
                    </legend>
                    <input
                        type="file"
                        class="file-input w-full"
                        accept=".png, .jpg, .jpeg, .webp"
                        :value="headerInput"
                        @input="headerUpload"
                    />
                    <label class="label">
                        {{ t('profile.max_size') }}
                    </label>
                    <InputError :message="form.errors.header" class="mt-2" />
                </fieldset>
            </div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">
                        {{ t('verbs.cancel') }}
                    </button>
                </form>

                <button
                    class="btn btn-primary"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click.prevent="submit"
                >
                    {{ t('verbs.save') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>
                {{ t('verbs.close') }}
            </button>
        </form>
    </dialog>
</template>
