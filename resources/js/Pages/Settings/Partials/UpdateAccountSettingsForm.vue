<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';

defineProps<{
    status?: String;
}>();

const user = usePage().props.auth.user;

const form = useForm({
    defaultNewPostView: user.settings?.default_new_post_view || 'location',
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">Account Settings</h2>

            <p class="mt-1 text-sm opacity-65"></p>
        </header>

        <form
            @submit.prevent="form.patch(route('account.settings.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="defaultNewPostView" value="Default New Post" />

                <SelectInput
                    id="defaultNewPostView"
                    class="mt-1 block w-full"
                    :error="form.errors.defaultNewPostView"
                    v-model="form.defaultNewPostView"
                    :options="[
                        { value: 'location', label: 'Location' },
                        { value: 'departures', label: 'Departures' },
                        { value: 'text', label: 'Text' },
                    ]"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.defaultNewPostView"
                />
            </div>

            <div class="flex items-center gap-4">
                <button class="btn btn-primary" :disabled="form.processing">
                    Save
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
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
