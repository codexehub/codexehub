<?php

use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/services', function () {
    return view('pages.services');
})->name('services');

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
