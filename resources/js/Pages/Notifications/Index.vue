<template>
    <Head title="Уведомления" />

    <AppLayout title="Уведомления">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Уведомления</h1>
                <p class="text-gray-600">Ваши уведомления и напоминания</p>
            </div>
            <div class="flex gap-2">
                <button 
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="btn btn-outline"
                    :disabled="loading"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Отметить все как прочитанные
                </button>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <div class="flex gap-4 items-center">
                    <div class="form-control">
                        <label class="label cursor-pointer">
                            <span class="label-text mr-2">Только непрочитанные</span>
                            <input 
                                type="checkbox" 
                                v-model="showUnreadOnly"
                                @change="applyFilters"
                                class="checkbox checkbox-primary" 
                            />
                        </label>
                    </div>
                    <div class="form-control">
                        <select v-model="selectedType" @change="applyFilters" class="select select-bordered">
                            <option value="">Все типы</option>
                            <option value="appointment_reminder">Напоминания о записи</option>
                            <option value="appointment_created">Новая запись</option>
                            <option value="appointment_cancelled">Отмена записи</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Список уведомлений -->
        <div class="space-y-4">
            <div v-if="loading" class="text-center py-8">
                <span class="loading loading-spinner loading-lg"></span>
                <p class="mt-4">Загрузка уведомлений...</p>
            </div>

            <div v-else-if="notifications.data && notifications.data.length > 0">
                <div 
                    v-for="notification in notifications.data" 
                    :key="notification.id"
                    class="card bg-base-100 shadow-sm"
                    :class="{ 'ring-2 ring-primary': !notification.read_at }"
                >
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <!-- Иконка типа уведомления -->
                            <div class="flex-shrink-0">
                                <div 
                                    class="w-10 h-10 rounded-full flex items-center justify-center"
                                    :class="getNotificationIconClass(notification.type)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Содержимое уведомления -->
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-medium text-base">{{ notification.data.message }}</h3>
                                        <p class="text-sm text-base-content/70 mt-1">
                                            {{ formatTime(notification.created_at) }}
                                        </p>
                                        
                                        <!-- Дополнительная информация -->
                                        <div v-if="notification.data.ticket_no" class="mt-2">
                                            <span class="badge badge-outline">
                                                Талон: {{ notification.data.ticket_no }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Действия -->
                                    <div class="flex gap-2">
                                        <button 
                                            v-if="!notification.read_at"
                                            @click="markAsRead(notification.id)"
                                            class="btn btn-xs btn-outline"
                                            title="Отметить как прочитанное"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button 
                                            @click="deleteNotification(notification.id)"
                                            class="btn btn-xs btn-ghost text-error"
                                            title="Удалить"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Пагинация -->
                <div v-if="notifications.links" class="mt-6">
                    <Pagination :links="notifications.links" />
                </div>
            </div>

            <!-- Пустое состояние -->
            <div v-else class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет уведомлений</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ showUnreadOnly ? 'Все уведомления прочитаны' : 'У вас пока нет уведомлений' }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import axios from 'axios'

const props = defineProps({
    notifications: Object
})

const loading = ref(false)
const showUnreadOnly = ref(false)
const selectedType = ref('')
const unreadCount = ref(0)

onMounted(() => {
    loadUnreadCount()
})

const loadUnreadCount = async () => {
    try {
        const response = await axios.get('/notifications/unread-count')
        unreadCount.value = response.data.count
    } catch (error) {
        console.error('Error loading unread count:', error)
    }
}

const markAsRead = async (notificationId) => {
    try {
        await axios.post(`/notifications/${notificationId}/read`)
        loadUnreadCount()
        // Обновляем локальное состояние
        const notification = props.notifications.data.find(n => n.id === notificationId)
        if (notification) {
            notification.read_at = new Date().toISOString()
        }
    } catch (error) {
        console.error('Error marking notification as read:', error)
    }
}

const markAllAsRead = async () => {
    loading.value = true
    try {
        await axios.post('/notifications/mark-all-read')
        loadUnreadCount()
        router.reload()
    } catch (error) {
        console.error('Error marking all as read:', error)
    } finally {
        loading.value = false
    }
}

const deleteNotification = async (notificationId) => {
    if (!confirm('Вы уверены, что хотите удалить это уведомление?')) {
        return
    }

    try {
        await axios.delete(`/notifications/${notificationId}`)
        loadUnreadCount()
        router.reload()
    } catch (error) {
        console.error('Error deleting notification:', error)
    }
}

const applyFilters = () => {
    const params = {}
    if (showUnreadOnly.value) params.unread_only = true
    if (selectedType.value) params.type = selectedType.value
    
    router.get('/notifications', params, { preserveState: true })
}

const getNotificationIconClass = (type) => {
    const classes = {
        'appointment_reminder': 'bg-warning text-warning-content',
        'appointment_created': 'bg-success text-success-content',
        'appointment_cancelled': 'bg-error text-error-content',
    }
    return classes[type] || 'bg-primary text-primary-content'
}

const formatTime = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diff = now - date
    
    if (diff < 60000) return 'только что'
    if (diff < 3600000) return `${Math.floor(diff / 60000)} мин назад`
    if (diff < 86400000) return `${Math.floor(diff / 3600000)} ч назад`
    return date.toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
