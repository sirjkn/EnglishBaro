<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', fn (Blueprint $table) => $table->dropUnique(['user_id', 'course_id']));
        Schema::table('enrollments', fn (Blueprint $table) => $table->dropConstrainedForeignId('course_id'));
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
        });
        Schema::table('enrollments', fn (Blueprint $table) => $table->unique(['user_id', 'track_id']));

        Schema::table('course_progress', fn (Blueprint $table) => $table->dropUnique(['user_id', 'course_id']));
        Schema::table('course_progress', fn (Blueprint $table) => $table->dropConstrainedForeignId('course_id'));
        Schema::rename('course_progress', 'track_progress');
        Schema::table('track_progress', function (Blueprint $table) {
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('levels_completed')->default(0);
            $table->unsignedInteger('total_levels')->default(0);
        });
        Schema::table('track_progress', fn (Blueprint $table) => $table->unique(['user_id', 'track_id']));

        Schema::table('certificates', fn (Blueprint $table) => $table->dropUnique(['user_id', 'course_id']));
        Schema::table('certificates', fn (Blueprint $table) => $table->dropConstrainedForeignId('course_id'));
        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
        });
        Schema::table('certificates', fn (Blueprint $table) => $table->unique(['user_id', 'track_id']));

        Schema::table('payments', fn (Blueprint $table) => $table->dropIndex(['course_id', 'status']));
        Schema::table('payments', fn (Blueprint $table) => $table->dropConstrainedForeignId('course_id'));
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
        });
        Schema::table('payments', fn (Blueprint $table) => $table->index(['track_id', 'status']));

        Schema::table('subscriptions', fn (Blueprint $table) => $table->dropConstrainedForeignId('course_id'));
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
        });

        // Assessments now sit on a level: each of the 100 levels can carry its own quiz/test.
        Schema::table('assessments', fn (Blueprint $table) => $table->dropConstrainedForeignId('course_id'));
        Schema::table('assessments', function (Blueprint $table) {
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('ebooks', fn (Blueprint $table) => $table->dropConstrainedForeignId('course_id'));
        Schema::table('ebooks', function (Blueprint $table) {
            $table->foreignId('track_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('resources', fn (Blueprint $table) => $table->dropConstrainedForeignId('course_id'));
        Schema::table('resources', function (Blueprint $table) {
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('resources', fn (Blueprint $table) => $table->dropConstrainedForeignId('track_id'));
        Schema::table('resources', fn (Blueprint $table) => $table->foreignId('course_id')->constrained()->cascadeOnDelete());

        Schema::table('ebooks', fn (Blueprint $table) => $table->dropConstrainedForeignId('track_id'));
        Schema::table('ebooks', fn (Blueprint $table) => $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete());

        Schema::table('assessments', fn (Blueprint $table) => $table->dropConstrainedForeignId('level_id'));
        Schema::table('assessments', fn (Blueprint $table) => $table->foreignId('course_id')->constrained()->cascadeOnDelete());

        Schema::table('subscriptions', fn (Blueprint $table) => $table->dropConstrainedForeignId('track_id'));
        Schema::table('subscriptions', fn (Blueprint $table) => $table->foreignId('course_id')->constrained()->cascadeOnDelete());

        Schema::table('payments', fn (Blueprint $table) => $table->dropIndex(['track_id', 'status']));
        Schema::table('payments', fn (Blueprint $table) => $table->dropConstrainedForeignId('track_id'));
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->index(['course_id', 'status']);
        });

        Schema::table('certificates', fn (Blueprint $table) => $table->dropUnique(['user_id', 'track_id']));
        Schema::table('certificates', fn (Blueprint $table) => $table->dropConstrainedForeignId('track_id'));
        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unique(['user_id', 'course_id']);
        });

        Schema::table('track_progress', fn (Blueprint $table) => $table->dropUnique(['user_id', 'track_id']));
        Schema::table('track_progress', function (Blueprint $table) {
            $table->dropConstrainedForeignId('track_id');
            $table->dropColumn(['levels_completed', 'total_levels']);
        });
        Schema::rename('track_progress', 'course_progress');
        Schema::table('course_progress', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unique(['user_id', 'course_id']);
        });

        Schema::table('enrollments', fn (Blueprint $table) => $table->dropUnique(['user_id', 'track_id']));
        Schema::table('enrollments', fn (Blueprint $table) => $table->dropConstrainedForeignId('track_id'));
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unique(['user_id', 'course_id']);
        });
    }
};
