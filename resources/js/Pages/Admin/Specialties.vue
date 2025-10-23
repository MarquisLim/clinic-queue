<template>
    <Head title="Управление специальностями" />

    <AppLayout title="Управление специальностями">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Управление специальностями</h1>
                <p class="text-gray-600">Добавление и редактирование медицинских специальностей</p>
            </div>
            <button 
                @click="openModal()"
                class="btn btn-primary"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Добавить специальность
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
                v-for="specialty in specialties" 
                :key="specialty.id"
                class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300"
            >
                <!-- Изображение специальности -->
                <figure class="relative">
                    <div v-if="specialty.image_url" class="w-full h-48 overflow-hidden">
                        <img 
                            :src="`/storage/${specialty.image_url}`" 
                            :alt="specialty.name" 
                            class="w-full h-full object-cover transition-transform duration-300 hover:scale-105" 
                        />
                    </div>
                    <div v-else class="w-full h-48 bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-primary/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <!-- Бейдж с количеством врачей -->
                    <div class="absolute top-4 right-4">
                        <div class="badge badge-primary badge-lg shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ specialty.doctors_count }} врачей
                        </div>
                    </div>
                </figure>

                <div class="card-body p-6">
                    <h2 class="card-title text-xl mb-2">
                        {{ specialty.name }}
                    </h2>
                    
                    <p v-if="specialty.description" class="text-sm text-gray-600 mb-4 line-clamp-3">
                        {{ specialty.description }}
                    </p>
                    
                    <div v-else class="text-sm text-gray-400 mb-4 italic">
                        Описание не указано
                    </div>

                    <!-- Действия -->
                    <div class="card-actions justify-end">
                        <button 
                            @click="editSpecialty(specialty)"
                            class="btn btn-sm btn-outline btn-primary"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Редактировать
                        </button>
                        <button 
                            @click="deleteSpecialty(specialty)"
                            class="btn btn-sm btn-error"
                            :disabled="specialty.doctors_count > 0"
                            :class="{ 'opacity-50 cursor-not-allowed': specialty.doctors_count > 0 }"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Удалить
                        </button>
                    </div>

                    <!-- Предупреждение о невозможности удаления -->
                    <div v-if="specialty.doctors_count > 0" class="alert alert-warning mt-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <span class="text-xs">Нельзя удалить специальность с привязанными врачами</span>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="specialties.length === 0" class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Нет специальностей</h3>
            <p class="mt-1 text-sm text-gray-500">Начните с добавления первой специальности.</p>
            <div class="mt-6">
                <button 
                    @click="openModal()"
                    class="btn btn-primary"
                >
                    Добавить специальность
                </button>
            </div>
        </div>

        <div class="modal" :class="{ 'modal-open': showModal }">
            <div class="modal-box max-w-2xl w-full mx-4">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ editingSpecialty ? 'Редактировать специальность' : 'Добавить специальность' }}
                </h3>

                <form @submit.prevent="saveSpecialty" class="space-y-4">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">Название</span>
                        </label>
                        <input 
                            type="text" 
                            v-model="form.name"
                            class="input input-bordered w-full"
                            placeholder="Введите название специальности"
                            required
                        />
                        <div class="label">
                            <span class="label-text-alt">Название медицинской специальности</span>
                        </div>
                        <div v-if="form.errors.name" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.name }}</span>
                        </div>
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">Описание</span>
                        </label>
                        <textarea 
                            v-model="form.description"
                            class="textarea textarea-bordered h-24"
                            placeholder="Описание специальности"
                        ></textarea>
                        <div class="label">
                            <span class="label-text-alt">Подробное описание специальности</span>
                        </div>
                        <div v-if="form.errors.description" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.description }}</span>
                        </div>
                    </div>

                    <!-- Изображение с предпросмотром -->
                    <div class="card bg-base-200 p-4">
                        <h4 class="font-semibold mb-3">Изображение специальности</h4>
                        
                        <!-- Предпросмотр изображения -->
                        <div v-if="imagePreview || (editingSpecialty && editingSpecialty.image_url)" class="mb-4">
                            <div class="flex items-center gap-4">
                                <div class="avatar">
                                    <div class="w-20 h-20 rounded-lg overflow-hidden">
                                        <img 
                                            :src="imagePreview || `/storage/${editingSpecialty.image_url}`" 
                                            :alt="form.name || 'Предпросмотр'" 
                                            class="w-full h-full object-cover" 
                                        />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm text-gray-600">
                                        <div class="font-semibold">Предпросмотр изображения</div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Рекомендуемый размер: 400x300px
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Загрузка файла -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text text-sm">Выберите изображение</span>
                            </label>
                            <input 
                                type="file" 
                                @change="handleImageChange"
                                accept="image/*"
                                class="file-input file-input-bordered w-full"
                            />
                            <div class="label">
                                <span class="label-text-alt">Поддерживаемые форматы: JPG, PNG, GIF (макс. 2MB)</span>
                            </div>
                            <div v-if="form.errors.image" class="label">
                                <span class="label-text-alt text-error">{{ form.errors.image }}</span>
                            </div>
                        </div>

                        <!-- Кнопка удаления изображения -->
                        <div v-if="editingSpecialty && editingSpecialty.image_url && !imagePreview" class="mt-2">
                            <button 
                                type="button"
                                @click="removeImage"
                                class="btn btn-sm btn-error"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Удалить изображение
                            </button>
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
                            <svg v-if="form.processing" class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ editingSpecialty ? 'Обновить' : 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    specialties: Array
})

const showModal = ref(false)
const editingSpecialty = ref(null)
const imagePreview = ref(null)

const form = useForm({
    name: '',
    description: '',
    image: null,
})

const openModal = (specialty = null) => {
    editingSpecialty.value = specialty
    if (specialty) {
        form.name = specialty.name
        form.description = specialty.description || ''
    } else {
        form.reset()
    }
    imagePreview.value = null
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    form.reset()
    imagePreview.value = null
    editingSpecialty.value = null
}

const handleImageChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.image = file
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const removeImage = () => {
    form.image = null
    imagePreview.value = null
    // Очищаем input file
    const fileInput = document.querySelector('input[type="file"]')
    if (fileInput) {
        fileInput.value = ''
    }
}

const saveSpecialty = () => {
    // Защита от множественных отправок
    if (form.processing) {
        return
    }

    const url = editingSpecialty.value 
        ? route('admin.specialties.update', editingSpecialty.value.id)
        : route('admin.specialties.store')
    
    const method = editingSpecialty.value ? 'put' : 'post'

    // Проверяем, что все обязательные поля заполнены
    if (!form.name) {
        return
    }

    // Подготавливаем данные для отправки
    const data = {
        name: form.name,
        description: form.description,
    }
    
    // Добавляем изображение только если оно выбрано
    if (form.image) {
        data.image = form.image
    }
    
    // Создаем FormData для правильной отправки файлов
    const formData = new FormData()
    formData.append('name', data.name)
    formData.append('description', data.description)
    
    if (data.image) {
        formData.append('image', data.image)
    }
    
    // Добавляем метод для PUT запроса
    if (method === 'put') {
        formData.append('_method', 'PUT')
    }
    
    // Добавляем CSRF токен
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (csrfToken) {
        formData.append('_token', csrfToken)
    }
    
    // Отправляем данные через fetch
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        if (response.ok) {
            closeModal()
            // Перезагружаем страницу для обновления данных
            window.location.reload()
        } else {
            throw new Error('Network response was not ok')
        }
    })
    .catch(error => {
        console.error('Error:', error)
    })
}

const editSpecialty = (specialty) => {
    openModal(specialty)
}

const deleteSpecialty = async (specialty) => {
    if (specialty.doctors_count > 0) {
        alert('Нельзя удалить специальность, к которой привязаны врачи')
        return
    }

    if (!confirm('Вы уверены, что хотите удалить эту специальность?')) {
        return
    }

    form.delete(route('admin.specialties.destroy', specialty.id))
}
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
