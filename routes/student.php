<?php

use App\Http\Controllers\Student\AccountController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\LearningController;
use App\Http\Controllers\Student\MyCourseController;
use App\Http\Controllers\Student\NotificationController;
use App\Http\Controllers\Student\PaymentController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Student\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('courses', [MyCourseController::class, 'index'])->name('courses.index');
        Route::get('courses/{course}/learn', [LearningController::class, 'show'])->name('courses.show-learn');
        Route::get('courses/{course}/learn/{lesson}', [LearningController::class, 'lesson'])->name('courses.learn');
        Route::post('courses/{course}/learn/{lesson}/complete', [LearningController::class, 'complete'])->name('courses.complete-lesson');
        Route::post('courses/{course}/learn/{lesson}/progress', [LearningController::class, 'updateVideoProgress'])->name('courses.video-progress');

        Route::get('progress', ProgressController::class)->name('progress');

        Route::get('account', [AccountController::class, 'edit'])->name('account');
        Route::put('account', [AccountController::class, 'update'])->name('account.update');
        Route::put('account/password', [AccountController::class, 'updatePassword'])->name('account.password');

        Route::delete('sessions/{session}', [SessionController::class, 'destroy'])->name('sessions.destroy');
        Route::delete('sessions', [SessionController::class, 'destroyOthers'])->name('sessions.destroy-others');

        Route::get('payments', PaymentController::class)->name('payments');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    });
