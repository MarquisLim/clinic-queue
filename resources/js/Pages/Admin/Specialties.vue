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
                class="card bg-base-100 shadow-xl"
            >
                <figure v-if="specialty.image_url">
                    <img :src="`/storage/${specialty.image_url}`" :alt="specialty.name" class="w-full h-48 object-cover" />
                </figure>
                <div class="card-body">
                    <h2 class="card-title">{{ specialty.name }}</h2>
                    <p v-if="specialty.description" class="text-sm text-gray-600">
                        {{ specialty.description }}
                    </p>
                    <div class="flex items-center justify-between mt-4">
                        <div class="badge badge-primary">
                            {{ specialty.doctors_count }} врачей
                        </div>
                        <div class="card-actions">
                            <button 
                                @click="editSpecialty(specialty)"
                                class="btn btn-sm btn-outline"
                            >
                                Редактировать
                            </button>
                            <button 
                                @click="deleteSpecialty(specialty)"
                                class="btn btn-sm btn-error"
                                :disabled="specialty.doctors_count > 0"
                            >
                                Удалить
                            </button>
                        </div>
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
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">
                    {{ editingSpecialty ? 'Редактировать специальность' : 'Добавить специальность' }}
                </h3>

                <form @submit.prevent="saveSpecialty">
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Название</span>
                        </label>
                        <input 
                            type="text" 
                            v-model="form.name"
                            class="input input-bordered w-full"
                            placeholder="Введите название специальности"
                            required
                        />
                        <div v-if="form.errors.name" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.name }}</span>
                        </div>
                    </div>

                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Описание</span>
                        </label>
                        <textarea 
                            v-model="form.description"
                            class="textarea textarea-bordered h-24"
                            placeholder="Описание специальности"
                        ></textarea>
                        <div v-if="form.errors.description" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.description }}</span>
                        </div>
                    </div>

                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Изображение</span>
                        </label>
                        <input 
                            type="file" 
                            @change="handleImageChange"
                            accept="image/*"
                            class="file-input file-input-bordered w-full"
                        />
                        <div v-if="form.errors.image" class="label">
                            <span class="label-text-alt text-error">{{ form.errors.image }}</span>
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

const saveSpecialty = () => {
    const url = editingSpecialty.value 
        ? route('admin.specialties.update', editingSpecialty.value.id)
        : route('admin.specialties.store')
    
    const method = editingSpecialty.value ? 'put' : 'post'

    form[method](url)
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
