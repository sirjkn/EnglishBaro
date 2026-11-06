<?php

use App\Http\Controllers\Student\AccountController;
use App\Http\Controllers\Student\ChangeLevelController;
use App\Http\Controllers\Student\CoursePaymentController;
use App\Http\Controllers\Student\HelpController;
use App\Http\Controllers\Student\LearningController;
use App\Http\Controllers\Student\MorePracticeController;
use App\Http\Controllers\Student\MyTrackController;
use App\Http\Controllers\Student\NotificationController;
use App\Http\Controllers\Student\PaymentController;
use App\Http\Controllers\Student\PlacementTestController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Student\SessionController;
use App\Http\Controllers\Student\SupportTicketController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('placement-test', [PlacementTestController::class, 'show'])->name('placement-test');
        Route::post('placement-test', [PlacementTestController::class, 'store'])->name('placement-test.store');
        Route::get('placement-test/result', [PlacementTestController::class, 'result'])->name('placement-test.result');

        Route::get('more-practice', [MorePracticeController::class, 'index'])->name('more-practice');
        Route::get('more-practice/{category}', [MorePracticeController::class, 'show'])->name('more-practice.show');

        Route::get('help', HelpController::class)->name('help');

        Route::post('change-level', [ChangeLevelController::class, 'store'])->name('change-level');

        Route::get('tracks/{track}', [MyTrackController::class, 'show'])->name('tracks.show');
        Route::post('courses/{course}/pay', [CoursePaymentController::class, 'pay'])->name('courses.pay');

        Route::scopeBindings()->group(function () {
            Route::get('tracks/{track}/levels/{level}', [LearningController::class, 'level'])->name('tracks.level');
            Route::get('tracks/{track}/levels/{level}/learn', [LearningController::class, 'resume'])->name('tracks.resume');
            Route::get('tracks/{track}/levels/{level}/lessons/{lesson}', [LearningController::class, 'lesson'])->name('tracks.learn');
            Route::post('tracks/{track}/levels/{level}/lessons/{lesson}/complete', [LearningController::class, 'complete'])->name('tracks.complete-lesson');
            Route::post('tracks/{track}/levels/{level}/lessons/{lesson}/progress', [LearningController::class, 'updateVideoProgress'])->name('tracks.video-progress');

            Route::get('tracks/{track}/levels/{level}/lessons/{lesson}/activity', [LearningController::class, 'lessonActivity'])->name('tracks.lesson-activity');
            Route::post('tracks/{track}/levels/{level}/lessons/{lesson}/activity/check-answers', [LearningController::class, 'checkLessonAnswers'])->name('tracks.lesson-activity.check-answers');
        });

        Route::get('progress', ProgressController::class)->name('progress');

        Route::get('account', [AccountController::class, 'edit'])->name('account');
        Route::put('account', [AccountController::class, 'update'])->name('account.update');
        Route::put('account/password', [AccountController::class, 'updatePassword'])->name('account.password');

        Route::delete('sessions/{session}', [SessionController::class, 'destroy'])->name('sessions.destroy');
        Route::delete('sessions', [SessionController::class, 'destroyOthers'])->name('sessions.destroy-others');

        Route::get('payments', PaymentController::class)->name('payments');
        Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

        Route::get('support', [SupportTicketController::class, 'index'])->name('support.index');
        Route::get('support/create', [SupportTicketController::class, 'create'])->name('support.create');
        Route::post('support', [SupportTicketController::class, 'store'])->name('support.store');
        Route::get('support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
        Route::post('support/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('support.reply');
        Route::post('support/{ticket}/close', [SupportTicketController::class, 'close'])->name('support.close');
    });
