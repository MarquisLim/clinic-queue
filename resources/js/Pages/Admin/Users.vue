<template>
    <Head title="Управление пользователями" />

    <AppLayout title="Управление пользователями">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">Управление пользователями</h1>
            <p class="text-gray-600">Управление ролями и правами пользователей</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Пациенты</div>
                    <div class="stat-value text-primary">{{ roleStats.patient }}</div>
                </div>
            </div>

            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Врачи</div>
                    <div class="stat-value text-secondary">{{ roleStats.doctor }}</div>
                </div>
            </div>

            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Регистраторы</div>
                    <div class="stat-value text-accent">{{ roleStats.registrar }}</div>
                </div>
            </div>

            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-figure text-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Администраторы</div>
                    <div class="stat-value text-info">{{ roleStats.admin }}</div>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title mb-4">Список пользователей</h2>
                <div class="flex flex-col md:flex-row md:items-center gap-3 mb-4">
                    <input v-model="search" @input="debouncedSearch" type="text" placeholder="Поиск по имени или email" class="input input-bordered w-full md:w-80" />
                    <select v-model="selectedRole" @change="applyFilters" class="select select-bordered w-full md:w-60">
                        <option value="">Все роли</option>
                        <option v-for="role in availableRoles" :key="role" :value="role">{{ getRoleText(role) }}</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Роли</th>
                                <th>Специальность</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data || users" :key="user.id || user">
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <div class="avatar">
                                            <div class="mask mask-squircle w-12 h-12 overflow-hidden">
                                                <img v-if="user.doctor?.photo_url" :src="storageUrl(user.doctor.photo_url)" :alt="user.name" class="w-full h-full object-cover" />
                                                <img v-else-if="user.doctor?.specialty?.image" :src="storageUrl(user.doctor.specialty.image)" :alt="user.doctor?.specialty?.name" class="w-full h-full object-cover" />
                                                <div v-else class="bg-primary text-primary-content flex items-center justify-center text-lg font-bold w-full h-full">
                                                    {{ user.name.charAt(0).toUpperCase() }}
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold">{{ user.name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ user.email }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        <div 
                                            v-for="role in user.roles" 
                                            :key="role.id"
                                            class="badge"
                                            :class="getRoleBadgeClass(role.name)"
                                        >
                                            {{ getRoleText(role.name) }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span v-if="user.doctor" class="text-sm">
                                        {{ user.doctor.specialty?.name || 'Не указана' }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td>
                                    <div class="dropdown dropdown-end">
                                        <div tabindex="0" role="button" class="btn btn-ghost btn-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </div>
                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                            <li v-for="role in availableRoles" :key="role">
                                                <button 
                                                    @click="updateUserRole(user, role)"
                                                    :class="{ 'active': user.roles.some(r => r.name === role) }"
                                                >
                                                    {{ getRoleText(role) }}
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <Pagination v-if="users.links" :links="users.links" />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    users: { type: [Array, Object], required: true },
    filters: { type: Object, default: () => ({}) }
})

const form = useForm({
    role: ''
})

const availableRoles = ['patient', 'doctor', 'registrar', 'admin']
const search = ref(props.filters.search || '')
const selectedRole = ref(props.filters.role || '')

const roleStats = computed(() => {
    const stats = { patient: 0, doctor: 0, registrar: 0, admin: 0 }
    (props.users.data || props.users).forEach(user => {
        user.roles.forEach(role => {
            if (stats.hasOwnProperty(role.name)) {
                stats[role.name]++
            }
        })
    })
    return stats
})

const getRoleBadgeClass = (role) => {
    const classes = {
        'patient': 'badge-primary',
        'doctor': 'badge-secondary',
        'registrar': 'badge-accent',
        'admin': 'badge-info'
    }
    return classes[role] || 'badge-neutral'
}

const getRoleText = (role) => {
    const texts = {
        'patient': 'Пациент',
        'doctor': 'Врач',
        'registrar': 'Регистратор',
        'admin': 'Администратор'
    }
    return texts[role] || role
}

const updateUserRole = (user, role) => {
    form.role = role
    form.put(route('admin.users.update-role', user.id))
}

const storageUrl = (path) => `/storage/${path}`

let debounceTimer
const debouncedSearch = () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(applyFilters, 300)
}

const applyFilters = () => {
    router.get(route('admin.users'), { search: search.value || undefined, role: selectedRole.value || undefined }, { preserveScroll: true, preserveState: true, replace: true })
}
</script>