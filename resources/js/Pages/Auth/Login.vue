<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AppLayout title="Вход в систему">
        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8">
                <!-- Заголовок -->
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-base-content">
                        Вход в систему
                    </h2>
                    <p class="mt-2 text-base-content/70">
                        Войдите в свой аккаунт для записи к врачу
                    </p>
                </div>

                <!-- Форма -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div v-if="status" class="alert alert-success mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ status }}</span>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Email -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Email</span>
                                </label>
                                <input
                                    type="email"
                                    v-model="form.email"
                                    class="input input-bordered w-full"
                                    :class="{ 'input-error': form.errors.email }"
                                    placeholder="Введите ваш email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                />
                                <label v-if="form.errors.email" class="label">
                                    <span class="label-text-alt text-error">{{ form.errors.email }}</span>
                                </label>
                            </div>

                            <!-- Password -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Пароль</span>
                                </label>
                                <input
                                    type="password"
                                    v-model="form.password"
                                    class="input input-bordered w-full"
                                    :class="{ 'input-error': form.errors.password }"
                                    placeholder="Введите ваш пароль"
                                    required
                                    autocomplete="current-password"
                                />
                                <label v-if="form.errors.password" class="label">
                                    <span class="label-text-alt text-error">{{ form.errors.password }}</span>
                                </label>
                            </div>

                            <!-- Remember me -->
                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-2">
                                    <input
                                        type="checkbox"
                                        v-model="form.remember"
                                        class="checkbox checkbox-primary"
                                    />
                                    <span class="label-text">Запомнить меня</span>
                                </label>
                            </div>

                            <!-- Submit button -->
                            <div class="form-control">
                                <button
                                    type="submit"
                                    class="btn btn-primary w-full"
                                    :class="{ 'loading': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                    {{ form.processing ? 'Вход...' : 'Войти' }}
                                </button>
                            </div>

                            <!-- Links -->
                            <div class="text-center space-y-2">
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="link link-primary text-sm"
                                >
                                    Забыли пароль?
                                </Link>
                                
                                <div class="text-sm text-base-content/70">
                                    Нет аккаунта? 
                                    <Link :href="route('register')" class="link link-primary">
                                        Зарегистрироваться
                                    </Link>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Back to home -->
                <div class="text-center">
                    <Link href="/" class="link link-primary">
                        ← Вернуться на главную
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
