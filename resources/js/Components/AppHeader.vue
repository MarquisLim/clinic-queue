<!-- resources/js/Components/AppHeader.vue -->
<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import ThemeToggle from "@/Components/ThemeToggle.vue";

const page = usePage();
const user = page.props.auth?.user || null;
</script>

<template>
    <div class="navbar bg-base-100 shadow-sm">
        <!-- left -->
        <div class="navbar-start">
            <div class="dropdown">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                </div>
                <ul
                    tabindex="0"
                    class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow">
                    <li><Link href="/">Homepage</Link></li>
                    <li><Link href="/doctors">Doctors</Link></li>
                    <li><Link href="/specialties">Specialties</Link></li>
                </ul>
            </div>
        </div>

        <!-- center -->
        <div class="navbar-center">
            <Link href="/" class="btn btn-ghost text-xl">ClinicQueue</Link>
        </div>

        <!-- right -->
        <div class="navbar-end gap-2">
            <ThemeToggle />

            <template v-if="!user">
                <Link href="/login" class="btn btn-primary btn-sm">Войти</Link>
            </template>

            <template v-else>
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost normal-case">
                        <span class="mr-2">{{ user.name }}</span>
                        <div class="avatar">
                            <div class="w-8 rounded-full">
                                <img src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp" alt="user" />
                            </div>
                        </div>
                    </div>
                    <ul
                        tabindex="0"
                        class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-48 p-2 shadow">
                        <li><Link href="/appointments/mine">Мои записи</Link></li>
                        
                        <!-- Панель врача -->
                        <li v-if="user?.is_doctor">
                            <Link href="/doctor/panel">Панель врача</Link>
                        </li>
                        
                        <!-- Панель регистратора -->
                        <li v-if="user?.is_registrar">
                            <Link href="/registrar/panel">Панель регистратора</Link>
                        </li>
                        
                        <!-- Админская панель -->
                        <li v-if="user?.is_admin">
                            <Link href="/admin">Админ панель</Link>
                        </li>
                        
                        <!-- Админские страницы -->
                        <li v-if="user?.is_admin"><Link href="/admin/specialties">Специальности</Link></li>
                        <li v-if="user?.is_admin"><Link href="/admin/doctors">Врачи</Link></li>
                        <li v-if="user?.is_admin"><Link href="/admin/users">Пользователи</Link></li>
                        <li v-if="user?.is_admin"><Link href="/admin/schedule">Расписание</Link></li>
                        
                        <li><Link href="/profile">Профиль</Link></li>
                        <li><Link href="/settings">Настройки</Link></li>
                        <li>
                            <Link method="post" href="/logout" as="button">Выйти</Link>
                        </li>
                    </ul>
                </div>
            </template>
        </div>
    </div>
</template>
