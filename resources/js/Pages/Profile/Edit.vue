<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({
    password: '',
});

const showPasswordForm = ref(false);
const showDeleteForm = ref(false);

function updateProfile() {
    form.put(route('profile.update'), {
        preserveScroll: true,
    });
}

function updatePassword() {
    passwordForm.put(route('profile.password'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            showPasswordForm.value = false;
        },
    });
}

function deleteAccount() {
    if (confirm('Вы уверены, что хотите удалить свой аккаунт? Это действие необратимо.')) {
        deleteForm.delete(route('profile.destroy'), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <AppLayout title="Профиль">
        <Head title="Профиль" />

        <div class="max-w-2xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold">Профиль</h1>
                <p class="text-base-content/70 mt-2">Управляйте информацией своего профиля</p>
            </div>

            <!-- Информация профиля -->
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Информация профиля</h2>
                    
                    <form @submit.prevent="updateProfile" class="space-y-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Имя</span>
                            </label>
                            <input 
                                v-model="form.name"
                                type="text" 
                                class="input input-bordered"
                                :class="{ 'input-error': form.errors.name }"
                                required 
                            />
                            <div v-if="form.errors.name" class="label">
                                <span class="label-text-alt text-error">{{ form.errors.name }}</span>
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Email</span>
                            </label>
                            <input 
                                v-model="form.email"
                                type="email" 
                                class="input input-bordered"
                                :class="{ 'input-error': form.errors.email }"
                                required 
                            />
                            <div v-if="form.errors.email" class="label">
                                <span class="label-text-alt text-error">{{ form.errors.email }}</span>
                            </div>
                        </div>

                        <div class="card-actions justify-end">
                            <button 
                                type="submit" 
                                class="btn btn-primary"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                                Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Смена пароля -->
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="card-title text-xl">Смена пароля</h2>
                        <button 
                            @click="showPasswordForm = !showPasswordForm"
                            class="btn btn-outline btn-sm"
                        >
                            {{ showPasswordForm ? 'Отмена' : 'Изменить пароль' }}
                        </button>
                    </div>

                    <form v-if="showPasswordForm" @submit.prevent="updatePassword" class="space-y-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Текущий пароль</span>
                            </label>
                            <input 
                                v-model="passwordForm.current_password"
                                type="password" 
                                class="input input-bordered"
                                :class="{ 'input-error': passwordForm.errors.current_password }"
                                required 
                            />
                            <div v-if="passwordForm.errors.current_password" class="label">
                                <span class="label-text-alt text-error">{{ passwordForm.errors.current_password }}</span>
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Новый пароль</span>
                            </label>
                            <input 
                                v-model="passwordForm.password"
                                type="password" 
                                class="input input-bordered"
                                :class="{ 'input-error': passwordForm.errors.password }"
                                required 
                            />
                            <div v-if="passwordForm.errors.password" class="label">
                                <span class="label-text-alt text-error">{{ passwordForm.errors.password }}</span>
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Подтверждение пароля</span>
                            </label>
                            <input 
                                v-model="passwordForm.password_confirmation"
                                type="password" 
                                class="input input-bordered"
                                :class="{ 'input-error': passwordForm.errors.password_confirmation }"
                                required 
                            />
                            <div v-if="passwordForm.errors.password_confirmation" class="label">
                                <span class="label-text-alt text-error">{{ passwordForm.errors.password_confirmation }}</span>
                            </div>
                        </div>

                        <div class="card-actions justify-end">
                            <button 
                                type="submit" 
                                class="btn btn-primary"
                                :disabled="passwordForm.processing"
                            >
                                <span v-if="passwordForm.processing" class="loading loading-spinner loading-sm"></span>
                                Изменить пароль
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Удаление аккаунта -->
            <div class="card bg-base-100 shadow-xl border-error/20">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="card-title text-xl text-error">Удаление аккаунта</h2>
                            <p class="text-sm text-base-content/70 mt-1">
                                После удаления аккаунта все ваши данные будут безвозвратно удалены.
                            </p>
                        </div>
                        <button 
                            @click="showDeleteForm = !showDeleteForm"
                            class="btn btn-error btn-sm"
                        >
                            {{ showDeleteForm ? 'Отмена' : 'Удалить аккаунт' }}
                        </button>
                    </div>

                    <form v-if="showDeleteForm" @submit.prevent="deleteAccount" class="space-y-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Пароль для подтверждения</span>
                            </label>
                            <input 
                                v-model="deleteForm.password"
                                type="password" 
                                class="input input-bordered input-error"
                                :class="{ 'input-error': deleteForm.errors.password }"
                                required 
                            />
                            <div v-if="deleteForm.errors.password" class="label">
                                <span class="label-text-alt text-error">{{ deleteForm.errors.password }}</span>
                            </div>
                        </div>

                        <div class="card-actions justify-end">
                            <button 
                                type="submit" 
                                class="btn btn-error"
                                :disabled="deleteForm.processing"
                            >
                                <span v-if="deleteForm.processing" class="loading loading-spinner loading-sm"></span>
                                Удалить аккаунт
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>