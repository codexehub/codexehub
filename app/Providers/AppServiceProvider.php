<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Payments\ManualBkashPaymentProvider;
use App\Services\Payments\PaymentProvider;
use App\Support\Authorization\PermissionCatalog;
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
        $this->app->bind(PaymentProvider::class, ManualBkashPaymentProvider::class);
        $this->app->singleton(AuditLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::anonymousComponentPath(resource_path('views/admin/components'), 'admin');

        // A super-admin passes every gate, even one defined for a permission added
        // after the seeder last ran.
        Gate::before(fn (User $user, string $ability) => $user->hasRole(Role::SUPER_ADMIN) ? true : null);

        // Every ability — control-plane and the folded-in legacy modules — is
        // defined from the catalog and resolved against the user's roles (RBAC-001
        // / RBAC-003). The catalog may list a key before the module that checks it.
        foreach (PermissionCatalog::all() as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermissionTo($permission));
        }

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return route('admin.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });
    }
}
