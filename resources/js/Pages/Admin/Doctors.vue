<template>
    <Head title="Управление врачами" />

    <AppLayout title="Управление врачами">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Управление врачами</h1>
                <p class="text-gray-600">Добавление и редактирование врачей</p>
            </div>
            <button 
                @click="openModal()"
                class="btn btn-primary"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Добавить врача
            </button>
        </div>

        <div class="flex items-center gap-3 mb-4">
            <input
                v-model="search"
                @input="debouncedSearch"
                type="text"
                placeholder="Поиск по имени или email"
                class="input input-bordered w-full md:w-80"
            />
            <select v-model="selectedSpec" @change="applyFilters" class="select select-bordered">
                <option value="">Все специальности</option>
                <option v-for="spec in specialties" :key="spec.id" :value="spec.id">{{ spec.name }}</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
                v-for="doctor in doctors.data || doctors"
                :key="doctor.id || doctor"
                class="card bg-base-100 shadow-xl"
            >
                <div class="card-body text-center">
                    <div class="avatar mb-4">
                        <div class="w-24 h-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2 overflow-hidden">
                            <img v-if="doctor.photo_url" :src="storageUrl(doctor.photo_url)" :alt="doctor.user?.name || 'Врач'" class="w-full h-full object-cover" />
                            <img v-else-if="doctor.specialty?.image" :src="storageUrl(doctor.specialty.image)" :alt="doctor.specialty?.name || 'Специальность'" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full bg-primary text-primary-content flex items-center justify-center text-2xl font-bold">
                                {{ (doctor.user?.name || 'В').charAt(0).toUpperCase() }}
                            </div>
                        </div>
                    </div>
                    <h2 class="card-title justify-center">{{ doctor.user?.name || 'Без имени' }}</h2>
                    <p class="text-sm text-gray-600 flex items-center justify-center gap-2">
                        <img v-if="doctor.specialty?.image" :src="storageUrl(doctor.specialty.image)" alt="" class="w-5 h-5 rounded" />
                        <span>{{ doctor.specialty?.name || 'Специальность не указана' }}</span>
                    </p>
                    <div class="space-y-2">
                        <div class="flex items-center text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Кабинет: {{ doctor.room || 'Не указан' }}
                        </div>
                        <div class="flex items-center text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Длительность: {{ doctor.avg_duration_min || 30 }} мин
                        </div>
                        <div class="flex items-center text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ doctor.user?.email || 'Email не указан' }}
                        </div>
                    </div>
                    <div class="card-actions justify-end mt-4">
                        <button 
                            @click="editDoctor(doctor)"
                            class="btn btn-sm btn-outline"
                        >
                            Редактировать
                        </button>
                        <button 
                            @click="deleteDoctor(doctor)"
                            class="btn btn-sm btn-error"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="(doctors.data ? doctors.data.length : doctors.length) === 0" class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Нет врачей</h3>
            <p class="mt-1 text-sm text-gray-500">Начните с добавления первого врача.</p>
            <div class="mt-6">
                <button 
                    @click="openModal()"
                    class="btn btn-primary"
                >
                    Добавить врача
                </button>
            </div>
        </div>

        <div class="modal" :class="{ 'modal-open': showModal }">
            <div class="modal-box max-w-2xl">
                <h3 class="font-bold text-lg mb-4">
                    {{ editingDoctor ? 'Редактировать врача' : 'Добавить врача' }}
                </h3>

                <form @submit.prevent="saveDoctor">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Имя</span>
                            </label>
                            <input 
                                type="text" 
                                v-model="form.name"
                                class="input input-bordered w-full"
                                placeholder="Введите имя врача"
                                required
                            />
                            <div v-if="form.errors.name" class="label">
                                <span class="label-text-alt text-error">{{ form.errors.name }}</span>
                            </div>
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Email</span>
                            </label>
                            <input 
                                type="email" 
                                v-model="form.email"
                                class="input input-bordered w-full"
                                placeholder="email@example.com"
                                required
                            />
                            <div v-if="form.errors.email" class="label">
                                <span class="label-text-alt text-error">{{ form.errors.email }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Пароль</span>
                        </label>
                        <input 
                            type="password" 
                            v-model="form.password"
                            class="input input-bordered w-full"
                            :placeholder="editingDoctor ? 'Оставьте пустым, чтобы не менять' : 'Введите пароль'"
                            :required="!editingDoctor"
                        />
                        <div v-if="form.errors.password" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.password }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Специальность</span>
                            </label>
                            <select 
                                v-model="form.specialty_id"
                                class="select select-bordered w-full"
                                required
                            >
                                <option value="">Выберите специальность</option>
                                <option v-for="specialty in specialties" :key="specialty.id" :value="specialty.id">
                                    {{ specialty.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.specialty_id" class="label">
                                <span class="label-text-alt text-error">{{ form.errors.specialty_id }}</span>
                            </div>
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Кабинет</span>
                            </label>
                            <input 
                                type="text" 
                                v-model="form.office"
                                class="input input-bordered w-full"
                                placeholder="Номер кабинета"
                                required
                            />
                            <div v-if="form.errors.office" class="label">
                                <span class="label-text-alt text-error">{{ form.errors.office }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Длительность приема (минуты)</span>
                        </label>
                        <input 
                            type="number" 
                            v-model="form.default_duration"
                            class="input input-bordered w-full"
                            min="15"
                            max="120"
                            required
                        />
                        <div v-if="form.errors.default_duration" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.default_duration }}</span>
                        </div>
                    </div>

                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Фото</span>
                        </label>
                        <input 
                            type="file" 
                            @change="handleImageChange"
                            accept="image/*"
                            class="file-input file-input-bordered w-full"
                        />
                        <div v-if="form.errors.photo" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.photo }}</span>
                        </div>
                        
                        <div v-if="imagePreview" class="mt-2">
                            <img :src="imagePreview" alt="Предпросмотр" class="w-32 h-32 object-cover rounded-lg" />
                        </div>
                    </div>

                    <div class="modal-action">
                        <button 
                            type="button"
                            @click="closeModal"
                            class="btn btn-ghost"
                        >
                            Отмена
                        </button>
                        <button 
                            type="submit"
                            class="btn btn-primary"
                            :disabled="form.processing"
                        >
                            {{ editingDoctor ? 'Обновить' : 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <Pagination v-if="doctors.links" :links="doctors.links" />
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
    doctors: { type: [Array, Object], required: true },
    specialties: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
})

const showModal = ref(false)
const editingDoctor = ref(null)
const imagePreview = ref(null)
const search = ref(props.filters.search || '')
const selectedSpec = ref(props.filters.specialty_id || '')

const form = useForm({
    name: '',
    email: '',
    password: '',
    specialty_id: '',
    office: '',
    default_duration: 30,
    photo: null,
})

const openModal = (doctor = null) => {
    editingDoctor.value = doctor
    if (doctor) {
        form.name = doctor.user?.name || ''
        form.email = doctor.user?.email || ''
        form.specialty_id = doctor.speciality_id || ''
        form.office = doctor.room || ''
        form.default_duration = doctor.avg_duration_min || 30
        form.password = ''
        
        imagePreview.value = `/storage/${doctor.photo_url}`
    } else {
        form.reset()
    }
    if (!doctor) {
        imagePreview.value = null
    }
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    form.reset()
    imagePreview.value = null
    editingDoctor.value = null
}

const handleImageChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.photo = file
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const saveDoctor = () => {
    const url = editingDoctor.value 
        ? route('admin.doctors.update', editingDoctor.value.id)
        : route('admin.doctors.store')
    
    const method = editingDoctor.value ? 'put' : 'post'

    form[method](url)
}

const editDoctor = (doctor) => {
    openModal(doctor)
}

const deleteDoctor = async (doctor) => {
    if (!confirm('Вы уверены, что хотите удалить этого врача?')) {
        return
    }

    form.delete(route('admin.doctors.destroy', doctor.id))
}

const storageUrl = (path) => `/storage/${path}`

let debounceTimer
const debouncedSearch = () => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(applyFilters, 300)
}

const applyFilters = () => {
    router.get(route('admin.doctors'), { search: search.value || undefined, specialty_id: selectedSpec.value || undefined }, { preserveScroll: true, preserveState: true, replace: true })
}
</script>
