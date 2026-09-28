<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\ConsultationController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\Licensing\LicenseController as LicensingLicenseController;
use App\Http\Controllers\Admin\Licensing\LicensedAppController;
use App\Http\Controllers\Admin\Licensing\LicensedAppPackageController;
use App\Http\Controllers\Admin\Licensing\LicenseTransactionController;
use App\Http\Controllers\Admin\PlaceholderController;
use App\Http\Controllers\Admin\PortfolioCategoryController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\PortfolioSettingController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);

        Route::get('password/reset', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('password/reset', [PasswordResetLinkController::class, 'store'])->name('password.email');

        Route::get('password/reset/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('password/reset/confirm', [NewPasswordController::class, 'store'])->name('password.update');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::prefix('consultations')->name('consultations.')->group(function () {
            Route::get('/', [ConsultationController::class, 'index'])->name('index');
            Route::get('{consultation}/edit', [ConsultationController::class, 'edit'])->name('edit');
            Route::put('{consultation}', [ConsultationController::class, 'update'])->name('update');
            Route::delete('{consultation}', [ConsultationController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('{role}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('{role}', [RoleController::class, 'update'])->name('update');
        });

        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');

            // Wildcard {customer} routes last so they don't swallow the static 'create' path.
            Route::get('{customer}', [CustomerController::class, 'show'])->name('show');
            Route::get('{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('{customer}', [CustomerController::class, 'destroy'])->name('destroy');
            Route::patch('{customer}/status', [CustomerController::class, 'updateStatus'])->name('status');
        });

        Route::prefix('team')->name('team.')->group(function () {
            Route::get('/', [TeamController::class, 'index'])->name('index');
            Route::get('create', [TeamController::class, 'create'])->name('create');
            Route::post('/', [TeamController::class, 'store'])->name('store');
            Route::delete('images/{image}', [TeamController::class, 'deleteImage'])->name('images.destroy');

            // Wildcard {team} routes must come last so they don't swallow the
            // static 'create' and 'images' paths above.
            Route::get('{team}/edit', [TeamController::class, 'edit'])->name('edit');
            Route::put('{team}', [TeamController::class, 'update'])->name('update');
            Route::delete('{team}', [TeamController::class, 'destroy'])->name('destroy');
            Route::patch('{team}/status', [TeamController::class, 'updateStatus'])->name('status');
        });

        Route::prefix('features')->name('features.')->group(function () {
            Route::get('/', [FeatureController::class, 'index'])->name('index');
            Route::get('create', [FeatureController::class, 'create'])->name('create');
            Route::post('/', [FeatureController::class, 'store'])->name('store');

            // Wildcard {feature} routes last so they don't swallow the static 'create' path.
            Route::get('{feature}/edit', [FeatureController::class, 'edit'])->name('edit');
            Route::put('{feature}', [FeatureController::class, 'update'])->name('update');
            Route::delete('{feature}', [FeatureController::class, 'destroy'])->name('destroy');
            Route::patch('{feature}/status', [FeatureController::class, 'updateStatus'])->name('status');
        });

        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', [ServiceController::class, 'index'])->name('index');
            Route::get('create', [ServiceController::class, 'create'])->name('create');
            Route::post('/', [ServiceController::class, 'store'])->name('store');
            Route::get('{service}/edit', [ServiceController::class, 'edit'])->name('edit');
            Route::put('{service}', [ServiceController::class, 'update'])->name('update');
            Route::delete('{service}', [ServiceController::class, 'destroy'])->name('destroy');
            Route::patch('{service}/status', [ServiceController::class, 'updateStatus'])->name('status');
            Route::patch('{service}/featured', [ServiceController::class, 'toggleFeatured'])->name('featured');
        });

        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::delete('images/{image}', [ProductController::class, 'deleteImage'])->name('images.destroy');

            Route::prefix('categories')->name('categories.')->group(function () {
                Route::get('/', [ProductCategoryController::class, 'index'])->name('index');
                Route::post('/', [ProductCategoryController::class, 'store'])->name('store');
                Route::get('{category}/edit', [ProductCategoryController::class, 'edit'])->name('edit');
                Route::put('{category}', [ProductCategoryController::class, 'update'])->name('update');
                Route::delete('{category}', [ProductCategoryController::class, 'destroy'])->name('destroy');
            });

            // Wildcard {product} routes must come last so they don't swallow the
            // static 'create', 'images' and 'categories' paths above.
            Route::get('{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('{product}', [ProductController::class, 'destroy'])->name('destroy');
            Route::patch('{product}/status', [ProductController::class, 'updateStatus'])->name('status');
            Route::patch('{product}/featured', [ProductController::class, 'toggleFeatured'])->name('featured');
        });

        Route::prefix('portfolio')->name('portfolio.')->group(function () {
            Route::get('/', [PortfolioController::class, 'index'])->name('index');
            Route::get('create', [PortfolioController::class, 'create'])->name('create');
            Route::post('/', [PortfolioController::class, 'store'])->name('store');
            Route::delete('images/{image}', [PortfolioController::class, 'deleteImage'])->name('images.destroy');
            Route::post('bulk', [PortfolioController::class, 'bulk'])->name('bulk');

            Route::prefix('categories')->name('categories.')->group(function () {
                Route::get('/', [PortfolioCategoryController::class, 'index'])->name('index');
                Route::post('/', [PortfolioCategoryController::class, 'store'])->name('store');
                Route::get('{category}/edit', [PortfolioCategoryController::class, 'edit'])->name('edit');
                Route::put('{category}', [PortfolioCategoryController::class, 'update'])->name('update');
                Route::delete('{category}', [PortfolioCategoryController::class, 'destroy'])->name('destroy');
            });

            Route::get('settings/edit', [PortfolioSettingController::class, 'edit'])->name('settings');
            Route::put('settings', [PortfolioSettingController::class, 'update'])->name('settings.update');

            // Wildcard {portfolio} routes must come last so they don't swallow the static
            // 'categories' and 'settings' paths above.
            Route::get('{portfolio}/edit', [PortfolioController::class, 'edit'])->name('edit');
            Route::put('{portfolio}', [PortfolioController::class, 'update'])->name('update');
            Route::delete('{portfolio}', [PortfolioController::class, 'destroy'])->name('destroy');
            Route::post('{portfolio}/duplicate', [PortfolioController::class, 'duplicate'])->name('duplicate');
            Route::patch('{portfolio}/status', [PortfolioController::class, 'updateStatus'])->name('status');
            Route::patch('{portfolio}/featured', [PortfolioController::class, 'toggleFeatured'])->name('featured');
        });

        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit.index');

        Route::get('blog', [PlaceholderController::class, 'blog'])->name('blog');
        Route::get('settings', [PlaceholderController::class, 'settings'])->name('settings');
        Route::get('reports', [PlaceholderController::class, 'reports'])->name('reports');

        Route::prefix('licensing')->name('licensing.')->middleware('can:licensing.view')->group(function () {
            Route::resource('apps', LicensedAppController::class)->except('show');
            Route::resource('packages', LicensedAppPackageController::class)->except('show');
            Route::resource('licenses', LicensingLicenseController::class)->except('destroy');

            Route::post('licenses/{license}/suspend', [LicensingLicenseController::class, 'suspend'])->name('licenses.suspend');
            Route::post('licenses/{license}/reactivate', [LicensingLicenseController::class, 'reactivate'])->name('licenses.reactivate');
            Route::post('licenses/{license}/revoke', [LicensingLicenseController::class, 'revoke'])->name('licenses.revoke');
            Route::patch('licenses/{license}/status', [LicensingLicenseController::class, 'updateStatus'])->name('licenses.status');

            Route::get('transactions', [LicenseTransactionController::class, 'index'])->name('transactions.index');
            Route::post('licenses/{license}/transactions', [LicenseTransactionController::class, 'store'])->name('transactions.store');
            Route::post('transactions/{transaction}/approve', [LicenseTransactionController::class, 'approve'])->name('transactions.approve');
            Route::post('transactions/{transaction}/reject', [LicenseTransactionController::class, 'reject'])->name('transactions.reject');
        });
    });
});
