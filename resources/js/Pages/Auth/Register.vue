<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AppLayout title="Регистрация">
        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8">
                <!-- Заголовок -->
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-base-content">
                        Регистрация
                    </h2>
                    <p class="mt-2 text-base-content/70">
                        Создайте аккаунт для записи к врачу
                    </p>
                </div>

                <!-- Форма -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Name -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Имя</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="form.name"
                                    class="input input-bordered w-full"
                                    :class="{ 'input-error': form.errors.name }"
                                    placeholder="Введите ваше имя"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />
                                <label v-if="form.errors.name" class="label">
                                    <span class="label-text-alt text-error">{{ form.errors.name }}</span>
                                </label>
                            </div>

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
                                    placeholder="Введите пароль"
                                    required
                                    autocomplete="new-password"
                                />
                                <label v-if="form.errors.password" class="label">
                                    <span class="label-text-alt text-error">{{ form.errors.password }}</span>
                                </label>
                            </div>

                            <!-- Password Confirmation -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Подтверждение пароля</span>
                                </label>
                                <input
                                    type="password"
                                    v-model="form.password_confirmation"
                                    class="input input-bordered w-full"
                                    :class="{ 'input-error': form.errors.password_confirmation }"
                                    placeholder="Подтвердите пароль"
                                    required
                                    autocomplete="new-password"
                                />
                                <label v-if="form.errors.password_confirmation" class="label">
                                    <span class="label-text-alt text-error">{{ form.errors.password_confirmation }}</span>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                    {{ form.processing ? 'Регистрация...' : 'Зарегистрироваться' }}
                                </button>
                            </div>

                            <!-- Links -->
                            <div class="text-center">
                                <div class="text-sm text-base-content/70">
                                    Уже есть аккаунт? 
                                    <Link :href="route('login')" class="link link-primary">
                                        Войти
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
