<?php

use App\Http\Controllers\Admin\AnswerReviewController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\LessonActivityController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\MediaUploadController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlacementQuestionController;
use App\Http\Controllers\Admin\PracticeQuestionController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TrackController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('module:dashboard')->group(function () {
            Route::get('dashboard', DashboardController::class)->name('dashboard');
        });

        // Also covers the nested levels/lessons/activity editor and its
        // media-upload helper endpoints — all part of the same track-editing
        // flow, so they share the 'tracks' module grant. Courses share it
        // too: a course is just the sellable wrapper around a set of tracks.
        Route::middleware('module:tracks')->group(function () {
            Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
            Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create');
            Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
            Route::get('courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
            Route::put('courses/{course}', [CourseController::class, 'update'])->name('courses.update');
            Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

            Route::get('tracks', [TrackController::class, 'index'])->name('tracks.index');
            Route::get('tracks/create', [TrackController::class, 'create'])->name('tracks.create');
            Route::post('tracks', [TrackController::class, 'store'])->name('tracks.store');
            Route::get('tracks/{track}/edit', [TrackController::class, 'edit'])->name('tracks.edit');
            Route::put('tracks/{track}', [TrackController::class, 'update'])->name('tracks.update');
            Route::delete('tracks/{track}', [TrackController::class, 'destroy'])->name('tracks.destroy');

            Route::post('media-uploads', [MediaUploadController::class, 'store'])->name('media-uploads.store');
            Route::delete('media-uploads/{media}', [MediaUploadController::class, 'destroy'])->name('media-uploads.destroy');
            Route::get('media-uploads/mux-assets', [MediaUploadController::class, 'muxAssets'])->name('media-uploads.mux-assets');
            Route::get('media-uploads/library', [MediaUploadController::class, 'libraryAssets'])->name('media-uploads.library');
            Route::post('media-uploads/from-mux', [MediaUploadController::class, 'fromMux'])->name('media-uploads.from-mux');

            Route::scopeBindings()->group(function () {
                Route::get('tracks/{track}/levels', [LevelController::class, 'index'])->name('tracks.levels.index');
                Route::post('tracks/{track}/levels', [LevelController::class, 'store'])->name('tracks.levels.store');
                Route::get('tracks/{track}/levels/{level}/edit', [LevelController::class, 'edit'])->name('tracks.levels.edit');
                // A bare GET here (no /edit) isn't a real page — just send it to
                // the edit page instead of a 405, in case of a stale link/bookmark.
                Route::redirect('tracks/{track}/levels/{level}', '/admin/tracks/{track}/levels/{level}/edit');
                Route::put('tracks/{track}/levels/{level}', [LevelController::class, 'update'])->name('tracks.levels.update');
                Route::delete('tracks/{track}/levels/{level}', [LevelController::class, 'destroy'])->name('tracks.levels.destroy');

                Route::post('tracks/{track}/levels/{level}/sections/{section}/lessons', [LessonController::class, 'store'])->name('tracks.lessons.store');
                Route::put('tracks/{track}/levels/{level}/lessons/{lesson}', [LessonController::class, 'update'])->name('tracks.lessons.update');
                Route::delete('tracks/{track}/levels/{level}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('tracks.lessons.destroy');

                Route::put('tracks/{track}/levels/{level}/lessons/{lesson}/activity', [LessonActivityController::class, 'updateTitle'])->name('tracks.lessons.activity.update-title');
                Route::post('tracks/{track}/levels/{level}/lessons/{lesson}/activity/questions', [LessonActivityController::class, 'storeQuestion'])->name('tracks.lessons.activity.questions.store');
            });

            // Not under scopeBindings(): AssessmentQuestion has no direct relation
            // named after $lesson on the Lesson model for implicit nested scoping,
            // so ownership is verified manually in the controller instead.
            Route::put('tracks/{track}/levels/{level}/lessons/{lesson}/activity/questions/{assessmentQuestion}', [LessonActivityController::class, 'updateQuestion'])->name('tracks.lessons.activity.questions.update');
            Route::delete('tracks/{track}/levels/{level}/lessons/{lesson}/activity/questions/{assessmentQuestion}', [LessonActivityController::class, 'destroyQuestion'])->name('tracks.lessons.activity.questions.destroy');
        });

        Route::middleware('module:enrollments')->group(function () {
            Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
            Route::delete('enrollments/{enrollment}', [EnrollmentController::class, 'cancel'])->name('enrollments.cancel');
        });

        Route::middleware('module:placement-test')->group(function () {
            Route::get('placement-test', [PlacementQuestionController::class, 'index'])->name('placement-test.index');
            Route::post('placement-test', [PlacementQuestionController::class, 'store'])->name('placement-test.store');
            Route::put('placement-test/{placementQuestion}', [PlacementQuestionController::class, 'update'])->name('placement-test.update');
            Route::delete('placement-test/{placementQuestion}', [PlacementQuestionController::class, 'destroy'])->name('placement-test.destroy');
        });

        Route::middleware('module:more-practice')->group(function () {
            Route::get('practice-questions', [PracticeQuestionController::class, 'index'])->name('practice-questions.index');
            Route::post('practice-questions', [PracticeQuestionController::class, 'store'])->name('practice-questions.store');
            Route::put('practice-questions/{practiceQuestion}', [PracticeQuestionController::class, 'update'])->name('practice-questions.update');
            Route::delete('practice-questions/{practiceQuestion}', [PracticeQuestionController::class, 'destroy'])->name('practice-questions.destroy');
        });

        Route::middleware('module:answer-reviews')->group(function () {
            Route::get('answer-reviews', [AnswerReviewController::class, 'index'])->name('answer-reviews.index');
            Route::put('answer-reviews/{assessmentAnswer}', [AnswerReviewController::class, 'update'])->name('answer-reviews.update');
        });

        Route::middleware('module:students')->group(function () {
            Route::get('students', [StudentController::class, 'index'])->name('students.index');
            Route::get('students/create', [StudentController::class, 'create'])->name('students.create');
            Route::post('students', [StudentController::class, 'store'])->name('students.store');
            Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');
            Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update');
            Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
            Route::post('students/{student}/reset-session-quota', [StudentController::class, 'resetSessionQuota'])->name('students.reset-session-quota');
            Route::post('students/{student}/reset-password', [StudentController::class, 'resetPassword'])->name('students.reset-password');
            Route::post('students/{student}/verify-email', [StudentController::class, 'verifyEmail'])->name('students.verify-email');
            Route::post('students/{student}/switch-track', [StudentController::class, 'switchTrack'])->name('students.switch-track');
        });

        Route::middleware('module:payments')->group(function () {
            Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        });

        Route::middleware('module:sessions')->group(function () {
            Route::get('sessions', [SessionController::class, 'index'])->name('sessions.index');
            Route::delete('sessions/orphaned', [SessionController::class, 'destroyOrphaned'])->name('sessions.destroy-orphaned');
            Route::delete('sessions/all/students', [SessionController::class, 'destroyAllStudents'])->name('sessions.destroy-all-students');
            Route::delete('sessions/all/admins', [SessionController::class, 'destroyAllAdmins'])->name('sessions.destroy-all-admins');
            Route::delete('sessions/user/{user}', [SessionController::class, 'destroyOthers'])->name('sessions.destroy-others');
            Route::delete('sessions/{session}', [SessionController::class, 'destroy'])->name('sessions.destroy');
        });

        Route::middleware('module:testimonials')->group(function () {
            Route::resource('testimonials', TestimonialController::class)->except(['show']);
            Route::post('testimonials/{testimonial}/toggle-publish', [TestimonialController::class, 'togglePublish'])->name('testimonials.toggle-publish');
        });

        Route::middleware('module:support')->group(function () {
            Route::get('support', [SupportTicketController::class, 'index'])->name('support.index');
            Route::get('support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
            Route::post('support/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('support.reply');
            Route::post('support/{ticket}/close', [SupportTicketController::class, 'close'])->name('support.close');
            Route::post('support/{ticket}/reopen', [SupportTicketController::class, 'reopen'])->name('support.reopen');
        });

        Route::middleware('module:audit-logs')->group(function () {
            Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        });

        Route::middleware('module:users')->group(function () {
            Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
            Route::get('users/create', [UserManagementController::class, 'create'])->name('users.create');
            Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('module:settings')->group(function () {
            Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company');
            Route::put('settings/intro-video', [SettingsController::class, 'updateIntroVideo'])->name('settings.intro-video');
            Route::put('settings/help-video', [SettingsController::class, 'updateHelpVideo'])->name('settings.help-video');
            Route::put('settings/payment', [SettingsController::class, 'updatePayment'])->name('settings.payment');
            Route::put('settings/system', [SettingsController::class, 'updateSystem'])->name('settings.system');
            Route::get('settings/backup', [BackupController::class, 'download'])->name('settings.backup.download');
            Route::post('settings/restore/preview', [BackupController::class, 'preview'])->name('settings.backup.restore.preview');
            Route::post('settings/restore', [BackupController::class, 'restore'])->name('settings.backup.restore');
        });
    });
