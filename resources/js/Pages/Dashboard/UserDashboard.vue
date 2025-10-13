<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object
});
</script>

<template>
    <AppLayout title="Мои записи">
        <div class="max-w-7xl mx-auto">
            <!-- Заголовок -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold">Добро пожаловать, {{ user?.name }}!</h1>
                <p class="text-base-content/70 mt-2">Управляйте своими записями к врачу</p>
            </div>

            <!-- Быстрые действия -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <Link href="/specialties" class="card bg-primary text-primary-content shadow-xl hover:shadow-2xl transition-shadow">
                    <div class="card-body text-center">
                        <div class="text-4xl mb-2">📅</div>
                        <h2 class="card-title justify-center">Записаться к врачу</h2>
                        <p>Выберите специальность и врача</p>
                    </div>
                </Link>

                <Link href="/appointments/mine" class="card bg-secondary text-secondary-content shadow-xl hover:shadow-2xl transition-shadow">
                    <div class="card-body text-center">
                        <div class="text-4xl mb-2">📋</div>
                        <h2 class="card-title justify-center">Мои записи</h2>
                        <p>Просмотр и управление записями</p>
                    </div>
                </Link>

                <div class="card bg-accent text-accent-content shadow-xl">
                    <div class="card-body text-center">
                        <div class="text-4xl mb-2">🔴</div>
                        <h2 class="card-title justify-center">Live очереди</h2>
                        <p>Real-time обновления позиций</p>
                    </div>
                </div>
            </div>

            <!-- Статистика -->
            <div class="stats stats-vertical lg:stats-horizontal shadow-lg mb-8">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Активные записи</div>
                    <div class="stat-value text-primary">0</div>
                    <div class="stat-desc">Записей на сегодня</div>
                </div>
                
                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Следующий приём</div>
                    <div class="stat-value text-secondary">--:--</div>
                    <div class="stat-desc">Время приёма</div>
                </div>
                
                <div class="stat">
                    <div class="stat-figure text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Позиция в очереди</div>
                    <div class="stat-value text-accent">-</div>
                    <div class="stat-desc">Текущая позиция</div>
                </div>
            </div>

            <!-- Последние записи -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title mb-4">Последние записи</h2>
                    
                    <div class="text-center py-8 text-base-content/70">
                        <div class="text-6xl mb-4">📋</div>
                        <p class="text-lg mb-4">У вас пока нет записей</p>
                        <Link href="/specialties" class="btn btn-primary">
                            Записаться к врачу
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
