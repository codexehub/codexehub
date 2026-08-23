<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Blade;
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
        Blade::anonymousComponentPath(resource_path('views/admin/components'), 'admin');

        // Module abilities. These resolve through the admin role for now; when the
        // Role & Permission module lands they can be re-pointed at real permission
        // records without touching the controllers that already check them.
        foreach (['view', 'create', 'edit', 'delete'] as $ability) {
            Gate::define("product.{$ability}", fn (User $user) => $user->isAdmin());
            Gate::define("team.{$ability}", fn (User $user) => $user->isAdmin());
        }

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return route('admin.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });
    }
}
