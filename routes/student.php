<?php

use App\Http\Controllers\Student\AccountController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\LearningController;
use App\Http\Controllers\Student\MyTrackController;
use App\Http\Controllers\Student\NotificationController;
use App\Http\Controllers\Student\PaymentController;
use App\Http\Controllers\Student\PlacementTestController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Student\SessionController;
use App\Http\Controllers\Student\TrackPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('placement-test', [PlacementTestController::class, 'show'])->name('placement-test');
        Route::post('placement-test', [PlacementTestController::class, 'store'])->name('placement-test.store');

        Route::get('tracks', [MyTrackController::class, 'index'])->name('tracks.index');
        Route::get('tracks/{track}', [MyTrackController::class, 'show'])->name('tracks.show');
        Route::post('tracks/{track}/pay', [TrackPaymentController::class, 'pay'])->name('tracks.pay');

        Route::scopeBindings()->group(function () {
            Route::get('tracks/{track}/levels/{level}', [LearningController::class, 'level'])->name('tracks.level');
            Route::get('tracks/{track}/levels/{level}/learn', [LearningController::class, 'resume'])->name('tracks.resume');
            Route::get('tracks/{track}/levels/{level}/lessons/{lesson}', [LearningController::class, 'lesson'])->name('tracks.learn');
            Route::post('tracks/{track}/levels/{level}/lessons/{lesson}/complete', [LearningController::class, 'complete'])->name('tracks.complete-lesson');
            Route::post('tracks/{track}/levels/{level}/lessons/{lesson}/progress', [LearningController::class, 'updateVideoProgress'])->name('tracks.video-progress');

            Route::get('tracks/{track}/levels/{level}/sections/{section}/activity', [LearningController::class, 'sectionActivity'])->name('tracks.section-activity');
            Route::post('tracks/{track}/levels/{level}/sections/{section}/activity/check-answers', [LearningController::class, 'checkSectionAnswers'])->name('tracks.section-activity.check-answers');
        });

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
