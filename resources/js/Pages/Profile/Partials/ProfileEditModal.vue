<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextArea from '@/Components/TextArea.vue';
import TextInput from '@/Components/TextInput.vue';
import { UserDto } from '@/types';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { PropType, ref, useTemplateRef } from 'vue';

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
        <div class="flex items-center">Edit Profile</div>
    </button>
    <dialog ref="editModal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">Edit my profile</h3>

            <div class="mt-6">
                <InputLabel
                    for="displayname"
                    value="Displayname"
                    class="sr-only"
                />

                <TextInput
                    id="displayname"
                    ref="displaynameInput"
                    v-model="form.name"
                    :error="form.errors.name"
                    class="mt-1 block w-3/4"
                    placeholder="Displayname"
                    @keydown.enter="submit"
                />

                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <div class="mt-6">
                <InputLabel for="bio" value="Bio" class="sr-only" />

                <TextArea
                    id="bio"
                    ref="bioInput"
                    v-model="form.bio"
                    :error="form.errors.bio"
                    class="mt-1 block w-3/4"
                    placeholder="Bio"
                />

                <InputError :message="form.errors.bio" class="mt-2" />
            </div>

            <div class="mt-6">
                <InputLabel for="website" value="Website" class="sr-only" />

                <TextInput
                    id="website"
                    ref="websiteInput"
                    v-model="form.website"
                    :error="form.errors.website"
                    class="mt-1 block w-3/4"
                    placeholder="Website"
                    @keydown.enter="submit"
                />

                <InputError :message="form.errors.website" class="mt-2" />
            </div>
            <div class="mt-6">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Profile Picture</legend>
                    <input
                        type="file"
                        class="file-input w-full"
                        accept=".png, .jpg, .jpeg, .webp"
                        :value="avatarInput"
                        @input="avatarUpload"
                    />
                    <label class="label">Max size 2MB</label>
                    <InputError :message="form.errors.avatar" class="mt-2" />
                </fieldset>
            </div>
            <div class="">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Header Picture</legend>
                    <input
                        type="file"
                        class="file-input w-full"
                        accept=".png, .jpg, .jpeg, .webp"
                        :value="headerInput"
                        @input="headerUpload"
                    />
                    <label class="label">Max size 2MB</label>
                    <InputError :message="form.errors.header" class="mt-2" />
                </fieldset>
            </div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn">Cancel</button>
                </form>

                <button
                    class="btn btn-primary"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click.prevent="submit"
                >
                    Save
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</template>

<style scoped></style>
