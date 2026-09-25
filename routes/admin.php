<?php

use App\Http\Controllers\Admin\AnswerReviewController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlacementQuestionController;
use App\Http\Controllers\Admin\SectionActivityController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TrackController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('tracks', [TrackController::class, 'index'])->name('tracks.index');
        Route::get('tracks/create', [TrackController::class, 'create'])->name('tracks.create');
        Route::post('tracks', [TrackController::class, 'store'])->name('tracks.store');
        Route::get('tracks/{track}/edit', [TrackController::class, 'edit'])->name('tracks.edit');
        Route::put('tracks/{track}', [TrackController::class, 'update'])->name('tracks.update');
        Route::delete('tracks/{track}', [TrackController::class, 'destroy'])->name('tracks.destroy');

        Route::get('levels', [LevelController::class, 'all'])->name('levels.index');

        Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::delete('enrollments/{enrollment}', [EnrollmentController::class, 'cancel'])->name('enrollments.cancel');

        Route::get('placement-test', [PlacementQuestionController::class, 'index'])->name('placement-test.index');
        Route::post('placement-test', [PlacementQuestionController::class, 'store'])->name('placement-test.store');
        Route::put('placement-test/{placementQuestion}', [PlacementQuestionController::class, 'update'])->name('placement-test.update');
        Route::delete('placement-test/{placementQuestion}', [PlacementQuestionController::class, 'destroy'])->name('placement-test.destroy');

        Route::get('answer-reviews', [AnswerReviewController::class, 'index'])->name('answer-reviews.index');
        Route::put('answer-reviews/{assessmentAnswer}', [AnswerReviewController::class, 'update'])->name('answer-reviews.update');

        Route::scopeBindings()->group(function () {
            Route::get('tracks/{track}/levels', [LevelController::class, 'index'])->name('tracks.levels.index');
            Route::post('tracks/{track}/levels', [LevelController::class, 'store'])->name('tracks.levels.store');
            Route::get('tracks/{track}/levels/{level}/edit', [LevelController::class, 'edit'])->name('tracks.levels.edit');
            Route::put('tracks/{track}/levels/{level}', [LevelController::class, 'update'])->name('tracks.levels.update');
            Route::delete('tracks/{track}/levels/{level}', [LevelController::class, 'destroy'])->name('tracks.levels.destroy');

            Route::post('tracks/{track}/levels/{level}/sections/{section}/lessons', [LessonController::class, 'store'])->name('tracks.lessons.store');
            Route::put('tracks/{track}/levels/{level}/lessons/{lesson}', [LessonController::class, 'update'])->name('tracks.lessons.update');
            Route::delete('tracks/{track}/levels/{level}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('tracks.lessons.destroy');

            Route::post('tracks/{track}/levels/{level}/sections/{section}/activity/questions', [SectionActivityController::class, 'storeQuestion'])->name('tracks.sections.activity.questions.store');
        });

        // Not under scopeBindings(): AssessmentQuestion has no direct relation
        // named after $section on the Section model for implicit nested scoping,
        // so ownership is verified manually in the controller instead.
        Route::put('tracks/{track}/levels/{level}/sections/{section}/activity/questions/{assessmentQuestion}', [SectionActivityController::class, 'updateQuestion'])->name('tracks.sections.activity.questions.update');
        Route::delete('tracks/{track}/levels/{level}/sections/{section}/activity/questions/{assessmentQuestion}', [SectionActivityController::class, 'destroyQuestion'])->name('tracks.sections.activity.questions.destroy');

        Route::get('students', [StudentController::class, 'index'])->name('students.index');
        Route::get('students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('students', [StudentController::class, 'store'])->name('students.store');
        Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::get('students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::post('students/{student}/reset-session-quota', [StudentController::class, 'resetSessionQuota'])->name('students.reset-session-quota');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

        Route::get('sessions', [SessionController::class, 'index'])->name('sessions.index');
        Route::delete('sessions/all/students', [SessionController::class, 'destroyAllStudents'])->name('sessions.destroy-all-students');
        Route::delete('sessions/all/admins', [SessionController::class, 'destroyAllAdmins'])->name('sessions.destroy-all-admins');
        Route::delete('sessions/user/{user}', [SessionController::class, 'destroyOthers'])->name('sessions.destroy-others');
        Route::delete('sessions/{session}', [SessionController::class, 'destroy'])->name('sessions.destroy');

        Route::resource('testimonials', TestimonialController::class)->except(['show']);
        Route::post('testimonials/{testimonial}/toggle-publish', [TestimonialController::class, 'togglePublish'])->name('testimonials.toggle-publish');

        Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::put('contact-messages/{contactMessage}', [ContactMessageController::class, 'update'])->name('contact-messages.update');
        Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company');
        Route::put('settings/payment', [SettingsController::class, 'updatePayment'])->name('settings.payment');
        Route::put('settings/system', [SettingsController::class, 'updateSystem'])->name('settings.system');
    });
