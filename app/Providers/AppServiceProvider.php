<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\AdminPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        
        // Регистрируем политики
        Gate::define('admin', [AdminPolicy::class, 'admin']);
        Gate::define('manage-specialties', [AdminPolicy::class, 'manageSpecialties']);
        Gate::define('manage-doctors', [AdminPolicy::class, 'manageDoctors']);
        Gate::define('manage-users', [AdminPolicy::class, 'manageUsers']);
    }
}
