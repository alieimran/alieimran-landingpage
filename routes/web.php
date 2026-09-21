<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LinkCategoryController;
use App\Http\Controllers\Admin\LinkController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::resource('links', LinkController::class)->except(['show']);
    Route::resource('social-links', SocialLinkController::class)->except(['show']);
    Route::resource('link-categories', LinkCategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('site-settings', [SiteSettingController::class, 'edit'])->name('site-settings.edit');
    Route::put('site-settings', [SiteSettingController::class, 'update'])->name('site-settings.update');
});

require __DIR__.'/auth.php';
