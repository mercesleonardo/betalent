<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::before(function (User $user, string $ability) {
            return $user->role === UserRole::ADMIN ? true : null;
        });

        Gate::define('manage-users', function (User $user) {
            return $user->role === UserRole::MANAGER;
        });

        Gate::define('manage-products', function (User $user) {
            return in_array($user->role, [UserRole::MANAGER, UserRole::FINANCE]);
        });

        Gate::define('manage-finances', function (User $user) {
            return $user->role === UserRole::FINANCE;
        });

        Model::preventLazyLoading(!app()->isProduction());
    }
}
