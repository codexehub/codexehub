<?php

use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/services', [ServiceController::class, 'index'])->name('services');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

// The static /products/software and /products/hardware paths must be declared
// before the {product} wildcard so it doesn't swallow them.
Route::get('/products/software', [ProductController::class, 'software'])->name('products.software');
Route::get('/products/hardware', [ProductController::class, 'physical'])->name('products.physical');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/about-us', function () {
    return view('pages.about');
})->name('about');

Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio');
Route::get('/portfolio/{portfolio}', [PortfolioController::class, 'show'])->name('portfolio.show');

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');

Route::post('/consultation', [ConsultationController::class, 'store'])->name('consultation.store');

require __DIR__.'/admin.php';
