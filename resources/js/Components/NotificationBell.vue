<template>
    <div class="relative">
        <!-- Иконка уведомлений -->
        <button 
            @click="toggleDropdown"
            class="btn btn-ghost btn-circle relative"
            :class="{ 'btn-primary': hasUnread }"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            
            <!-- Счетчик непрочитанных -->
            <span 
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 bg-error text-error-content text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown с уведомлениями -->
        <div 
            v-if="showDropdown"
            class="absolute right-0 mt-2 w-80 bg-base-100 rounded-lg shadow-xl border border-base-300 z-50"
            style="max-width: calc(100vw - 2rem);"
        >
            <div class="p-4 border-b border-base-300">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-lg">Уведомления</h3>
                    <div class="flex gap-1 flex-wrap">
                        <button 
                            v-if="unreadCount > 0"
                            @click="markAllAsRead"
                            class="btn btn-xs btn-outline whitespace-nowrap"
                            :disabled="loading"
                        >
                            Отметить все
                        </button>
                        <button @click="viewAll" class="btn btn-xs btn-primary whitespace-nowrap">
                            Все
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-h-96 overflow-y-auto">
                <!-- Загрузка -->
                <div v-if="loading" class="p-4 text-center">
                    <span class="loading loading-spinner loading-sm"></span>
                    <span class="ml-2">Загрузка...</span>
                </div>

                <!-- Список уведомлений -->
                <div v-else-if="notifications.length > 0" class="divide-y divide-base-300">
                    <div 
                        v-for="notification in notifications" 
                        :key="notification.id"
                        class="p-4 hover:bg-base-200 transition-colors"
                        :class="{ 'bg-primary/10': !notification.read_at }"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-medium text-sm">{{ notification.data.message }}</span>
                                    <span 
                                        v-if="!notification.read_at"
                                        class="w-2 h-2 bg-primary rounded-full"
                                    ></span>
                                </div>
                                <div class="text-xs text-base-content/70">
                                    {{ formatTime(notification.created_at) }}
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button 
                                    v-if="!notification.read_at"
                                    @click="markAsRead(notification.id)"
                                    class="btn btn-xs btn-ghost"
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

                <!-- Пустое состояние -->
                <div v-else class="p-8 text-center text-base-content/70">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p>Нет уведомлений</p>
                </div>
            </div>
        </div>

        <!-- Overlay для закрытия dropdown -->
        <div 
            v-if="showDropdown"
            @click="closeDropdown"
            class="fixed inset-0 z-40"
        ></div>

        <!-- Toast уведомления -->
        <Toast
            v-for="toast in toasts"
            :key="toast.id"
            :message="toast.message"
            :type="toast.type"
            @close="removeToast(toast.id)"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import Toast from './Toast.vue'

const showDropdown = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
const loading = ref(false)

const hasUnread = computed(() => unreadCount.value > 0)

let echo = null

onMounted(() => {
    loadUnreadCount()
    loadRecentNotifications()
    
    // Setup real-time notifications
    console.log('Setting up Echo...', { 
        hasEcho: !!window.Echo, 
        userId: window.Laravel?.user?.id,
        user: window.Laravel?.user 
    })
    
    if (window.Echo) {
        echo = window.Echo
        
            // Listen to user notifications channel
        const userId = window.Laravel?.user?.id || null
        if (userId) {
            console.log(`Subscribing to notifications.${userId} channel`)
            
            echo.channel(`notifications.${userId}`)
                .listen('.appointment.reminder', (e) => {
                    console.log('🔔 New reminder notification received:', e)
                    
                    // Show toast notification
                    showToast(e.message || 'Новое напоминание о записи', 'info')
                    
                    // Update unread counter
                    unreadCount.value++
                    console.log('📊 Updated unread count:', unreadCount.value)
                    
                    // Update notifications list
                    loadRecentNotifications()
                    
                    // Additionally update counter from server for accuracy
                    setTimeout(() => {
                        console.log('🔄 Refreshing unread count from server...')
                        loadUnreadCount()
                    }, 500)
                })
                .error((error) => {
                    console.error('❌ Echo channel error:', error)
                })
        } else {
            console.warn('⚠️ No user ID found for notifications channel')
        }
    } else {
        console.warn('⚠️ Echo not available')
    }
})

onUnmounted(() => {
    if (echo) {
        const userId = window.Laravel?.user?.id || null
        if (userId) {
            echo.leave(`notifications.${userId}`)
        }
    }
})

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value
    if (showDropdown.value) {
        loadRecentNotifications()
    }
}

const closeDropdown = () => {
    showDropdown.value = false
}

const loadUnreadCount = async () => {
    try {
        const response = await axios.get('/notifications/unread-count')
        unreadCount.value = response.data.count
    } catch (error) {
        console.error('Error loading unread count:', error)
    }
}

const loadRecentNotifications = async () => {
    loading.value = true
    try {
        const response = await axios.get('/notifications/recent')
        notifications.value = response.data
    } catch (error) {
        console.error('Error loading notifications:', error)
    } finally {
        loading.value = false
    }
}

const markAsRead = async (notificationId) => {
    try {
        await axios.post(`/notifications/${notificationId}/read`)
        
        // Update counter locally
        if (unreadCount.value > 0) {
            unreadCount.value--
        }
        
        // Update notifications list
        loadRecentNotifications()
        
        // Additionally update counter from server
        setTimeout(() => {
            loadUnreadCount()
        }, 200)
    } catch (error) {
        console.error('Error marking notification as read:', error)
    }
}

const markAllAsRead = async () => {
    try {
        await axios.post('/notifications/mark-all-read')
        
        // Update counter locally
        unreadCount.value = 0
        
        // Update notifications list
        loadRecentNotifications()
        
        // Additionally update counter from server
        setTimeout(() => {
            loadUnreadCount()
        }, 200)
    } catch (error) {
        console.error('Error marking all as read:', error)
    }
}

const deleteNotification = async (notificationId) => {
    try {
        await axios.delete(`/notifications/${notificationId}`)
        
        // Update counter locally (if notification was unread)
        const notification = notifications.value.find(n => n.id === notificationId)
        if (notification && !notification.read_at && unreadCount.value > 0) {
            unreadCount.value--
        }
        
        // Update notifications list
        loadRecentNotifications()
        
        // Additionally update counter from server
        setTimeout(() => {
            loadUnreadCount()
        }, 200)
    } catch (error) {
        console.error('Error deleting notification:', error)
    }
}

const viewAll = () => {
    router.visit('/notifications')
    closeDropdown()
}

// Toast уведомления уже объявлены выше

const formatTime = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diff = now - date
    
    if (diff < 60000) return 'только что'
    if (diff < 3600000) return `${Math.floor(diff / 60000)} мин назад`
    if (diff < 86400000) return `${Math.floor(diff / 3600000)} ч назад`
    return date.toLocaleDateString('ru-RU')
}

const toasts = ref([])

const showToast = (message, type = 'info') => {
    const id = Date.now()
    toasts.value.push({
        id,
        message,
        type
    })
    
    // Автоматически удаляем toast через 5 секунд
    setTimeout(() => {
        removeToast(id)
    }, 5000)
}

const removeToast = (id) => {
    toasts.value = toasts.value.filter(toast => toast.id !== id)
}
</script>
