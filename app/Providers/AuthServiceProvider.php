<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Form;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Users
        Gate::define('users:list', function (User $user) {
            return $user->role === 'admin';
        });

        // Forms
        Gate::define('forms:create', function (User $user) {
            return $user ?? false;
        });

        // Forms
        Gate::define('forms:update', function (User $user, Form $form) {
            return $user->public_id === $form->user_id;
        });

        Gate::define('forms:delete', function (User $user, Form $form) {
            return $user->public_id === $form->user_id;
        });
    }
}
