<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseSectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::resource('courses', CourseController::class)->except(['show']);
        Route::post('courses/{course}/sections', [CourseSectionController::class, 'store'])->name('courses.sections.store');
        Route::put('courses/{course}/sections/{section}', [CourseSectionController::class, 'update'])->name('courses.sections.update');
        Route::delete('courses/{course}/sections/{section}', [CourseSectionController::class, 'destroy'])->name('courses.sections.destroy');

        Route::post('courses/{course}/sections/{section}/lessons', [LessonController::class, 'store'])->name('courses.lessons.store');
        Route::put('courses/{course}/lessons/{lesson}', [LessonController::class, 'update'])->name('courses.lessons.update');
        Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('courses.lessons.destroy');

        Route::resource('levels', LevelController::class)->except(['show']);

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
