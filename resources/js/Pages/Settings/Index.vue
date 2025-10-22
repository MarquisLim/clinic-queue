<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});
</script>

<template>
    <AppLayout title="Настройки">
        <Head title="Настройки" />

        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold">Настройки</h1>
                <p class="text-base-content/70 mt-2">Управляйте настройками своего аккаунта</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Профиль -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="avatar placeholder">
                                <div class="bg-primary text-primary-content rounded-full w-12">
                                    <span class="text-xl">{{ user?.name?.charAt(0)?.toUpperCase() }}</span>
                                </div>
                            </div>
                            <div>
                                <h2 class="card-title">Профиль</h2>
                                <p class="text-sm text-base-content/70">Управление личной информацией</p>
                            </div>
                        </div>
                        <p class="text-sm text-base-content/60 mb-4">
                            Обновите информацию о себе, измените пароль или удалите аккаунт.
                        </p>
                        <div class="card-actions">
                            <Link href="/profile" class="btn btn-primary">
                                Открыть профиль
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Уведомления -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="avatar placeholder">
                                <div class="bg-secondary text-secondary-content rounded-full w-12">
                                    <span class="text-xl">🔔</span>
                                </div>
                            </div>
                            <div>
                                <h2 class="card-title">Уведомления</h2>
                                <p class="text-sm text-base-content/70">Настройки уведомлений</p>
                            </div>
                        </div>
                        <p class="text-sm text-base-content/60 mb-4">
                            Настройте, какие уведомления вы хотите получать о записях к врачу.
                        </p>
                        <div class="card-actions">
                            <button class="btn btn-secondary" disabled>
                                Скоро будет доступно
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Безопасность -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="avatar placeholder">
                                <div class="bg-accent text-accent-content rounded-full w-12">
                                    <span class="text-xl">🔒</span>
                                </div>
                            </div>
                            <div>
                                <h2 class="card-title">Безопасность</h2>
                                <p class="text-sm text-base-content/70">Настройки безопасности</p>
                            </div>
                        </div>
                        <p class="text-sm text-base-content/60 mb-4">
                            Управляйте настройками безопасности вашего аккаунта.
                        </p>
                        <div class="card-actions">
                            <button class="btn btn-accent" disabled>
                                Скоро будет доступно
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Приватность -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="avatar placeholder">
                                <div class="bg-info text-info-content rounded-full w-12">
                                    <span class="text-xl">👁️</span>
                                </div>
                            </div>
                            <div>
                                <h2 class="card-title">Приватность</h2>
                                <p class="text-sm text-base-content/70">Настройки приватности</p>
                            </div>
                        </div>
                        <p class="text-sm text-base-content/60 mb-4">
                            Управляйте тем, какую информацию о себе вы хотите показывать.
                        </p>
                        <div class="card-actions">
                            <button class="btn btn-info" disabled>
                                Скоро будет доступно
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Информация о системе -->
            <div class="card bg-base-100 shadow-xl mt-6">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Информация о системе</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-title">Роль</div>
                            <div class="stat-value text-lg">
                                <span class="badge badge-primary">
                                    {{ user?.roles?.[0]?.name || 'Пользователь' }}
                                </span>
                            </div>
                        </div>
                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-title">Дата регистрации</div>
                            <div class="stat-value text-lg">
                                {{ new Date(user?.created_at).toLocaleDateString('ru-RU') }}
                            </div>
                        </div>
                        <div class="stat bg-base-200 rounded-lg">
                            <div class="stat-title">Последнее обновление</div>
                            <div class="stat-value text-lg">
                                {{ new Date(user?.updated_at).toLocaleDateString('ru-RU') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>



