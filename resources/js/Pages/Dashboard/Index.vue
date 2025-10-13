<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object
});
</script>

<template>
    <AppLayout title="Клиника - Запись к врачу">
        <!-- Hero Section -->
        <div class="hero min-h-screen bg-gradient-to-br from-primary/10 via-base-100 to-secondary/10">
            <div class="hero-content text-center">
                <div class="max-w-4xl">
                    <h1 class="text-5xl font-bold mb-6 bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                        🏥 Клиника "Здоровье"
                    </h1>
                    <p class="text-xl mb-8 text-base-content/80">
                        Современная система записи к врачу с real-time обновлениями очередей
                    </p>
                    
                    <!-- Статистика -->
                    <div class="stats stats-vertical lg:stats-horizontal shadow-lg mb-8">
                        <div class="stat">
                            <div class="stat-figure text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </div>
                            <div class="stat-title">Специальностей</div>
                            <div class="stat-value text-primary">{{ stats?.specialties_count || 0 }}</div>
                        </div>
                        
                        <div class="stat">
                            <div class="stat-figure text-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="stat-title">Врачей</div>
                            <div class="stat-value text-secondary">{{ stats?.doctors_count || 0 }}</div>
                        </div>
                        
                        <div class="stat">
                            <div class="stat-figure text-accent">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                </svg>
                            </div>
                            <div class="stat-title">Real-time</div>
                            <div class="stat-value text-accent">🔴 Live</div>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link href="/specialties" class="btn btn-primary btn-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Записаться к врачу
                        </Link>
                        
                        <Link href="/login" class="btn btn-outline btn-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Войти в систему
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Популярные специальности -->
        <div v-if="stats?.featured_specialties?.length" class="py-16 bg-base-200">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Популярные специальности</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="specialty in stats.featured_specialties" 
                        :key="specialty.id"
                        class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow"
                    >
                        <figure class="px-6 pt-6">
                            <div class="w-16 h-16 bg-primary/20 rounded-full flex items-center justify-center">
                                <span class="text-2xl">{{ specialty.icon || '🏥' }}</span>
                            </div>
                        </figure>
                        <div class="card-body">
                            <h3 class="card-title">{{ specialty.name }}</h3>
                            <p class="text-base-content/70">{{ specialty.description || 'Профессиональная медицинская помощь' }}</p>
                            <div class="card-actions justify-end">
                                <Link :href="`/specialties`" class="btn btn-primary btn-sm">
                                    Выбрать врача
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Преимущества -->
        <div class="py-16">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Почему выбирают нас?</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-primary/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">⚡</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Быстрая запись</h3>
                        <p class="text-base-content/70">Запишитесь к врачу за несколько кликов</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-secondary/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">🔴</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Real-time очереди</h3>
                        <p class="text-base-content/70">Мгновенные обновления позиций в очереди</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-accent/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">👨‍⚕️</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Опытные врачи</h3>
                        <p class="text-base-content/70">Квалифицированные специалисты с большим опытом</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="py-16 bg-primary text-primary-content">
            <div class="max-w-4xl mx-auto text-center px-4">
                <h2 class="text-3xl font-bold mb-4">Готовы записаться к врачу?</h2>
                <p class="text-xl mb-8 opacity-90">Выберите специальность и найдите подходящего врача</p>
                <Link href="/specialties" class="btn btn-secondary btn-lg">
                    Начать запись
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
