<template>
    <Head title="Администрирование" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Администрирование
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Специальности -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Специальности
                                </h3>
                                <button 
                                    @click="openSpecialtyModal()"
                                    class="btn btn-primary btn-sm"
                                >
                                    Добавить
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Управление медицинскими специальностями
                            </p>
                        </div>
                    </div>

                    <!-- Врачи -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Врачи
                                </h3>
                                <button 
                                    @click="openDoctorModal()"
                                    class="btn btn-primary btn-sm"
                                >
                                    Добавить
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Управление врачами и их профилями
                            </p>
                        </div>
                    </div>

                    <!-- Пользователи -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Пользователи
                                </h3>
                                <button 
                                    @click="openUsersModal()"
                                    class="btn btn-primary btn-sm"
                                >
                                    Управление
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Управление ролями пользователей
                            </p>
                        </div>
                    </div>

                    <!-- Расписание -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Расписание
                                </h3>
                                <Link 
                                    href="/admin/schedule"
                                    class="btn btn-primary btn-sm"
                                >
                                    Управление
                                </Link>
                            </div>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Управление расписанием врачей
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно для специальностей -->
        <Modal :show="showSpecialtyModal" @close="closeSpecialtyModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    {{ editingSpecialty ? 'Редактировать специальность' : 'Добавить специальность' }}
                </h2>

                <form @submit.prevent="saveSpecialty">
                    <div class="mb-4">
                        <InputLabel for="specialty_name" value="Название" />
                        <TextInput
                            id="specialty_name"
                            v-model="specialtyForm.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError class="mt-2" :message="specialtyForm.errors.name" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="specialty_description" value="Описание" />
                        <textarea
                            id="specialty_description"
                            v-model="specialtyForm.description"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            rows="3"
                        ></textarea>
                        <InputError class="mt-2" :message="specialtyForm.errors.description" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="specialty_image" value="Изображение" />
                        <input
                            id="specialty_image"
                            type="file"
                            @change="handleSpecialtyImageChange"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        <InputError class="mt-2" :message="specialtyForm.errors.image" />
                        
                        <!-- Предпросмотр изображения -->
                        <div v-if="specialtyImagePreview" class="mt-2">
                            <img :src="specialtyImagePreview" alt="Предпросмотр" class="w-32 h-32 object-cover rounded-lg" />
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <SecondaryButton @click="closeSpecialtyModal">
                            Отмена
                        </SecondaryButton>
                        <PrimaryButton :disabled="specialtyForm.processing">
                            {{ editingSpecialty ? 'Обновить' : 'Создать' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Модальное окно для врачей -->
        <Modal :show="showDoctorModal" @close="closeDoctorModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    {{ editingDoctor ? 'Редактировать врача' : 'Добавить врача' }}
                </h2>

                <form @submit.prevent="saveDoctor">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="doctor_name" value="Имя" />
                            <TextInput
                                id="doctor_name"
                                v-model="doctorForm.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="doctorForm.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="doctor_email" value="Email" />
                            <TextInput
                                id="doctor_email"
                                v-model="doctorForm.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="doctorForm.errors.email" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <InputLabel for="doctor_password" value="Пароль" />
                        <TextInput
                            id="doctor_password"
                            v-model="doctorForm.password"
                            type="password"
                            class="mt-1 block w-full"
                            :required="!editingDoctor"
                        />
                        <InputError class="mt-2" :message="doctorForm.errors.password" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel for="doctor_specialty" value="Специальность" />
                            <select
                                id="doctor_specialty"
                                v-model="doctorForm.specialty_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                required
                            >
                                <option value="">Выберите специальность</option>
                                <option v-for="specialty in specialties" :key="specialty.id" :value="specialty.id">
                                    {{ specialty.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="doctorForm.errors.specialty_id" />
                        </div>

                        <div>
                            <InputLabel for="doctor_office" value="Кабинет" />
                            <TextInput
                                id="doctor_office"
                                v-model="doctorForm.office"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="doctorForm.errors.office" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <InputLabel for="doctor_duration" value="Длительность приема (минуты)" />
                        <TextInput
                            id="doctor_duration"
                            v-model="doctorForm.default_duration"
                            type="number"
                            min="15"
                            max="120"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError class="mt-2" :message="doctorForm.errors.default_duration" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="doctor_photo" value="Фото" />
                        <input
                            id="doctor_photo"
                            type="file"
                            @change="handleDoctorImageChange"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        <InputError class="mt-2" :message="doctorForm.errors.photo" />
                        
                        <!-- Предпросмотр изображения -->
                        <div v-if="doctorImagePreview" class="mt-2">
                            <img :src="doctorImagePreview" alt="Предпросмотр" class="w-32 h-32 object-cover rounded-lg" />
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <SecondaryButton @click="closeDoctorModal">
                            Отмена
                        </SecondaryButton>
                        <PrimaryButton :disabled="doctorForm.processing">
                            {{ editingDoctor ? 'Обновить' : 'Создать' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Модальное окно для пользователей -->
        <Modal :show="showUsersModal" @close="closeUsersModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Управление пользователями
                </h2>

                <div class="space-y-4">
                    <div v-for="user in users" :key="user.id" class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h3 class="font-medium">{{ user.name }}</h3>
                            <p class="text-sm text-gray-600">{{ user.email }}</p>
                            <p class="text-xs text-gray-500">
                                Роль: {{ user.roles?.[0]?.name || 'Не назначена' }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <select 
                                :value="user.roles?.[0]?.name || 'patient'"
                                @change="updateUserRole(user.id, $event.target.value)"
                                class="text-sm border rounded px-2 py-1"
                            >
                                <option value="patient">Пациент</option>
                                <option value="doctor">Врач</option>
                                <option value="registrar">Регистратор</option>
                                <option value="admin">Администратор</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <SecondaryButton @click="closeUsersModal">
                        Закрыть
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import axios from 'axios'

// Состояние модальных окон
const showSpecialtyModal = ref(false)
const showDoctorModal = ref(false)
const showUsersModal = ref(false)

// Данные
const specialties = ref([])
const users = ref([])
const editingSpecialty = ref(null)
const editingDoctor = ref(null)

// Предпросмотр изображений
const specialtyImagePreview = ref(null)
const doctorImagePreview = ref(null)

// Формы
const specialtyForm = useForm({
    name: '',
    description: '',
    image: null,
})

const doctorForm = useForm({
    name: '',
    email: '',
    password: '',
    specialty_id: '',
    office: '',
    default_duration: 30,
    photo: null,
})

// Загрузка данных
onMounted(() => {
    loadSpecialties()
    loadUsers()
})

const loadSpecialties = async () => {
    try {
        const response = await axios.get('/admin/api/specialties')
        specialties.value = response.data
    } catch (error) {
        console.error('Ошибка загрузки специальностей:', error)
    }
}

const loadUsers = async () => {
    try {
        const response = await axios.get('/admin/api/users')
        users.value = response.data
    } catch (error) {
        console.error('Ошибка загрузки пользователей:', error)
    }
}

// Управление специальностями
const openSpecialtyModal = (specialty = null) => {
    editingSpecialty.value = specialty
    if (specialty) {
        specialtyForm.name = specialty.name
        specialtyForm.description = specialty.description || ''
    } else {
        specialtyForm.reset()
    }
    specialtyImagePreview.value = null
    showSpecialtyModal.value = true
}

const closeSpecialtyModal = () => {
    showSpecialtyModal.value = false
    specialtyForm.reset()
    specialtyImagePreview.value = null
    editingSpecialty.value = null
}

const handleSpecialtyImageChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        specialtyForm.image = file
        const reader = new FileReader()
        reader.onload = (e) => {
            specialtyImagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const saveSpecialty = () => {
    const formData = new FormData()
    formData.append('name', specialtyForm.name)
    formData.append('description', specialtyForm.description)
    if (specialtyForm.image) {
        formData.append('image', specialtyForm.image)
    }

    const url = editingSpecialty.value 
        ? `/admin/api/specialties/${editingSpecialty.value.id}`
        : '/admin/api/specialties'
    
    const method = editingSpecialty.value ? 'put' : 'post'

    axios[method](url, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then(() => {
        loadSpecialties()
        closeSpecialtyModal()
    })
    .catch(error => {
        if (error.response?.data?.errors) {
            specialtyForm.setError(error.response.data.errors)
        }
    })
}

// Управление врачами
const openDoctorModal = (doctor = null) => {
    editingDoctor.value = doctor
    if (doctor) {
        doctorForm.name = doctor.user.name
        doctorForm.email = doctor.user.email
        doctorForm.specialty_id = doctor.specialty_id
        doctorForm.office = doctor.office
        doctorForm.default_duration = doctor.default_duration
        doctorForm.password = ''
    } else {
        doctorForm.reset()
    }
    doctorImagePreview.value = null
    showDoctorModal.value = true
}

const closeDoctorModal = () => {
    showDoctorModal.value = false
    doctorForm.reset()
    doctorImagePreview.value = null
    editingDoctor.value = null
}

const handleDoctorImageChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        doctorForm.photo = file
        const reader = new FileReader()
        reader.onload = (e) => {
            doctorImagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const saveDoctor = () => {
    const formData = new FormData()
    formData.append('name', doctorForm.name)
    formData.append('email', doctorForm.email)
    if (doctorForm.password) {
        formData.append('password', doctorForm.password)
    }
    formData.append('specialty_id', doctorForm.specialty_id)
    formData.append('office', doctorForm.office)
    formData.append('default_duration', doctorForm.default_duration)
    if (doctorForm.photo) {
        formData.append('photo', doctorForm.photo)
    }

    const url = editingDoctor.value 
        ? `/admin/api/doctors/${editingDoctor.value.id}`
        : '/admin/api/doctors'
    
    const method = editingDoctor.value ? 'put' : 'post'

    axios[method](url, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then(() => {
        loadUsers()
        closeDoctorModal()
    })
    .catch(error => {
        if (error.response?.data?.errors) {
            doctorForm.setError(error.response.data.errors)
        }
    })
}

// Управление пользователями
const openUsersModal = () => {
    showUsersModal.value = true
}

const closeUsersModal = () => {
    showUsersModal.value = false
}

const updateUserRole = async (userId, role) => {
    try {
        await axios.put(`/admin/api/users/${userId}/role`, { role })
        loadUsers()
    } catch (error) {
        console.error('Ошибка обновления роли:', error)
    }
}
</script>
