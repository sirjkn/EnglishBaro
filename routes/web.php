<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('tracks', [TrackController::class, 'index'])->name('tracks.index');
Route::get('tracks/{track}', [TrackController::class, 'show'])->name('tracks.show');
Route::get('tracks/{track}/levels/{level}', [TrackController::class, 'level'])->scopeBindings()->name('tracks.level');

Route::get('contact', [ContactController::class, 'create'])->name('contact');
Route::post('contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::post('logout', LogoutController::class)
    ->middleware(['auth'])
    ->name('logout');

require __DIR__.'/auth.php';
require __DIR__.'/student.php';
require __DIR__.'/admin.php';
