<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Log in" />

        <h2 class="mb-2 text-center text-2xl font-semibold">Login</h2>
        <div v-if="status" class="text-success mb-4 text-sm font-medium">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="form-control w-full">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    :error="form.errors.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="form-control mt-4 w-full">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    :error="form.errors.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <fieldset class="form-control">
                    <label class="fieldset-label">
                        <input
                            type="checkbox"
                            v-model="form.remember"
                            class="checkbox"
                        />
                        Remember me
                    </label>
                </fieldset>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="link"
                >
                    Forgot your password?
                </Link>
            </div>

            <button
                class="btn btn-primary mt-12 w-full"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Log in
            </button>
        </form>
        <div class="mt-4 text-center">
            Don't have an account yet?
            <Link :href="route('register')" class="link"> Register </Link>
        </div>
    </AuthLayout>
</template>
