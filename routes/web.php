<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('courses/{course}', [CourseController::class, 'show'])->name('courses.show');

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
